
<?php

require_once("adminn.php");

 if ($_SESSION['user_type'] == 'User'){
       session_destroy();
    header("location:login.php");
    exit;
 
 }

$message = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $username = htmlspecialchars(trim(mysqli_real_escape_string($link,$_POST["username"])));
    $password = htmlspecialchars(trim(mysqli_real_escape_string($link,$_POST["password"])));
    $email =htmlspecialchars(trim(mysqli_real_escape_string($link,$_POST["email"])));
    $user_type = $_POST["user_type"];

    // Validate inputs
    if (empty($username) || empty($password) || empty($email) || empty($user_type)) {
        $message = "All fields are required.";
        $message_type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $message_type = "error";
    } else {
        // Check for duplicate username or email
        $stmt = $link->prepare("SELECT * FROM user WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Username or Email already exists.";
            $message_type = "error";
        } else {
            // Secure password storage
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Insert into the database
            $stmt = $link->prepare("INSERT INTO user (Name,username, password, email, user_type) VALUES (?, ?, ?, ?,?)");
            $stmt->bind_param("sssss", $username, $username, $hashed_password, $email, $user_type);

            if ($stmt->execute()) {
                $message = "User created successfully:".$username;
                $message_type = "success";
            } else {
                $message = "Error creating user. Try again.";
                $message_type = "error";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self'">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
    <link rel="stylesheet" href="insert_user_stylee.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>
<body>
    <div class="container">
        <h1>Create User</h1>
        <form method="post" action="">
           
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
   <div class="message-container">
                <!-- Display Success/Error Messages -->
                <?php if (!empty($message)): ?>
                    <div class="alert message <?= htmlspecialchars($message_type) ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <div>
                    <input type="text" class="textbox" name="username" placeholder="Username" required />
                </div>

                <div>
                    <input type="password" class="textbox" name="password" placeholder="Password" required />
                </div>

                <div>
                    <input type="email" class="textbox" name="email" placeholder="Email" required />
                </div>

                <div>
                    <label for="user_type">Select User Type:</label>
                    <select name="user_type" required>
                        <option value="User">User</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>

                <div>
                    <input type="submit" value="Submit" name="but_submit" id="but_submit" />
                </div>
       
        </form>
    </div>
</body>
</html>

<script type="text/javascript">
    
     setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => alert.style.display = 'none');
    }, 3000);

</script>