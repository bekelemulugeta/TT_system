<?php
include_once("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    $id = intval($_POST["id"]);
    $service_number = htmlspecialchars($_POST["service_number"]);
    $ip = htmlspecialchars($_POST["ip"]);
    $imei = htmlspecialchars($_POST["imei"]);
    $sim_id = htmlspecialchars($_POST["sim_id"]);

    $stmt = $link->prepare("UPDATE `4g` SET service_number=?, Ip=?, IMEI=?, sim_id=? WHERE id=?");
    $stmt->bind_param("ssssi", $service_number, $ip, $imei, $sim_id, $id);

    if ($stmt->execute()) {
        echo "Success";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
