<?php
require_once("adminn.php");



$message = "";

// Handle Loan with Signature Upload
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['but_submit'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }


    $old = trim($_POST['op']);
    $new = trim($_POST['np']);
    $confirm = trim($_POST['cp']);
    $uname = $_SESSION['login_user'];

    // Validate password strength
    if (strlen($new) < 8 || !preg_match("/[A-Z]/", $new) || !preg_match("/[0-9]/", $new)) {
        $message = "<p class='error-msg'>New password must be at least 8 characters long, contain an uppercase letter and a number.</p>";
    } elseif ($new !== $confirm) {
        $message = "<p class='error-msg'>Confirmation password mismatched.</p>";
    } else {
        // Get current hashed password from database
        $stmt = mysqli_prepare($link, "SELECT password FROM user WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $uname);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $hashed_password);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($hashed_password && password_verify($old, $hashed_password)) {
            // Hash new password
            $new_hashed = password_hash($new, PASSWORD_BCRYPT);

            // Update password securely
            $stmt = mysqli_prepare($link, "UPDATE user SET password = ? WHERE username = ?");
            mysqli_stmt_bind_param($stmt, "ss", $new_hashed, $uname);
            if (mysqli_stmt_execute($stmt)) {
                session_destroy();
                header("Location: change_password_admin.php?msg=success");
                exit;
            } else {
                $message = "<p class='error-msg'>Error updating password. Please try again.</p>";
            }
            mysqli_stmt_close($stmt);
        } else {
            $message = "<p class='error-msg'>Incorrect old password.</p>";
        }
    }

    mysqli_close($link);
}
?>


<html>
<head>
    <title>Change Password</title>
    <link href="change_password.css" rel="stylesheet" type="text/css">
</head>
<body>
    <div class="container">
        <form method="post" action="">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            
            <div id="div_login">
                <h1>Change Password</h1>

                <!-- Display Message -->
                <div class="message-container">
                    <?php 
                        if (!empty($message)) { 
                            echo $message; 
                        } elseif (isset($_GET['msg']) && $_GET['msg'] == 'success') {
                            echo "<p class='success-msg'>Password changed successfully. Please log in again.</p>";
                        }
                    ?>
                </div>

                <div>
                    <input type="password" class="textbox" name="op" placeholder="Old Password" required />
                </div>
                <div>
                    <input type="password" class="textbox" name="np" placeholder="New Password" required/>
                </div>
                <div>
                    <input type="password" class="textbox" name="cp" placeholder="Confirm Password" required/>
                </div>
                <div>
                    <input type="submit" value="Submit" name="but_submit" id="but_submit" />
                </div>
            </div>
        </form>
    </div>
</body>
</html>
