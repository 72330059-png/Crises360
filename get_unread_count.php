<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include "db.php";

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if ($user_id <= 0) { echo json_encode(["count" => 0]); exit; }

$sql = "SELECT COUNT(*) as count
        FROM alerts
        WHERE status = 'Sent'
        AND id NOT IN (
            SELECT alert_id FROM user_alerts
            WHERE user_id = $user_id AND action = 'read'
        )";

$result = $conn->query($sql);
$row    = $result->fetch_assoc();
echo json_encode(["count" => (int)$row["count"]]);
$conn->close();
?>