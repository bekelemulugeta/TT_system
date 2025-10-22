<?php
if (isset($_POST['ip'])) {
    $ip = $_POST['ip'];
    $detail = isset($_POST['detail']) ? $_POST['detail'] : false;

    // Fix /24 IP format
    if (preg_match('/\.(\d+)\/24$/', $ip, $matches)) {
        $lastOctet = (int)$matches[1];
        if ($lastOctet === 0) {
            $ip = preg_replace('/\.0\/24$/', '.1', $ip);
        } else {
            $ip = preg_replace('/\/24$/', '', $ip);
        }
    }

    if ($detail) {
        // Full output for manual ping
        exec("ping -n 4 -l 1400 " . escapeshellarg($ip), $output, $status);
        echo "<pre>" . implode("\n", $output) . "</pre>";
    } else {
        // Quick check for auto update
        exec("ping -n 1 -w 2000 " . escapeshellarg($ip), $output); // 2s timeout
        $up = false;
        foreach ($output as $line) {
            if (stripos($line, "Reply from ".$ip) !== false) {
                $up = true;
                break;
            }
        }
        echo $up ? "UP" : "DOWN";
    }
}

?>
