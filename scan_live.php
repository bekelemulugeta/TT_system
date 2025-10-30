<?php
// scan_live_minimal.php
// Minimal SSE scanner: outputs only "UP|<ip>" or "DOWN|<ip>"
// Supports single host, /24, and start-end ranges. Chains nmap->fping->ping->tcp probe.
// Keep require_once("adminn.php") if you want auth.

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

@ini_set('output_buffering','off');
@ini_set('zlib.output_compression','off');
while (ob_get_level() > 0) ob_end_flush();
ob_implicit_flush(true);

define('MAX_HOSTS', 1024); // safety cap

function sse_line($status, $ip) {
    // status: 'UP' or 'DOWN'
    $line = $status . '|' . $ip;
    echo "data: " . $line . "\n\n";
    @ob_flush(); @flush();
}

// helper: check command presence
function cmd_exists($cmd) {
    if (PHP_OS_FAMILY === 'Windows') {
        $out = @shell_exec("where $cmd 2>NUL");
        return !empty(trim($out));
    } else {
        $out = @shell_exec("command -v $cmd 2>/dev/null");
        return !empty(trim($out));
    }
}

// unsigned ip helpers for ranges
function ip2uintstr($ip) {
    $v = @ip2long($ip);
    if ($v === false) return false;
    return sprintf('%u', $v);
}
function uintstr2ip($u) {
    return long2ip((int)$u);
}

// allowlist for safety (private ranges)
function allowed_range($ip) {
    if (strpos($ip, '10.') === 0) return true;
    if (strpos($ip, '192.168.') === 0) return true;
    if (preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ip)) return true;
    return false;
}

// input
if (!isset($_GET['ip']) || trim($_GET['ip']) === '') {
    sse_line('DOWN', 'error:missing_ip');
    exit;
}
$input = trim($_GET['ip']);
$bytes = isset($_GET['bytes']) ? (int)$_GET['bytes'] : 1400;
if ($bytes < 1 || $bytes > 65500) $bytes = 1400;

// prepare targets
$clean = preg_replace('/\s+/', '', $input);
$targets = [];

// range A-B?
if (strpos($clean, '-') !== false) {
    list($a, $b) = explode('-', $clean, 2);
    $a = trim($a); $b = trim($b);
    if (!filter_var($a, FILTER_VALIDATE_IP) || !filter_var($b, FILTER_VALIDATE_IP)) {
        sse_line('DOWN', 'error:invalid_range');
        exit;
    }
    if (!allowed_range($a) || !allowed_range($b)) {
        sse_line('DOWN', 'error:range_not_allowed');
        exit;
    }
    $sa = ip2uintstr($a); $sb = ip2uintstr($b);
    if ($sa === false || $sb === false) { sse_line('DOWN','error:ip_conv'); exit; }
    if (bccomp($sa,$sb) === 1) { sse_line('DOWN','error:start_gt_end'); exit; }
    $count = (int)bcadd(bcsub($sb,$sa),'1');
    if ($count > MAX_HOSTS) { sse_line('DOWN','error:range_too_big'); exit; }
    $cur = $sa;
    for ($i=0;$i<$count;$i++) {
        $targets[] = uintstr2ip($cur);
        $cur = bcadd($cur,'1');
    }
}
// cidr /24?
elseif (strpos($clean, '/24') !== false) {
    $base = explode('/', $clean)[0];
    if (preg_match('/^(\d{1,3}\.\d{1,3}\.\d{1,3})$/', $base, $m)) $base = $m[1] . '.0';
    if (!filter_var($base, FILTER_VALIDATE_IP)) { sse_line('DOWN','error:invalid_cidr'); exit; }
    if (!allowed_range($base)) { sse_line('DOWN','error:cidr_not_allowed'); exit; }
    $p = explode('.', $base);
    $prefix3 = "{$p[0]}.{$p[1]}.{$p[2]}.";
    for ($i=1;$i<=254;$i++) $targets[] = $prefix3 . $i;
    if (count($targets) === 0) { sse_line('DOWN','error:no_targets'); exit; }
}
// single host (IP or hostname)
else {
    if (strpos($clean, '/') !== false) $clean = explode('/', $clean)[0];
    if (filter_var($clean, FILTER_VALIDATE_IP) && !allowed_range($clean)) { sse_line('DOWN','error:not_allowed'); exit; }
    if (!filter_var($clean, FILTER_VALIDATE_IP)) {
        $resolved = @gethostbyname($clean);
        if ($resolved && $resolved !== $clean && filter_var($resolved, FILTER_VALIDATE_IP)) {
            if (!allowed_range($resolved)) { sse_line('DOWN','error:resolved_not_allowed'); exit; }
            $targets[] = $resolved;
        } else {
            $targets[] = $clean; // let ping resolve hostname on its own
        }
    } else {
        $targets[] = $clean;
    }
}

// helper: determine UP/DOWN via methods
function check_host_status($host, $bytes=1400) {
    
    // 1) Native ping first
    if (PHP_OS_FAMILY === 'Windows') {
        $cmd = "ping -n 1 -w 500 " . escapeshellarg($host) . " 2>&1";
    } else {
        $cmd = "ping -c 1 -W 1 " . escapeshellarg($host) . " 2>&1";
    }
    $out = @shell_exec($cmd);
    if ($out !== null && ($out !== '')) {
        if (stripos($out, 'ttl=') !== false || stripos($out, 'bytes from') !== false || stripos($out, 'reply from') !== false) return 'UP';
    }

    // 2) TCP connect fallback
    $ports =[
    135,   // RPC
    139,   // NetBIOS
    445,   // SMB
    3389,  // RDP
    5985,  // WinRM HTTP
    5986,  // WinRM HTTPS
    389,   // LDAP
    636,   // LDAPS
    3268,  // Global Catalog
    1433,  // MSSQL
    5900,  // VNC
    80,    // HTTP (web apps)
    443    // HTTPS (web apps)
];
    foreach ($ports as $p) {
        $fp = @fsockopen($host, $p, $errno, $errstr, 0.8);
        if ($fp) { fclose($fp); return 'UP'; }
    }

    // 3) fping if available
    if (cmd_exists('fping')) {
        $cmd = "fping -c1 -t500 " . escapeshellarg($host) . " 2>&1";
        $out = @shell_exec($cmd);
        if ($out !== null && (stripos($out, 'alive') !== false || stripos($out, 'responded') !== false)) return 'UP';
    }

    // 4) nmap as last resort
    if (cmd_exists('nmap')) {
        $cmd = "nmap -sn " . escapeshellarg($host) . " 2>&1";
        $out = @shell_exec($cmd);
        if ($out !== null && (stripos($out, 'host is up') !== false || stripos($out, '1 host up') !== false)) return 'UP';
    }

    return 'DOWN';
}


// iterate targets and send minimal lines
foreach ($targets as $t) {
    $status = check_host_status($t, $bytes); // 'UP' or 'DOWN'
    sse_line($status, $t);
    // short sleep to reduce load
    usleep(20000);
}

sse_line('DONE','scan_complete');
exit;
