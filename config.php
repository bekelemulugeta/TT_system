<?php


if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
    exit();
}


// **Security Headers**
header("X-XSS-Protection: 1; mode=block"); 
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("X-Frame-Options: DENY"); 
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("Content-Security-Policy-Report: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css; object-src 'none'; frame-ancestors 'none'");

     

// **Secure session settings** (Set these BEFORE session_start)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        "lifetime" => 7200,  
        "path" => "/",
        "domain" => "",  
        "secure" => true,  
        "httponly" => true,  
        "samesite" => "Lax"  
    ]);

    ini_set('session.gc_maxlifetime', 7200); 
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);

    session_start(); // Start session AFTER setting parameters
}

// **Session timeout logic**
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 7200)) {
    session_unset();
    session_destroy();
    header("Location: login.php"); 
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time();
session_regenerate_id(true); // Prevent session fixation attacks

// **Generate CSRF token if not set**
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// **Database connection settings**
$DATABASE_HOST = getenv('DB_HOST') ?: 'localhost';
$DATABASE_USER = getenv('DB_USER') ?: 'root';
$DATABASE_PASS = getenv('DB_PASS') ?: 'gbe@1234';
$DATABASE_NAME = getenv('DB_NAME') ?: 'dgb_tt';

// Enable error reporting for debugging, but prevent sensitive info from being shown
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $link = new mysqli($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
    $link->set_charset("utf8mb4"); 
} catch (Exception $e) {
    // Log the error to a file
    error_log("[" . date("Y-m-d H:i:s") . "] Database connection error: " . $e->getMessage() . "\n", 3, "errors.log");


    // Display a user-friendly error message
    die("<p style='margin-left:100px;color:red;'>Cannot connect to the database. Please try again later.</p>");
}

?>
