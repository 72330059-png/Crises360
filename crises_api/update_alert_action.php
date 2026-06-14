<?php
include "db.php";

$user_id = $_POST['user_id'];
$alert_id = $_POST['alert_id'];
$action = $_POST['action'];

$sql = "INSERT INTO user_alerts (user_id, alert_id, action)
        VALUES ('$user_id', '$alert_id', '$action')";

if ($conn->query($sql)) {
    echo "success";
} else {
    echo "error";
}

$conn->close();
?>