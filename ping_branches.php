<?php
include_once("config.php");

$downBranches = [];
$upBranches = [];
$lastChecked = date('Y-m-d H:i:s');

$query = "SELECT branch_name, lanip FROM service_info ORDER BY branch_name ASC";
$result = mysqli_query($link, $query);

// ✅ Helper: generate nearby IPs (.11–.20)
function generate_range_ips($ip, $rangeStart = 11, $rangeEnd = 20) {
    $parts = explode('.', $ip);
    $ips = [];
    if (count($parts) === 4) {
        for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
            $parts[3] = $i;
            $ips[] = implode('.', $parts);
        }
    }
    return $ips;
}

// ✅ Multi-method host status check (fast + resilient)
function check_host_status($ip) {
    // 1️⃣ Use fping (parallel and fast)
    if (function_exists('shell_exec') && @shell_exec('which fping')) {
        $cmd = "fping -c1 -t300 " . escapeshellarg($ip) . " 2>&1";
        $out = @shell_exec($cmd);
        if ($out && (stripos($out, 'alive') !== false || stripos($out, 'bytes from') !== false))
            return true;
    }

    // 2️⃣ Quick TCP check on common ports
    $ports = [135, 139, 445, 3389, 13291, 13000, 14000, 5985, 5986, 389, 636, 3268, 1433, 5900, 80, 443];
    foreach ($ports as $p) {
        $fp = @fsockopen($ip, $p, $errno, $errstr, 0.3);
        if ($fp) {
            fclose($fp);
            return true;
        }
    }

    // 3️⃣ Ping fallback (cross-platform)
    if (PHP_OS_FAMILY === 'Windows') {
        $cmd = "ping -n 1 -w 1000 " . escapeshellarg($ip);
    } else {
        $cmd = "ping -c 1 -W 1 " . escapeshellarg($ip);
    }
    $out = @shell_exec($cmd);
    if ($out && (stripos($out, 'ttl=') !== false || stripos($out, 'bytes from') !== false || stripos($out, 'reply from') !== false))
        return true;

    return false;
}

while ($row = mysqli_fetch_assoc($result)) {
    $lanip = trim($row['lanip']);
    $branch_name = trim($row['branch_name']);

    // Normalize /24 → .1 if .0/24
    $ipOnly = explode('/', $lanip)[0];
    $octets = explode('.', $ipOnly);
    if (count($octets) === 4) {
        if ($octets[3] === '0') $octets[3] = '1';
        $pingIp = implode('.', $octets);
    } else {
        $pingIp = $ipOnly;
    }

    // ✅ Step 1: Try main IP
    $isUp = check_host_status($pingIp);

    // ✅ Step 2: If main fails, try small IP range (.11–.20)
    if (!$isUp) {
        $range = generate_range_ips($pingIp);
        foreach ($range as $altIp) {
            if (check_host_status($altIp)) {
                $isUp = true;
                break;
            }
        }
    }

    $branchData = [
        'name' => $branch_name,
        'ip' => $pingIp,
        'status' => $isUp ? 'Up' : 'Down',
        'last_checked' => $lastChecked
    ];

    if ($isUp) {
        $upBranches[] = $branchData;
    } else {
        $downBranches[] = $branchData;
    }
}

// Combine down first (priority)
$allBranches = array_merge($downBranches, $upBranches);

// Return as JSON
header('Content-Type: application/json');
echo json_encode($allBranches, JSON_PRETTY_PRINT);
?>
