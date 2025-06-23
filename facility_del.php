<?php
include_once("config.php");

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    
    // Use prepared statement to safely delete the row
    $query = "DELETE FROM head_office_ip WHERE id = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo 'success'; // Return success if row deleted
    } else {
        echo 'error'; // Return error if something goes wrong
    }
}
?>
