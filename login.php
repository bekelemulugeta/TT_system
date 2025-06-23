<?php
// Rate limit settings
define('MAX_LOGIN_ATTEMPTS', 5); // Max failed login attempts
define('LOCKOUT_TIME', 900); // Lockout time in seconds (15 minutes)
include("config.php");

// Process Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['but_submit'])) {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: login.php");
        exit();
    }

    if (isset($_POST['txt_uname']) && isset($_POST['txt_pwd'])) {
        $uname = trim($_POST['txt_uname']);
        $password = trim($_POST['txt_pwd']);
        $ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address

        // Check for too many login attempts
        $stmt = $link->prepare("SELECT COUNT(*), MIN(UNIX_TIMESTAMP(attempt_time)) FROM login_attempts WHERE username = ? AND ip_address = ? AND attempt_time > NOW() - INTERVAL ? SECOND");
        $lockoutDuration = LOCKOUT_TIME;
        $stmt->bind_param("ssi", $uname, $ip_address, $lockoutDuration);
        $stmt->execute();
        $stmt->bind_result($failed_attempts, $first_attempt_time);
        $stmt->fetch();
        $stmt->close();

        if ($failed_attempts >= MAX_LOGIN_ATTEMPTS) {
            $current_time = time();
            $lockout_remaining = LOCKOUT_TIME - ($current_time - $first_attempt_time);
            $minutes_left = ceil($lockout_remaining / 60);
            $_SESSION['error'] = "Too many failed login attempts. Please try again after $minutes_left minute(s).";
            header("Location: login.php");
            exit();
        }

        // Prepared statement to prevent SQL Injection
        $stmt = $link->prepare("SELECT username, user_type, password FROM user WHERE username = ?");
        $stmt->bind_param("s", $uname);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the user exists and verify the password
        if ($row = $result->fetch_assoc()) {
            $hashed_password = $row['password']; // Assuming passwords are stored hashed

            // Verify password
            if (password_verify($password, $hashed_password)) {
                session_regenerate_id(true); // Prevent session fixation attacks
                $_SESSION['login_user'] = $row['username'];
                $_SESSION['user_type'] = $row['user_type'];

                // Clear failed login attempts for this user and IP
                $stmt = $link->prepare("DELETE FROM login_attempts WHERE username = ? AND ip_address = ?");
                $stmt->bind_param("ss", $uname, $ip_address);
                $stmt->execute();
                $stmt->close();

                // Redirect based on user type
                header("Location: admin_home.php");
                exit();
            } else {
                // Failed login attempt, record it
                $stmt = $link->prepare("INSERT INTO login_attempts (username, ip_address, attempt_time) VALUES (?, ?, NOW())");
                $stmt->bind_param("ss", $uname, $ip_address);
                $stmt->execute();
                $stmt->close();

                $_SESSION['error'] = "Invalid credentials."; // Invalid password
            }
        } else {
            $_SESSION['error'] = "Invalid credentials."; // Username not found
        }
    }

    // Redirect to login page if login fails
    header("Location: login.php");
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <!-- FontAwesome Icons -->
       <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="login.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>
<body>
    
<div class="container">
    <form method="post" action="">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="login-form">
                   
                        <span class="login-label-gbe">Global Bank Ethiopia</span>
                    <!-- Error Message Example -->

                        <div class="text-input-container">

                            <?php if (isset($_SESSION['error'])): ?>
    <div class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
<?php endif; ?>

                            <label class="form-label">USERNAME</label>
                            <input name="txt_uname" type="text" class="text-style" required>
                        </div>

                        <div class="text-input-container">
                            <label class="form-label">PASSWORD</label>
                            <input name="txt_pwd" type="password" class="text-style" required>
                        </div>

                        <button name="but_submit" class="login-btn">Login</button>
                    </div>

             




                <div class="login-deco-container">
                    <div class="login-deco">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <i class="fa-solid fa-cloud big-cloud-icon"></i>
                        <div class="lock">
                            <i class="fa-solid fa-lock lock-icon"></i>
                            <i class="fa-solid fa-circle-check check-icon"></i>
                        </div>
                    </div>


                    <span class="footer">Secured</span>
             
            

            <!-- Digital Clock -->
            <div class="clock">
                <div><span id="hour">00</span></div>
                <div><span id="minutes">00</span></div>
                <div><span id="seconds">00</span></div>
                <div><span id="ampm">AM</span></div>
                <div><span id="date">DATE</span></div>
            </div>
        
    </form>
    <?php
    // Show error message if any
    if (isset($_SESSION['error'])) {
        echo '<p>' . $_SESSION['error'] . '</p>';
        unset($_SESSION['error']); // Clear the error after displaying
    }
    ?>
</div>
    <!-- JavaScript for Clock -->
    <script src="clock.js"></script>


</body>
</html>

  