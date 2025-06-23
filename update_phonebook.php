<?php
include_once("config.php");

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    
    $id = $_POST['id'];
    $branch_name = mysqli_real_escape_string($link, $_POST['branch_name']);
    $office = mysqli_real_escape_string($link, $_POST['office']);
    $manager = mysqli_real_escape_string($link, $_POST['manager']);
    $mphone = mysqli_real_escape_string($link, $_POST['mphone']);
    $accountant = mysqli_real_escape_string($link, $_POST['accountant']);
    $aphone = mysqli_real_escape_string($link, $_POST['aphone']);

    $query = "UPDATE phonebook SET 
              branch_name = '$branch_name', 
              office = '$office', 
              manager = '$manager', 
              mphone = '$mphone', 
              accountant = '$accountant', 
              aphone = '$aphone' 
              WHERE id = '$id'";

    if (mysqli_query($link, $query)) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
