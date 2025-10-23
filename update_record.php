<?php
require_once("config.php");

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die(json_encode(["error" => "CSRF token validation failed."]));
    }

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        echo json_encode(["error" => "Invalid ID"]);
        http_response_code(400);
        exit;
    }

    // Safely get all input fields, default empty string if not set
    $User_Name      = isset($_POST['User_Name']) ? trim($_POST['User_Name']) : '';
    $Department     = isset($_POST['Department']) ? trim($_POST['Department']) : '';
    $Computer_Model = isset($_POST['Computer_Model']) ? trim($_POST['Computer_Model']) : '';
    $Computer_Name  = isset($_POST['Computer_Name']) ? trim($_POST['Computer_Name']) : '';
    $Ip             = isset($_POST['Ip']) ? trim($_POST['Ip']) : '';
    $Flex           = isset($_POST['Flex']) ? trim($_POST['Flex']) : '';
    $Rep            = isset($_POST['Rep']) ? trim($_POST['Rep']) : '';
    $Local          = isset($_POST['Local']) ? trim($_POST['Local']) : '';
    $NBE            = isset($_POST['NBE']) ? trim($_POST['NBE']) : '';
    $Internet       = isset($_POST['Internet']) ? trim($_POST['Internet']) : '';
    $remark         = isset($_POST['remark']) ? trim($_POST['remark']) : '';

    $query = "UPDATE head_office_ip 
              SET User_Name=?, Department=?, Computer_Model=?, Computer_Name=?, Ip=?, 
                  Flex=?, Rep=?, Local=?, NBE=?, Internet=?, remark=? 
              WHERE id=?";

    $stmt = mysqli_prepare($link, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssssssssssi", 
            $User_Name, $Department, $Computer_Model, $Computer_Name, $Ip, 
            $Flex, $Rep, $Local, $NBE, $Internet, $remark, $id
        );

        if (mysqli_stmt_execute($stmt)) {
            // Return updated row data
            echo json_encode([
                "success" => true,
                "data" => [
                    "id" => $id,
                    "User_Name" => $User_Name,
                    "Department" => $Department,
                    "Computer_Model" => $Computer_Model,
                    "Computer_Name" => $Computer_Name,
                    "Ip" => $Ip,
                    "Flex" => $Flex,
                    "Rep" => $Rep,
                    "Local" => $Local,
                    "NBE" => $NBE,
                    "Internet" => $Internet,
                    "remark" => $remark
                ]
            ]);
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
