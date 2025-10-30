<?php
// scan_live_debug.php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', 'off');
while (ob_get_level() > 0) ob_end_flush();
ob_implicit_flush(true);

function sse($m) {
    echo "data: " . str_replace("\n", "\ndata: ", $m) . "\n\n";
    @ob_flush(); @flush();
}

sse("Debug scan starting...");
sse("Server OS: " . PHP_OS_FAMILY);
sse("PHP SAPI: " . php_sapi_name());
sse("PHP version: " . PHP_VERSION);

// Check disable_functions
$df = ini_get('disable_functions');
sse("disable_functions: " . ($df ? $df : '(none)'));

// Check safe_mode (old)
$safe = ini_get('safe_mode');
sse("safe_mode: " . ($safe ? $safe : '(off or N/A)'));

// Check common function availability
$funcs = ['exec','shell_exec','popen','proc_open','system','passthru'];
foreach ($funcs as $f) {
    sse("$f available: " . (function_exists($f) ? 'yes' : 'no'));
}

// Check for commands
$checkCommands = ['nmap','fping','ping'];
foreach ($checkCommands as $c) {
    if (PHP_OS_FAMILY === 'Windows') {
        $out = @shell_exec("where $c 2>NUL");
    } else {
        $out = @shell_exec("command -v $c 2>/dev/null");
    }
    sse("$c found: " . ($out ? trim($out) : 'NO'));
}

// Try a quick native ping test (to 127.0.0.1 to avoid network)
$target = '127.0.0.1';
sse("Trying native ping to $target ...");
if (PHP_OS_FAMILY === 'Windows') {
    $cmd = "ping -n 1 -w 1000 " . escapeshellarg($target) . " 2>&1";
} else {
    $cmd = "ping -c 1 -W 1 " . escapeshellarg($target) . " 2>&1";
}
$out = null;
$failedCmd = false;
if (function_exists('shell_exec')) {
    $out = @shell_exec($cmd);
    sse("ping output (first 5 lines):\n" . ($out ? implode("\n", array_slice(explode("\n", $out),0,5)) : "(no output or command failed)"));
} else {
    sse("shell_exec not available; cannot run ping.");
    $failedCmd = true;
}

// Try popen
if (function_exists('popen')) {
    sse("Trying popen with echo test...");
    $p = @popen((PHP_OS_FAMILY === 'Windows' ? 'echo hello' : 'echo hello'), 'r');
    if ($p) {
        $line = fgets($p);
        sse("popen read: " . trim($line));
        pclose($p);
    } else {
        sse("popen failed to run.");
    }
} else {
    sse("popen not available.");
}

// Try fsockopen to a known reachable port (localhost 80 or 443)
sse("Trying TCP connect to 127.0.0.1:80 (timeout 0.8s) ...");
$fp = @fsockopen('127.0.0.1', 80, $errno, $errstr, 0.8);
if ($fp) {
    sse("fsockopen success to 127.0.0.1:80");
    fclose($fp);
} else {
    sse("fsockopen failed to 127.0.0.1:80 — errno:$errno errstr:$errstr");
}

// Check current user
if (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
    $uid = posix_geteuid();
    $pw = posix_getpwuid($uid);
    sse("Process user: " . ($pw ? $pw['name'] : $uid));
} else {
    sse("posix_geteuid/posix_getpwuid not available - trying get_current_user()");
    sse("get_current_user(): " . get_current_user());
}

sse("Debug completed. If you see 'NO' for ping/nmap/fping or functions disabled, that's the reason scans finish quickly.");
exit;

#..................................................................

#This is the longest method that show each used method clearly



<?php
// scan_live.php
// SSE scanner: supports single host, /24 sweep, and start-end ranges (A-B).
// Chains: nmap -> fping -> native ping -> PHP TCP probe (fsockopen).
// WARNING: restrict usage to trusted admins and internal cidrs only.

// require_once("adminn.php"); // uncomment if you want only logged-in admins

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

@ini_set('output_buffering','off');
@ini_set('zlib.output_compression','off');
while (ob_get_level() > 0) ob_end_flush();
ob_implicit_flush(true);

define('MAX_HOSTS', 1024); // safety cap - change if you need

function sse_send($msg) {
    $msg = rtrim($msg, "\n");
    $lines = explode("\n", $msg);
    foreach ($lines as $line) {
        echo "data: " . $line . "\n";
    }
    echo "\n";
    @ob_flush();
    @flush();
}

function cmd_exists($cmd) {
    if (PHP_OS_FAMILY === 'Windows') {
        $out = @shell_exec("where $cmd 2>NUL");
        return !empty(trim($out));
    } else {
        $out = @shell_exec("command -v $cmd 2>/dev/null");
        return !empty(trim($out));
    }
}

// unsigned ip helper (returns string of unsigned int) - safer across platforms
function ip2uintstr($ip) {
    $v = @ip2long($ip);
    if ($v === false) return false;
    return sprintf('%u', $v);
}

function uintstr2ip($u) {
    // cast to int then long2ip (works on 64-bit PHP)
    return long2ip((int)$u);
}

// Basic input
if (!isset($_GET['ip']) || trim($_GET['ip']) === '') {
    sse_send("Error: missing ip parameter");
    exit;
}
$input = trim($_GET['ip']);
$bytes = isset($_GET['bytes']) ? (int)$_GET['bytes'] : 1400;
if ($bytes < 1 || $bytes > 65500) $bytes = 1400; // clamp

// Allowlist: only scan private RFC1918 by default (customize if needed)
function allowed_range($ip_or_cidr) {
    if (strpos($ip_or_cidr, '10.') === 0) return true;
    if (strpos($ip_or_cidr, '192.168.') === 0) return true;
    if (preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ip_or_cidr)) return true;
    return false;
}

$clean = preg_replace('/\s+/', '', $input);
sse_send("Requested: $clean (server: " . PHP_OS_FAMILY . ")");

// Determine input type: range A-B, CIDR (/24), or single host
$targets = [];

// 1) Start-end range format: "A-B"
if (strpos($clean, '-') !== false) {
    list($a, $b) = explode('-', $clean, 2);
    $a = trim($a); $b = trim($b);
    if (!filter_var($a, FILTER_VALIDATE_IP) || !filter_var($b, FILTER_VALIDATE_IP)) {
        sse_send("Error: invalid start/end IP format.");
        exit;
    }
    // enforce allowlist for IPv4
    if (!allowed_range($a) || !allowed_range($b)) {
        sse_send("Error: range not allowed by policy.");
        exit;
    }

    $sa = ip2uintstr($a);
    $sb = ip2uintstr($b);
    if ($sa === false || $sb === false) {
        sse_send("Error: ip2long conversion failed.");
        exit;
    }
    // make sure start <= end
    if (bccomp($sa, $sb) === 1) {
        sse_send("Error: start IP is greater than end IP.");
        exit;
    }
    // count hosts
    $count = (int)bcadd(bcsub($sb, $sa), '1');
    if ($count > MAX_HOSTS) {
        sse_send("Error: requested range expands to {$count} hosts (max " . MAX_HOSTS . ").");
        exit;
    }
    // build targets
    $cur = $sa;
    for ($i = 0; $i < $count; $i++) {
        $targets[] = uintstr2ip($cur);
        $cur = bcadd($cur, '1');
    }
}

// 2) CIDR /24 support (x.y.z.0/24 or x.y.z/24)
elseif (strpos($clean, '/24') !== false) {
    $base = explode('/', $clean)[0];
    // normalize forms like "10.10.24" -> "10.10.24.0"
    if (preg_match('/^(\d{1,3}\.\d{1,3}\.\d{1,3})$/', $base, $m)) {
        $base = $m[1] . '.0';
    }
    if (!filter_var($base, FILTER_VALIDATE_IP)) {
        sse_send("Error: invalid CIDR base ($base).");
        exit;
    }
    if (!allowed_range($base)) {
        sse_send("Error: CIDR not allowed by policy.");
        exit;
    }
    $parts = explode('.', $base);
    $prefix3 = "{$parts[0]}.{$parts[1]}.{$parts[2]}.";
    for ($i = 1; $i <= 254; $i++) {
        $targets[] = $prefix3 . $i;
        if (count($targets) >= MAX_HOSTS) break;
    }
    if (count($targets) === 0) {
        sse_send("Error: no targets generated for CIDR.");
        exit;
    }
}

// 3) single host (IP or hostname)
else {
    // strip accidental slash parts
    if (strpos($clean, '/') !== false) $clean = explode('/', $clean)[0];

    // if IPv4 and not allowed, block
    if (filter_var($clean, FILTER_VALIDATE_IP) && !allowed_range($clean)) {
        sse_send("Error: IP not allowed by policy.");
        exit;
    }

    // resolve hostname if possible, but allow hostname if it doesn't resolve
    if (!filter_var($clean, FILTER_VALIDATE_IP)) {
        $resolved = @gethostbyname($clean);
        if ($resolved && $resolved !== $clean && filter_var($resolved, FILTER_VALIDATE_IP)) {
            // resolved to IP
            if (!allowed_range($resolved)) {
                sse_send("Error: resolved IP not allowed by policy.");
                exit;
            }
            $targets[] = $resolved;
        } else {
            // keep hostname (native ping will attempt resolution)
            $targets[] = $clean;
        }
    } else {
        $targets[] = $clean;
    }
}

// summary
sse_send("Targets to scan: " . count($targets) . " entries.");

// ---------- Try nmap ----------
if (cmd_exists('nmap')) {
    $escaped = escapeshellarg($clean);
    $cmd = "nmap -sn $escaped 2>&1";
    sse_send("Trying nmap: $cmd");
    $proc = @popen($cmd, 'r');
    if ($proc) {
        while (!feof($proc)) {
            $line = fgets($proc);
            if ($line !== false) sse_send(trim($line));
        }
        pclose($proc);
        sse_send("nmap finished.");
        exit;
    } else {
        sse_send("nmap present but failed to run.");
    }
} else {
    sse_send("nmap not found — falling back.");
}

// ---------- Try fping ----------
if (cmd_exists('fping')) {
    if (count($targets) > 1 && PHP_OS_FAMILY !== 'Windows') {
        // pass range to fping if possible (Linux)
        $escapedRange = escapeshellarg($base . '/24');
        $cmd = "fping -a -g {$escapedRange} 2>&1";
        sse_send("Trying fping: $cmd");
        $proc = @popen($cmd, 'r');
        if ($proc) {
            while (!feof($proc)) {
                $line = fgets($proc);
                if ($line !== false) sse_send(trim($line));
            }
            pclose($proc);
            sse_send("fping finished.");
            exit;
        } else {
            sse_send("fping present but failed to run.");
        }
    } else {
        // single-host fping
        $t = escapeshellarg($targets[0]);
        $cmd = "fping -c1 -t300 $t 2>&1";
        sse_send("Trying fping single host: $cmd");
        $out = @shell_exec($cmd);
        if (!empty($out)) {
            foreach (explode("\n", trim($out)) as $line) sse_send($line);
            sse_send("fping finished.");
            exit;
        } else {
            sse_send("fping failed or produced no output.");
        }
    }
} else {
    sse_send("fping not found — falling back.");
}

// ---------- Native ping fallback ----------
sse_send("Using native ping fallback (may be slow for ranges).");
$isWindows = (PHP_OS_FAMILY === 'Windows');

foreach ($targets as $t) {
    if ($isWindows) {
        if (count($targets) === 1) {
            $cmd = "ping -n 5 -l " . intval($bytes) . " " . escapeshellarg($t) . " 2>&1";
        } else {
            $cmd = "ping -n 1 -w 500 " . escapeshellarg($t) . " 2>&1";
        }
    } else {
        if (count($targets) === 1) {
            $cmd = "ping -c 5 -s " . intval($bytes) . " " . escapeshellarg($t) . " 2>&1";
        } else {
            $cmd = "ping -c 1 -W 1 " . escapeshellarg($t) . " 2>&1";
        }
    }

    $out = @shell_exec($cmd);
    $isUp = false;
    if ($out !== null && $out !== '') {
        if (stripos($out, 'ttl=') !== false || stripos($out, 'bytes from') !== false || stripos($out, 'reply from') !== false) {
            $isUp = true;
        }
        foreach (explode("\n", trim($out)) as $line) {
            if ($line !== '') sse_send($t . " | " . trim($line));
        }
    } else {
        sse_send($t . " | (no ping output)");
    }
    sse_send($t . " - " . ($isUp ? "UP (ping)" : "DOWN (ping)"));
    usleep(20000);
}

// ---------- Final fallback: TCP connect probe ----------
sse_send("Ping stage done. Now trying TCP connect probes as last resort.");
$commonPorts = [22, 139, 443, 3389]; // adjust as needed

foreach ($targets as $t) {
    $status = 'DOWN';
    foreach ($commonPorts as $port) {
        $timeout = 0.8;
        $fp = @fsockopen($t, $port, $errno, $errstr, $timeout);
        if ($fp) {
            $status = "UP (tcp $port)";
            fclose($fp);
            break;
        }
    }
    sse_send("$t - $status (tcp probe)");
    usleep(20000);
}

sse_send("Scan finished.");
exit;



#use this in admin_home.php



// ---------- Scan: SSE based, supports single IP, CIDR, start-end, hostname ----------
function isLikelyValidTarget(input) {
    input = input.trim();
    if (input === '') return false;

    // Quick checks (not exhaustive) for allowed formats:
    // - single IP: 1.2.3.4
    // - CIDR: 1.2.3.0/24
    // - range: 1.2.3.1-1.2.3.100
    // - hostname: contains letters and dots
    var ipRegex = /^(25[0-5]|2[0-4]\d|1?\d?\d)(\.(25[0-5]|2[0-4]\d|1?\d?\d)){3}$/;
    var cidrRegex = /^([0-9]{1,3}\.){3}[0-9]{1,3}\/([0-9]|[12][0-9]|3[0-2])$/;
    var rangeRegex = /^([0-9]{1,3}\.){3}[0-9]{1,3}\s*-\s*([0-9]{1,3}\.){3}[0-9]{1,3}$/;
    var hostRegex = /^[a-zA-Z0-9\-\.]{1,253}$/;

    if (ipRegex.test(input) || cidrRegex.test(input) || rangeRegex.test(input)) return true;
    // hostname: ensure it has at least one dot or letters (basic)
    if (hostRegex.test(input)) return true;

    return false;
}

$(document).on("click", "#manual-scan-btn", function(){
    var ip = $("#manual-ip").val().trim();
    var resultDiv = $("#scan-result-content");
    var stopBtn = $("#manual-scan-stop-btn");

    if (ip === "") {
        alert("Please enter an IP, CIDR, range, or hostname to scan.");
        return;
    }
    if (!isLikelyValidTarget(ip)) {
        if (!confirm("Input looks unusual. Send it to the server anyway?")) {
            return;
        }
    }

    // Close any previous scan SSE
    if (window.scanSource) {
        try { window.scanSource.close(); } catch(e) {}
    }

    resultDiv.html("<em>Starting scan for " + $("<div>").text(ip).html() + " ...</em><br>");
    stopBtn.show();

    // Start SSE connection to scan_live.php
    window.scanSource = new EventSource("scan_live.php?ip=" + encodeURIComponent(ip));

    window.scanSource.onmessage = function(e) {
        // Append lines — the server streams "data: ..." SSE messages
        resultDiv.append($("<div>").text(e.data + "\n").html());
        // auto-scroll
        resultDiv.scrollTop(resultDiv[0].scrollHeight);
    };

    window.scanSource.onerror = function(e) {
        // When server closes, this fires; display finished if not user-stopped
        resultDiv.append("<span style='color:green;'>Scan finished or connection closed.</span><br>");
        try { window.scanSource.close(); } catch(err) {}
        stopBtn.hide();
    };
});

// Stop / cancel scan button
$(document).on("click", "#manual-scan-stop-btn", function(){
    if (window.scanSource) {
        try {
            window.scanSource.close();
        } catch(e){}
        $("#scan-result-content").append("<span style='color:red;'>Scan stopped by user.</span><br>");
    }
    $(this).hide();
});

// Also stop scan if user clicks other network actions (optional)
$(document).on("click", "#manual-ping-btn, #manual-trace-btn", function(){
    if (window.scanSource) {
        try { window.scanSource.close(); } catch(e){}
        $("#manual-scan-stop-btn").hide();
    }
});
