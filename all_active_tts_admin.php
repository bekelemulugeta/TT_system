<?php
require_once("adminn.php"); // session_start() & $link assumed here

$resolved = '0000-00-00';
$success_message = $error_message = "";


// Fetch Active TTs
$query = "SELECT branch_name, service_number, tt, wanip, lanip, tt_reg_date, status, remark 
          FROM `tt_registration` WHERE tt_resolved_date=? ORDER BY tt_reg_date ASC";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "s", $resolved);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Fetch Branches for dropdown
$query_branch = "SELECT DISTINCT branch_name FROM `tt_registration` WHERE tt_resolved_date=? ORDER BY tt_reg_date ASC";
$stmt_branch = mysqli_prepare($link, $query_branch);
mysqli_stmt_bind_param($stmt_branch, "s", $resolved);
mysqli_stmt_execute($stmt_branch);
$result_branch = mysqli_stmt_get_result($stmt_branch);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed.");
    }

    $updated_by = $_SESSION['login_user'] ?? 'unknown';
    $today = date("Y-m-d");

    // -----------------------------
    // BULK UPDATE
    // -----------------------------
  if (isset($_POST['bulk_update'])) {
    $selected_tts = $_POST['selected_tt'] ?? [];
    $general_remark = "old TTs update in bulk";

    if (empty($selected_tts)) {
        $error_message = "Please select TTs.";
    } else {
        mysqli_begin_transaction($link);

        // Prepare update statement
        $stmt_update = mysqli_prepare(
            $link,
            "UPDATE tt_registration 
             SET status = ?, remark = ?, tt_resolved_date = ? 
             WHERE tt = ? AND tt_resolved_date = '0000-00-00'"
        );

        foreach ($selected_tts as $tt) {
            // Fetch only unresolved TTs
            $stmt_fetch = mysqli_prepare(
                $link,
                "SELECT status, remark FROM tt_registration WHERE tt = ? AND tt_resolved_date = '0000-00-00'"
            );
            mysqli_stmt_bind_param($stmt_fetch, "s", $tt);
            mysqli_stmt_execute($stmt_fetch);
            $res = mysqli_stmt_get_result($stmt_fetch);
            $row = mysqli_fetch_assoc($res);
            mysqli_stmt_close($stmt_fetch);

            // Skip if TT is already resolved or does not exist
            if (!$row) continue;

            $old_status = $row['status'] ?? '';
            $old_remark = $row['remark'] ?? '';

            // Build new status and remark
            $new_status = $old_status 
                ? $old_status . " ,Closed on $today by $updated_by" 
                : "Closed on $today by $updated_by";

            $new_remark = $old_remark 
                ? $old_remark . ", " . $general_remark 
                : $general_remark;

            // Execute update
            mysqli_stmt_bind_param($stmt_update, "ssss", $new_status, $new_remark, $today, $tt);
            mysqli_stmt_execute($stmt_update);
        }

        mysqli_stmt_close($stmt_update);
        mysqli_commit($link);
        $success_message = "Selected TTs updated successfully.";
    }
}


    // -----------------------------
    // SINGLE UPDATE
    // -----------------------------
    if (isset($_POST['save_changes'])) {
        $branch = htmlspecialchars(trim($_POST['branchh'] ?? ''));
        $status = htmlspecialchars(trim($_POST['status'] ?? ''));
        $remark = htmlspecialchars(trim($_POST['remark'] ?? ''));
        $tt_value = $_POST['tt'] ?? '';

        if ($branch) {
            $closeddate = ($status === "closed") ? $today : "";

            // Fetch current TT info
            $stmt_single = mysqli_prepare($link, "SELECT tt, status, remark FROM tt_registration WHERE branch_name=? AND tt_resolved_date=?");
            mysqli_stmt_bind_param($stmt_single, "ss", $branch, $resolved);
            mysqli_stmt_execute($stmt_single);
            $res_single = mysqli_stmt_get_result($stmt_single);
            $row_single = mysqli_fetch_assoc($res_single);
            mysqli_stmt_close($stmt_single);

            $btt = $row_single['tt'] ?? '';
            $bstatus = $row_single['status'] ?? '';
            $bremark = $row_single['remark'] ?? '';

            if (!empty($bremark)) $remark = $bremark . ", " . $remark;
            $TT = !empty($tt_value) ? $tt_value : $btt;
            $statuss = trim($bstatus . ($bstatus && $status ? ", " : ",") . $status);
            if (!empty($statuss)) $statuss .= " on $today by $updated_by";

            // Update tt_registration
            $stmt_update2 = mysqli_prepare($link, "UPDATE tt_registration SET status=?, remark=?, tt_resolved_date=?, tt=? WHERE branch_name=? AND tt_resolved_date=?");
            mysqli_stmt_bind_param($stmt_update2, "ssssss", $statuss, $remark, $closeddate, $TT, $branch, $resolved);
            mysqli_stmt_execute($stmt_update2);
            mysqli_stmt_close($stmt_update2);


            $success_message = "Single branch updated successfully.";
        } else {
            $error_message = "Please select a branch.";

        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Active TTs Management</title>
<link rel="stylesheet" href="all_active_tts_admin.css">
</head>
<body>
<div class="container">



<!-- Bulk Update Table -->
<?php if (mysqli_num_rows($result) > 0): ?>
<div class="table">
<h2>Active TTs - Bulk Update</h2>
<!-- Success/Error Messages -->
<?php if (!empty($success_message) || !empty($error_message)): ?>
<div id="message-box" class="<?= !empty($success_message) ? 'success' : 'error' ?>">
    <p><?= htmlspecialchars($success_message ?: $error_message, ENT_QUOTES, 'UTF-8'); ?></p>
</div>
<?php endif; ?>
<form method="post">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<table>
    <thead>
        <tr>
            <th>Select</th><th>Branch</th><th>Service No</th><th>TT</th><th>WAN IP</th><th>LAN IP</th><th>Reg Date</th><th>Status</th><th>Remark</th><th>Days</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)):
            $day = $row['tt_reg_date'] ?? '';
            $days = ($day && strtotime($day)) ? floor((strtotime(date("Y-m-d")) - strtotime($day)) / (60*60*24)) : 'N/A';
        ?>
        <tr>
            <td><input type="checkbox" name="selected_tt[]" value="<?= htmlspecialchars($row['tt'], ENT_QUOTES, 'UTF-8') ?>"></td>
            <td><?= htmlspecialchars($row['branch_name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($row['service_number'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($row['tt'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($row['wanip'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($row['lanip'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($row['tt_reg_date'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><span class="clickable" onclick="openModal('<?= addslashes($row['status']) ?>')"><?= substr($row['status'],0,20) . (strlen($row['status'])>20?"...":"") ?></span></td>
            <td><span class="clickable" onclick="openModal('<?= addslashes($row['remark']) ?>')"><?= substr($row['remark'],0,20) . (strlen($row['remark'])>20?"...":"") ?></span></td>
            <td><?= $days ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<div class="update-btn">
<button type="submit" name="bulk_update">Close Selected</button>
</div>
</form>
</div>


<!-- Single Update Form -->
<div class="single-update">
<h2>Single Branch/TT Update</h2>
<form method="post">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

<label for="branch">Select Branch:</label>
<select name="branchh" required>
<option value="" selected disabled hidden>Please select...</option>
<?php while ($rowb = mysqli_fetch_assoc($result_branch)): ?>
<option value="<?= htmlspecialchars($rowb['branch_name'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($rowb['branch_name'], ENT_QUOTES, 'UTF-8') ?></option>
<?php endwhile; ?>
</select>

<label for="tt">TT (Optional):</label>
<input type="text" name="tt" pattern="^\d{16}$" maxlength="16" minlength="16">

<label for="status">Status:</label>
<select name="status">
<option value=""></option>
<option value="Escalated">Escalated</option>
<option value="closed">Closed</option>
</select>

<label for="remark_single">Remark:</label>
<select id="remark_select" name="remark_select" onchange="toggleRemark(this.value)" required>
<option value="">-- Select Remark --</option>
<option value="Issue Resolved">Issue Resolved</option>
<option value="TT Correction">Correct TT</option>
<option value="Closed Automatic">Closed Automatic</option>
<option value="Monitoring Ongoing">Monitoring Ongoing</option>
<option value="Pending Branch Response">Pending User Response</option>
<option value="Handling">Handling by ISP</option>
<option value="Area Power Issue">Area Power Issue</option>
<option value="Scheduled Maintenance">Scheduled Maintenance</option>
<option value="Other">Other</option>
</select>
<textarea id="remark" rows="4" cols="40" name="remark" style="display:none;"></textarea>

<button type="submit" name="save_changes">Save Changes</button>
</form>
</div>

<!-- Modal -->
<div id="myModal" class="modal">
<div class="modal-content">
<span id="close-modal" class="close-btn">&times;</span>
<p id="modal-text"></p>
</div>
</div>

</div>
<?php else: ?>
<p>No active TTs found.</p>
<?php endif; ?>

<script>
// Modal functionality
function openModal(text){
    document.getElementById("modal-text").innerText = text;
    document.getElementById("myModal").style.display = "block";
}
document.getElementById("close-modal").addEventListener("click", () => {
    document.getElementById("myModal").style.display = "none";
});
window.onclick = function(event){
    if(event.target == document.getElementById("myModal")){
        document.getElementById("myModal").style.display = "none";
    }
}

// Hide message after 3 seconds and redirect
setTimeout(() => {
    let messageBox = document.getElementById("message-box");
    if(messageBox) {
        messageBox.style.display = "none";
        // Only redirect if it was a success message
        if(messageBox.classList.contains("success")) {
            location.href = "all_active_tts_admin.php"; // <-- set your desired URL here
        }
    }
}, 3000);


// Remark dropdown toggle
function toggleRemark(value){
    const txtarea = document.getElementById("remark");
    if(value === "Other"){
        txtarea.style.display = "block"; txtarea.focus(); txtarea.value="";
    } else if(value){
        txtarea.style.display = "none"; txtarea.value = value;
    } else {
        txtarea.style.display = "none"; txtarea.value = "";
    }
}
</script>
</body>
</html>
