<?php
include_once("config.php");

// Get the ID from the GET request
$id = $_GET['id'];

// Sanitize the ID to prevent SQL injection
$id = mysqli_real_escape_string($link, $id);

// Query to fetch service details by ID
$query = "SELECT id, branch_name, service_number, service_type, bw, wanip, lanip FROM service_info WHERE id = '$id'";
$result = mysqli_query($link, $query);

// Check if any data is found
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    // Return the data as a JSON response
    echo json_encode($row);
} else {
    echo json_encode(["error" => "No data found"]);
}
?>
