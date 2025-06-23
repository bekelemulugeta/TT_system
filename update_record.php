<?php
require_once("config.php");

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        echo json_encode(["error" => "Invalid ID"]);
        http_response_code(400);
        exit;
    }


    $User_Name = filter_input(INPUT_POST, 'User_Name', FILTER_SANITIZE_STRING);
    $Department = filter_input(INPUT_POST, 'Department', FILTER_SANITIZE_STRING);
    $Computer_Model = filter_input(INPUT_POST, 'Computer_Model', FILTER_SANITIZE_STRING);
    $Computer_Name = filter_input(INPUT_POST, 'Computer_Name', FILTER_SANITIZE_STRING);
    $Ip = filter_input(INPUT_POST, 'Ip', FILTER_SANITIZE_STRING);
    $Flex = filter_input(INPUT_POST, 'Flex', FILTER_SANITIZE_STRING);
    $Rep = filter_input(INPUT_POST, 'Rep', FILTER_SANITIZE_STRING);
    $Local = filter_input(INPUT_POST, 'Local', FILTER_SANITIZE_STRING);
    $NBE = filter_input(INPUT_POST, 'NBE', FILTER_SANITIZE_STRING);
    $Internet = filter_input(INPUT_POST, 'Internet', FILTER_SANITIZE_STRING);

    $query = "UPDATE head_office_ip 
              SET User_Name=?, Department=?, Computer_Model=?, Computer_Name=?, Ip=?, 
                  Flex=?, Rep=?, Local=?, NBE=?, Internet=? 
              WHERE id=?";

    $stmt = mysqli_prepare($link, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssssssssi", 
            $User_Name, $Department, $Computer_Model, $Computer_Name, $Ip, 
            $Flex, $Rep, $Local, $NBE, $Internet, $id
        );

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => "Record updated successfully"]);
            http_response_code(200);
        } else {
            echo json_encode(["error" => "Database update failed"]);
            http_response_code(500);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(["error" => "Database error"]);
        http_response_code(500);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
    http_response_code(405);
}

mysqli_close($link);
?>
