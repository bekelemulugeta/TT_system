<?php
require_once("config.php");

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $branch_name = trim($_POST['branch_name'] ?? '');
    $service_number = trim($_POST['service_number'] ?? '');
    $wanip = trim($_POST['wanip'] ?? '');
    $lanip = trim($_POST['lanip'] ?? '');
    
    // Check if 'bw' is set to "Other", then use 'other_bw' input value
    $bw = ($_POST['bw'] === "Other" && !empty($_POST['other_bandwidth'])) ? trim($_POST['other_bandwidth']) : trim($_POST['bw']);
    
    // Check if 'service_type' is set to "others", then use 'other_service_type' input value
    $service_type = ($_POST['service_type'] === "others" && !empty($_POST['other_service_type'])) ? trim($_POST['other_service_type']) : trim($_POST['service_type']);

    // Append "/24" to LAN IP
    $lanip .= "/24";

    // Ensure ID is valid
    if ($id <= 0) {
        echo "Invalid ID provided.";
        exit;
    }

    // Use a prepared statement for security
    $query = "UPDATE service_info SET 
                branch_name = ?, 
                service_number = ?, 
                service_type = ?, 
                bw = ?, 
                wanip = ?, 
                lanip = ? 
              WHERE id = ?";

    $stmt = mysqli_prepare($link, $query);
    if (!$stmt) {
        echo "Error preparing statement: " . mysqli_error($link);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "ssssssi", $branch_name, $service_number, $service_type, $bw, $wanip, $lanip, $id);

    if (mysqli_stmt_execute($stmt)) {
        echo "success";
    } else {
        echo "Database update failed: " . mysqli_error($link);
    }

    mysqli_stmt_close($stmt);
} else {
    echo "Invalid request method.";
}
?>
