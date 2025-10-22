<?php
if (isset($_GET['ip'])) {
    $ip = $_GET['ip'];
    $bytes = isset($_GET['bytes']) ? (int)$_GET['bytes'] : 1400; // Default to 1400

    // Fix /24 IP format
    if (preg_match('/\.(\d+)\/24$/', $ip, $matches)) {
        $lastOctet = (int)$matches[1];
        if ($lastOctet === 0) {
            $ip = preg_replace('/\.0\/24$/', '.1', $ip);
        } else {
            $ip = preg_replace('/\/24$/', '', $ip);
        }
    }

    // Set headers for SSE
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');

    // Run ping command with dynamic byte size
    $command = "ping -n 20 -l " . $bytes . " " . escapeshellarg($ip);
    $proc = popen($command, 'r');

    if ($proc) {
        while (!feof($proc)) {
            $line = fgets($proc);
            if ($line) {
                echo "data: " . trim($line) . "\n\n"; // Send line to client
                ob_flush();
                flush();
            }
        }
        pclose($proc);
    }
}
?>
