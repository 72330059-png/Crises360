<?php
date_default_timezone_set('Asia/Beirut');
header('Content-Type: application/json');
include 'db.php';

$user_id = intval($_POST['user_id'] ?? 0);

if ($user_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid user ID"]);
    exit;
}

$stmt = $conn->prepare("
    SELECT full_name, national_id, phone, gender, dob,
           family_status, blood_group, father_name,
           mother_name, country, place_of_birth
    FROM members WHERE id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    echo json_encode(["status" => "error", "message" => "Member not found"]);
    exit;
}

echo json_encode(["status" => "success", "data" => $row]);
$conn->close();
?>