<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include "db.php";

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if ($user_id <= 0) { echo json_encode(["status" => "error"]); exit; }

// Get all Sent alert IDs not yet marked read by this user
$sql = "SELECT id FROM alerts
        WHERE status = 'Sent'
        AND id NOT IN (
            SELECT alert_id FROM user_alerts
            WHERE user_id = $user_id AND action = 'read'
        )";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $alert_id = (int)$row['id'];
    $conn->query("INSERT INTO user_alerts (user_id, alert_id, action)
                  VALUES ($user_id, $alert_id, 'read')");
}

echo json_encode(["status" => "ok"]);
$conn->close();
?>