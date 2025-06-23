<?php
include_once("config.php");

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $query = "DELETE FROM service_info WHERE id = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        echo "success";
    } else {
        echo "error";
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($link);
?>
