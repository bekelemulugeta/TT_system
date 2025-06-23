<?php
include_once("config.php"); // Database connection
include_once("adminn.php");

$branch_name = $office = $manager = $mphone = $accountant = $aphone = "";
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $branch_name = htmlspecialchars(trim(mysqli_real_escape_string($link,$_POST["branch_name"])));
    $office = htmlspecialchars(trim(mysqli_real_escape_string($link,$_POST["office"])));
    $manager = htmlspecialchars(trim(mysqli_real_escape_string($link,$_POST["manager"])));
    $mphone = htmlspecialchars(trim(mysqli_real_escape_string($link,$_POST["mphone"])));
    $accountant = htmlspecialchars(trim(mysqli_real_escape_string($link,$_POST["accountant"])));
    $aphone = htmlspecialchars(trim(mysqli_real_escape_string($link,$_POST["aphone"])));

    // Validation
    if (empty($branch_name)) $errors[] = "Branch name is required.";
    if (empty($office)) $errors[] = "Office phone is required.";
    
    // If no errors, insert into database
    if (empty($errors)) {
        $query = "INSERT INTO phonebook (branch_name, office, manager, mphone, accountant, aphone) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $query)) {
            mysqli_stmt_bind_param($stmt, "ssssss", $branch_name, $office, $manager, $mphone, $accountant, $aphone);
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Record added successfully!";
            } else {
                $errors[] = "Error inserting record: " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
     
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Phonebook Entry</title>
    <link rel="stylesheet" href="add_phone_admin.css"> 

</head>
<body>

<div class="container">
    <h2>Add New Phonebook Entry</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error) echo "<p>$error</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_msg)): ?>
        <div class="success">
            <p><?php echo $success_msg; ?></p>
        </div>
    <?php endif; ?>

    <form method="POST" action="add_phonebook.php">

        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

        <div class="form-group">
        <label>Branch Name:</label>
        <input type="text" name="branch_name" value="<?= htmlspecialchars($branch_name) ?>" required>
</div>

 <div class="form-group">
        <label>Office Phone:</label>
        <input type="text" name="office" value="<?= htmlspecialchars($office) ?>" required>
        </div>

 <div class="form-group">
        <label>Manager:</label>
        <input type="text" name="manager" value="<?= htmlspecialchars($manager) ?>" >
        </div>

 <div class="form-group">
        <label>Manager Phone:</label>
        <input type="text" name="mphone" value="<?= htmlspecialchars($mphone) ?>" >
        </div>

 <div class="form-group">
        <label>Accountant:</label>
        <input type="text" name="accountant" value="<?= htmlspecialchars($accountant) ?>" >
        </div>

 <div class="form-group">
        <label>Accountant Phone:</label>
        <input type="text" name="aphone" value="<?= htmlspecialchars($aphone) ?>" >
</div>
        <button type="submit" id="button" class="btn btn-primary">Add Entry</button>
    </form>
    <a href="all_phonebook_admin.php" class="btn btn-secondary">Back to Phonebook List</a>
</div>

</body>
</html>
