<?php
require_once("adminn.php");

$error_message = '';

// Handle Loan with Signature Upload
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['but_submit'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }


    // Get and sanitize input
    $sn = trim($_POST['sn']); // Trim spaces
    $sn = strip_tags($sn); // Remove HTML tags

    // Check if department already exists
    $sql_check = "SELECT 1 FROM department WHERE department = ?";
    $stmt_check = mysqli_prepare($link, $sql_check);
    
    if ($stmt_check) {
        mysqli_stmt_bind_param($stmt_check, "s", $sn);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check); // Store result to check num_rows

        if(mysqli_stmt_num_rows($stmt_check) > 0) {
           $error_message = "Department already exists: " . htmlspecialchars($sn);
        } else {
            // Insert new department
            $sql_insert = "INSERT INTO department (department) VALUES (?)";
            $stmt_insert = mysqli_prepare($link, $sql_insert);
            
            if ($stmt_insert) {
                mysqli_stmt_bind_param($stmt_insert, "s", $sn);
                if(mysqli_stmt_execute($stmt_insert)){
                    $success_message = "Department Registered Successfully: " . htmlspecialchars($sn);
                } else {
                    $error_message = "Something went wrong, try again later.";
                }
                mysqli_stmt_close($stmt_insert);
            } else {
                $error_message = "Failed to prepare insert statement.";
            }
        }
        mysqli_stmt_close($stmt_check);
    } else {
        $error_message = "Failed to prepare select statement.";
    }
}

// Close Database Connection
mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Department</title>
    <link href="add_department.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="jquery.inputmask.bundle.js"></script>
</head>
<body>
    <div class="form-container">

 <h1>Add Department</h1>
    <?php if (!empty($error_message)): ?>
        <div class="error">
            <p><?php echo $error_message; ?></p>
        </div>
    <?php endif; ?>
    <?php if (!empty($success_message)): ?>
        <div class="success">
            <p><?php echo $success_message; ?></p>
        </div>
    <?php endif; ?>
        <form method="post" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="form-group">
                
                <input type="text" id="sn" name="sn" class="form-input" required/>
            </div>
            <div class="form-group">
                <input type="submit" value="ADD" name="but_submit" class="submit-btn"/>
            </div>
        </form>
    </div>
</body>
</html>

