<?php

require_once("config.php");



$resolved = '0000-00-00';
$success_message = $error_message = "";

// Prepare and execute the first SELECT query securely
$queryy = "SELECT branch_name FROM `tt_registration` WHERE tt_resolved_date=? ORDER BY tt_reg_date ASC";
$stmt1 = mysqli_prepare($link, $queryy);
if ($stmt1) {
    mysqli_stmt_bind_param($stmt1, "s", $resolved);
    mysqli_stmt_execute($stmt1);
    $result1 = mysqli_stmt_get_result($stmt1);
} else {
    error_log("Database error: " . mysqli_error($link),"errors.log");
    $error_message = "Something went wrong! Please try again.";
    
    
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed.");
    }

    // Validate inputs
    $status = htmlspecialchars(trim($_POST['status'] ?? ''));
    $branch = htmlspecialchars(trim($_POST['branchh'] ?? ''));
    $remark = htmlspecialchars(trim($_POST['remark'] ?? ''));
    $tt_value = $_POST['tt'] ?? ''; // Ensure 'tt' exists or set to an empty string
    


    $updated_by = $_SESSION['login_user'] ?? 'unknown';
    $date = date("Y-m-d");
    $closeddate = ($status === "closed") ? $date : "";

    // Prepare and execute the second SELECT query
    $query = "SELECT tt, status, remark FROM `tt_registration` WHERE branch_name=? AND tt_resolved_date=?";
    $stmt11 = mysqli_prepare($link, $query);
    
    if ($stmt11) {
        mysqli_stmt_bind_param($stmt11, "ss", $branch, $resolved);
        mysqli_stmt_execute($stmt11);
        $result = mysqli_stmt_get_result($stmt11);
        $row = mysqli_fetch_assoc($result);

        $btt =    htmlspecialchars($row['tt']) ?? '';
        $bstatus = htmlspecialchars($row['status']) ?? '';
        $bremark = htmlspecialchars($row['remark']) ?? '';

        if (!empty($bremark)) {
            $remark = $bremark . ", " . $remark;
        }

        $TT = !empty($tt_value) ? $tt_value : $btt;
        $statuss = trim($bstatus . ($bstatus && $status ? ", " : ", ") . $status);
        if (!empty($statuss)) {
            $statuss .= " on " . $date . " by " . $updated_by;
        }

        mysqli_stmt_close($stmt11);
    } else {
        error_log("Database error: " . mysqli_error($link));
        $error_message = "Something went wrong! Please try again.";
    }

    // Prepare the UPDATE query
    $sql = "UPDATE tt_registration SET status=?, remark=?, tt_resolved_date=?, tt=? WHERE branch_name=? AND tt_resolved_date=?";
    $stmt22 = mysqli_prepare($link, $sql);

    if ($stmt22) {
        mysqli_stmt_bind_param($stmt22, "ssssss", $statuss, $remark, $closeddate, $TT, $branch, $resolved);
        
        if (mysqli_stmt_execute($stmt22)) {
            $success_message = "Updated Successfully.";
        } else {
            $error_message = "Update failed.";
            error_log("Update error: " . mysqli_error($link));
        }

        mysqli_stmt_close($stmt22);
    } else {
        error_log("Database error: " . mysqli_error($link));
        $error_message = "Something went wrong! Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Status & Remark</title>
    <link href="status_remark_update_styles.css" rel="stylesheet" type="text/css">
</head>
<body>
    <div class="container-update">
        <h2 class="form-title">Update Status & Remark</h2>

        <?php if (!empty($error_message) || !empty($success_message)): ?>
        <div id="message-box" class="<?php echo !empty($success_message) ? 'success' : 'error'; ?>">
            <p><?php echo htmlspecialchars($success_message ?: $error_message, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
         <?php endif; ?>

        <form method="post" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="form-group">
                <label for="branch">Choose Branch:</label>
                <select name="branchh" required>
                    <option value="" selected disabled hidden>Please select...</option>
                    <?php while ($row1 = mysqli_fetch_array($result1)): ?>
                        <option value="<?php echo htmlspecialchars($row1[0], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars($row1[0], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="status">Update Status:</label>
                <select name="status">
                    <option value=""></option>
                    <option value="Escalated">Escalated</option>
                    <option value="closed">Closed</option>
                </select>
            </div>

            <div class="form-group">
                <label for="tt">Update TT:</label>
                <input type="text" name="tt" pattern="^\d{16}$" maxlength="16" minlength="16" id="TT" />
                <small class="error-message" id="tt_error"></small>
            </div>

            <div class="form-group">
                <label for="remark">Remark:</label>
                <textarea id="remark" rows="4" cols="40" name="remark" required placeholder="Write your remark..."></textarea>
            </div>

            <button type="submit" class="btn btn-secondary">Save Changes</button>
        </form>
    </div>
</body>
</html>




    <script type="text/javascript">


 // Input validation for TT field (only numbers, 16 digits)
        var ttInput = document.getElementById("TT");
        var errorMessage = document.getElementById("tt_error");

        ttInput.addEventListener("input", function () {
            var value = ttInput.value;
            ttInput.value = value.replace(/\D/g, ''); // Allow only numbers

            if (ttInput.value.length !== 16) {
                errorMessage.textContent = "TT must be exactly 16 digits.";
            } else {
                errorMessage.textContent = "";
            }
        });

        const txtarea = document.getElementById("remark");
        txtarea.addEventListener("input", function () {
            const forbiddenChars = ["%", ">", "<", "/"];
            let inputValue = txtarea.value;
            forbiddenChars.forEach(char => {
                if (inputValue.includes(char)) {
                    txtarea.value = inputValue.replaceAll(char, "");
                    console.log(`You tried to enter ${char}`);
                }
            });
        });

        setTimeout(() => {
            let messageBox = document.getElementById("message-box");
            if (messageBox) {
                messageBox.style.display = "none";
                window.location.href = "all_active_tts_admin.php"; // Replace with the desired page
            }
        }, 3000);
    </script>

