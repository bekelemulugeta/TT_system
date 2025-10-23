<?php
include_once("config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
   
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "CSRF validation failed.";
        exit;
    }

    $tt = trim($_POST['tt']);
    $updated_by = $_SESSION['login_user'] ?? 'unknown';
    $today = date("Y-m-d");
    $general_remark = "TT checked and closed";

    mysqli_begin_transaction($link);
    $stmt_fetch = mysqli_prepare($link, "SELECT status, remark FROM tt_registration WHERE tt = ? AND tt_resolved_date = '0000-00-00'");
    mysqli_stmt_bind_param($stmt_fetch, "s", $tt);
    mysqli_stmt_execute($stmt_fetch);
    $res = mysqli_stmt_get_result($stmt_fetch);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt_fetch);

    if (!$row) {
        echo "TT not found or already closed.";
        exit;
    }

    $new_status = ($row['status'] ?? '') . " ,Closed on $today by $updated_by";
    $new_remark = ($row['remark'] ?? '') . ", " . $general_remark;

    $stmt_update = mysqli_prepare($link, "UPDATE tt_registration SET status=?, remark=?, tt_resolved_date=? WHERE tt=?");
    mysqli_stmt_bind_param($stmt_update, "ssss", $new_status, $new_remark, $today, $tt);
    mysqli_stmt_execute($stmt_update);
    mysqli_stmt_close($stmt_update);
    mysqli_commit($link);

    echo "success";
}
?>
