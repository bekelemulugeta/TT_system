
<?php

require_once("adminn.php");


// Fetch department values for dropdown
$dept_query = "SELECT department FROM department ORDER BY id ASC";
$dept_result = mysqli_query($link, $dept_query);
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    // Function to validate and sanitize input
    function validate_input($data, $maxLength = 100) {
        $data = trim($data);
        $data = htmlspecialchars($data);
        return substr($data, 0, $maxLength); // Limit length
    }

    // Collect and sanitize inputs
    $User_Name = validate_input($_POST['User_Name'] ?? '', 50);
    $Department = validate_input($_POST['Department'] ?? '', 50);
    $Computer_Model = validate_input($_POST['Computer_Model'] ?? '', 50);
    $Computer_Name = validate_input($_POST['Computer_Name'] ?? '', 50);
    $Ip = validate_input($_POST['Ip'] ?? '', 15);
    $Flex = validate_input($_POST['Flex'] ?? '', 1);
    $Rep = validate_input($_POST['Rep'] ?? '', 1);
    $Local = validate_input($_POST['Local'] ?? '', 1);
    $NBE = validate_input($_POST['NBE'] ?? '', 1);
    $Internet = validate_input($_POST['Internet'] ?? '', 1);

    // Validate IP format
    if (!filter_var($Ip, FILTER_VALIDATE_IP)) {
        $error_message = "Invalid IP address.";
    }

    // Ensure required fields are filled
    if (empty($User_Name) || empty($Department) || empty($Computer_Name) || empty($Ip)) {
        $error_message = "Please fill in all required fields.";
    }

    if (empty($error_message)) {
        // Use prepared statement to prevent SQL Injection
        $query = "INSERT INTO head_office_ip (User_Name, Department, Computer_Model, Computer_Name, Ip, Flex, Rep, Local, NBE, Internet) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = mysqli_prepare($link, $query)) {
            mysqli_stmt_bind_param($stmt, "ssssssssss", $User_Name, $Department, $Computer_Model, $Computer_Name, $Ip, $Flex, $Rep, $Local, $NBE, $Internet);

            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Registered Successfully:". htmlspecialchars($Ip);
            } else {
                $error_message = "Error processing request.";
            }


            mysqli_stmt_close($stmt);
        } else {
           
        }
    }
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert New Record</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.7-beta.19/jquery.inputmask.min.js"></script>
    <link rel="stylesheet" href="insert_record.css">
</head>
<body>
<div class="container">
    <h2>ADD HOIP</h2>
  <?php if (!empty($error_message)): ?>
        <div class="alert error">
            <p><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
        <div class="alert success">
            <p><?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <?php endif; ?>

    <form id="insertForm" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

        <label for="User_Name">User Name:</label>
        <input type="text" name="User_Name" id="User_Name" required maxlength="50">

        <label for="Department">Department:</label>
        <select name="Department" id="Department" required>
            <option value="" selected disabled>Select Department</option>
            <?php while ($row = mysqli_fetch_assoc($dept_result)) { ?>
                <option value="<?php echo htmlspecialchars($row['department']); ?>">
                    <?php echo htmlspecialchars($row['department']); ?>
                </option>
            <?php } ?>
        </select>

        <label for="Computer_Model">Computer Model:</label>
        <input type="text" name="Computer_Model" id="Computer_Model" maxlength="50">

        <label for="Computer_Name">Computer Name:</label>
        <input type="text" name="Computer_Name" id="Computer_Name" required maxlength="50">

        <label for="Ip">IP Address:</label>
        <input type="text" name="Ip" id="Ip" class="mask-ipv4" required maxlength="15">

        <label for="Flex">Flex:</label>
        <select name="Flex" id="Flex">
            <option value="Y">Yes</option>
            <option value="N">No</option>
        </select>

        <label for="Rep">Report:</label>
        <select name="Rep" id="Rep">
            <option value="Y">Yes</option>
            <option value="N">No</option>
        </select>

        <label for="Local">Local:</label>
        <select name="Local" id="Local">
            <option value="Y">Yes</option>
            <option value="N">No</option>
        </select>

        <label for="NBE">NBE:</label>
        <select name="NBE" id="NBE">
            <option value="Y">Yes</option>
            <option value="N">No</option>
        </select>

        <label for="Internet">Internet:</label>
        <select name="Internet" id="Internet">
            <option value="Y">Yes</option>
            <option value="N">No</option>
        </select>

        <button id="button" type="submit">Add Record</button>
    </form>

</div>

<script>
    $(".mask-ipv4").inputmask({ alias: "ip", greedy: false });

   
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => alert.style.display = 'none');
    }, 3000);



</script>

</body>
</html>
