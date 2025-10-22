<?php
session_start();
include 'connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] != 'admin') {
    header("Location: index.html");
    exit;
}

// Check if the delete request is made
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'])) {
        $id = $_POST['id'];

        // Sanitize the input
        $id = mysqli_real_escape_string($conn, $id);

        // Delete the announcement
        $deleteQuery = "DELETE FROM announcements WHERE id = '$id'";
        if (mysqli_query($conn, $deleteQuery)) {
            // Set success message
            $_SESSION['error'] = "Announcement deleted successfully.";
            $_SESSION['message_type'] = 'success';
        } else {
            // Set error message for deletion failure
            $_SESSION['error'] = "Error deleting announcement.";
            $_SESSION['message_type'] = 'error';
        }
    } else {
        // Set error message for invalid request
        $_SESSION['error'] = "Invalid request for deletion.";
        $_SESSION['message_type'] = 'error';
    }
} else {
    // If the request method is not POST, redirect back
    header("Location: display_announcment.php");
    exit; // Ensure the script stops after redirecting
}

// Redirect back to the announcements page
header("Location: display_announcment.php");
exit; // Ensure the script stops after redirecting
?>
