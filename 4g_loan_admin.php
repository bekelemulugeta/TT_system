<?php

require_once("adminn.php");

$rd = '0000-00-00';
$td = date("Y-m-d");
$rb = "";



// Fetch branches securely
$query = "SELECT branch_name FROM `service_info` ORDER BY branch_name ASC";
$resultbn = mysqli_prepare($link, $query);
mysqli_stmt_execute($resultbn);
$branch_result = mysqli_stmt_get_result($resultbn);


// Fetch available IMEIs securely
$query = "SELECT imei FROM `4g` WHERE imei NOT IN (SELECT imei FROM `4g_loan` WHERE return_date=?)";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "s", $rd);
mysqli_stmt_execute($stmt);
$imei_result = mysqli_stmt_get_result($stmt);

// Fetch loaned devices securely and store them in an array for reuse
$query = "SELECT * FROM `4g_loan` WHERE return_date=?";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "s", $rd);
mysqli_stmt_execute($stmt);
$loanedDevices = mysqli_stmt_get_result($stmt);
$loanedDevicesArray = mysqli_fetch_all($loanedDevices, MYSQLI_ASSOC); // Store the result for reuse

// Handle Loan with Signature Upload
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['loan_submit'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $sn = trim($_POST['old_sn']);
    $bn = trim($_POST['branch_name']);
    $takenby = trim($_POST['person']);
    $signaturePath = "";

    // Handle file upload
    if (isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . "/signature/";
        $fileName = basename($_FILES['signature']['name']);
        $fileName = time() . "_" . $fileName; // Unique file name
        $targetFile = $uploadDir . $fileName;

        // Ensure directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Validate file type (Only images allowed)
        $fileType = mime_content_type($_FILES['signature']['tmp_name']);
        if (in_array($fileType, ['image/jpeg', 'image/png'])) {
            move_uploaded_file($_FILES['signature']['tmp_name'], $targetFile);
            $signaturePath = "signature/" . $fileName;
        } else {
            die("Invalid file type. Only JPG and PNG are allowed.");
        }
    }

    // Check if IMEI is already loaned
    $imeiCheckQuery = "SELECT imei FROM `4g_loan` WHERE imei = ? AND return_date = ?";
    $imeiCheckStmt = mysqli_prepare($link, $imeiCheckQuery);
    mysqli_stmt_bind_param($imeiCheckStmt, "ss", $sn, $rd);
    mysqli_stmt_execute($imeiCheckStmt);
    $imeiCheckResult = mysqli_stmt_get_result($imeiCheckStmt);

    if (mysqli_num_rows($imeiCheckResult) > 0) {
        
         $error_message = "This IMEI has already been loaned ";
    }

    if (!empty($sn) && !empty($bn) && !empty($takenby) && !empty($signaturePath)) {
        $sql = "INSERT INTO 4g_loan (imei, branch, date_taken, taken_by, return_date, return_by, signature) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "sssssss", $sn, $bn, $td, $takenby, $rd, $rb, $signaturePath);
        mysqli_stmt_execute($stmt);
        $success_message="Loaned!!!!!";
        
    }
}

// Handle Return
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['return_submit'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $imei = trim($_POST['return_imei']);
    $returnBy = trim($_POST['return_by']);

    if (!empty($imei) && !empty($returnBy)) {
        $sql = "UPDATE 4g_loan SET return_date=?, return_by=? WHERE imei=? AND return_date=?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $td, $returnBy, $imei, $rd);
        mysqli_stmt_execute($stmt);
         $sucess_message = "Returned";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>4G Loan System</title>
    <link rel="stylesheet" href="4g_loan_admin_styles.css">
</head>

<body>
    <div class="table-container">
    <h2>4G Loan Management</h2>

    <!-- Loaned Devices Table -->
   <table id="tblData" class="table table-bordered">
         <thead>
                <tr>
            <th>IMEI</th>
            <th>Branch</th>
            <th>Date Taken</th>
            <th>Taken By</th>
            <th>Signature</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($loanedDevicesArray as $row) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['imei']); ?></td>
                <td><?php echo htmlspecialchars($row['branch']); ?></td>
                <td><?php echo htmlspecialchars($row['date_taken']); ?></td>
                <td><?php echo htmlspecialchars($row['taken_by']); ?></td>
                <td>
                    <a href="<?php echo htmlspecialchars($row['signature']); ?>" target="_blank">View Signature</a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
    </table>
</div>

    <div class="container">
        

        <!-- Loan Form -->
        <div class="form-box">
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
            <h2>Loan a 4G Device</h2>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <label>Choose IMEI:</label>
                <select name="old_sn" required>
                    <option value="" disabled selected>Please select...</option>
                    <?php while ($row = mysqli_fetch_array($imei_result)) { ?>
                        <option value="<?php echo htmlspecialchars($row[0]); ?>"><?php echo htmlspecialchars($row[0]); ?></option>
                    <?php } ?>
                </select>

                <label>Choose Branch:</label>
                <select name="branch_name" required>
                    <option value="" disabled selected>Please select...</option>
                    <?php while ($row = mysqli_fetch_array($branch_result)) { ?>
                        <option value="<?php echo htmlspecialchars($row[0]); ?>"><?php echo htmlspecialchars($row[0]); ?></option>
                    <?php } ?>
                </select>

                <label>Taken By:</label>
                <input type="text" name="person" required>

                <label>Signature (JPG/PNG):</label>
                <input type="file" name="signature" accept="image/jpeg, image/png" required>

                <button type="submit" name="loan_submit">Loan</button>
            </form>
        </div>

        <!-- Return Form -->
        <div class="form-box">
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

            <h2>Return a 4G Device</h2>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <label>Choose IMEI:</label>
                <select name="return_imei" required>
                    <option value="" disabled selected>Please select...</option>
                    <?php foreach ($loanedDevicesArray as $row) { ?>
                        <option value="<?php echo htmlspecialchars($row['imei']); ?>"><?php echo htmlspecialchars($row['imei']); ?></option>
                    <?php } ?>
                </select>

                <label>Returned By:</label>
                <input type="text" name="return_by" required>

                <button type="submit" name="return_submit" id="return">Return</button>
            </form>
        </div>
    </div>
</body>

</html>

<script type="text/javascript">
     setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => alert.style.display = 'none');
    }, 3000);


</script>