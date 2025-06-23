<?php
include_once('config.php');

// Check if an ID is passed via GET request
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Generate a temporary password
    $temp_password = 'gbe@123'; 

    // Hash the password for security (even though hashing is not required in your case, it's a good practice)
    $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);

    // Update the user's password in the database
    $sql = "UPDATE user SET password = ? WHERE id = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param('si', $hashed_password, $user_id); // s = string, i = integer
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Return success response
            echo "Password reset successfully. Temporary password: " . $temp_password;
        } else {
            echo "Error: User not found or no changes made.";
        }
        $stmt->close();
    } else {
        echo "Error: Could not prepare query.";
    }
} else {
    echo "Error: No user ID provided.";
}

$link->close();
?>
