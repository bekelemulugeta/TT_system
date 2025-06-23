<?php
include_once("config.php");

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate and sanitize input
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($id === false || $id === null) {
        echo json_encode(["error" => "Invalid ID"]);
        http_response_code(400); // Bad Request
        exit;
    }

    // Prepare the SQL statement
    $query = "SELECT * FROM head_office_ip WHERE id = ?";
    $stmt = mysqli_prepare($link, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($row) {
            echo json_encode($row);
        } else {
            echo json_encode(["error" => "No record found"]);
            http_response_code(404); // Not Found
        }
    } else {
        echo json_encode(["error" => "Database error"]);
        http_response_code(500); // Internal Server Error
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
    http_response_code(405); // Method Not Allowed
}

// Close the database connection
mysqli_close($link);
?>
