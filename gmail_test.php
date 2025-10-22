<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// --- IPs to test ---
$branches = [
    ['name' => 'Branch1', 'ip' => '8.8.8.8'],  // Example: Google DNS (up)
    ['name' => 'Branch2', 'ip' => '197.0.2.1'] // Example: non-routable IP (down)
];

$downBranches = [];
$lastChecked = date('Y-m-d H:i:s');

// --- Ping each branch ---
foreach ($branches as $branch) {
    $status = null;
    exec("ping -n 1 -w 1000 {$branch['ip']}", $output, $status); // Windows ping
    if ($status !== 0) {
        $downBranches[] = $branch;
    }
}

// --- Send email if any down ---
if (!empty($downBranches)) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'bikemami0090@gmail.com';       // Your Gmail
        $mail->Password   = 'tlhy biky npje xqva ';         // Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->SMTPDebug = 2; // optional, shows detailed debug info
$mail->SMTPOptions = [
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ]
];


        // Recipients
        $mail->setFrom('bikemami0090@gmail.com', 'Branch Monitor');
        $mail->addAddress('bikemami0090@gmail.com');          // Receiver

        // Content
        $mail->isHTML(false);
        $mail->Subject = 'Branch Down Alert!';
        $body = "The following branches are down as of $lastChecked:\n\n";
        foreach ($downBranches as $b) {
            $body .= "{$b['name']} ({$b['ip']})\n";
        }
        $mail->Body = $body;

        $mail->send();
        echo "Email sent successfully!\n";
    } catch (Exception $e) {
        echo "Email could not be sent. Error: {$mail->ErrorInfo}\n";
    }
} else {
    echo "All branches are up. No email sent.\n";
}
?>
