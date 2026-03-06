

<?php
if (isset($_POST['ip'])) {
    $ip = $_POST['ip'];

    // Normalize /24 format (convert x.x.x.0/24 → x.x.x.1)
    if (preg_match('/\.(\d+)\/24$/', $ip, $matches)) {
        $lastOctet = (int)$matches[1];
        if ($lastOctet === 0) {
            $ip = preg_replace('/\.0\/24$/', '.1', $ip);
        } else {
            $ip = preg_replace('/\/24$/', '', $ip);
        }
    }

    // Generate range of IPs (default .11–.20)
    function generate_range_ips($ip, $rangeStart = 11, $rangeEnd = 20) {
        $parts = explode('.', $ip);
        $ips = [];
        for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
            $parts[3] = $i;
            $ips[] = implode('.', $parts);
        }
        return $ips;
    }

    // Fast host checking with fping or TCP fallback
    function check_hosts_fast($hosts) {
        // 1) Use fping (fastest, parallel check)
        if (function_exists('shell_exec') && @shell_exec('which fping')) {
            $cmd = "fping -a -q -r0 -t200 " . implode(' ', array_map('escapeshellarg', $hosts)) . " 2>&1";
            $out = @shell_exec($cmd);
            if ($out) {
                $alive = array_filter(explode("\n", trim($out)));
                if (!empty($alive)) return true; // At least one host alive
            }
        }

        // 2) Fallback: Quick TCP port check (short timeout)
        $ports = [135, 139, 445, 3389, 13291, 13000, 14000,5985, 5986, 389, 636, 3268, 1433, 5900, 80, 443];
        foreach ($hosts as $host) {
            foreach ($ports as $p) {
                $fp = @fsockopen($host, $p, $errno, $errstr, 0.3);
                if ($fp) { fclose($fp); return true; }
            }
        }

        // 3) Optional quick ping (if allowed)
        if (PHP_OS_FAMILY !== 'Windows') {
            foreach ($hosts as $host) {
                $cmd = "ping -c 1 -W 1 " . escapeshellarg($host) . " 2>&1";
                $out = @shell_exec($cmd);
                if ($out && stripos($out, 'bytes from') !== false) return true;
            }
        }

        return false;
    }

    // Try the main IP first (if reachable)
    if (check_hosts_fast([$ip])) {
        echo "UP";
        exit;
    }

    // Otherwise, try IPs in the range (parallel via fping)
    $rangeIPs = generate_range_ips($ip);
    echo check_hosts_fast($rangeIPs) ? "UP" : "DOWN";
}
?>
