<?php
date_default_timezone_set('Asia/Beirut');
header('Content-Type: application/json');
include 'db.php';

$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');

// Validation 
if (empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Email and password are required"]);
    exit;
}

// Find user by email 
$stmt = $conn->prepare("
    SELECT id, password, full_name, national_id,
           phone, dob, blood_group
    FROM members
    WHERE email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    echo json_encode(["status" => "error", "message" => "No account found with this email"]);
    exit;
}

// Check password
if (!password_verify($password, $row['password'])) {
    echo json_encode(["status" => "error", "message" => "Incorrect password"]);
    exit;
}

//  Profile is complete if phone + dob + blood_group filled
$profileComplete = !empty($row['phone'])
                && !empty($row['dob'])
                && !empty($row['blood_group']);

// ── Success — do NOT set isLoggedIn yet, 2FA comes next 
echo json_encode([
    "status"           => "success",
    "user_id"          => $row['id'],
    "full_name"        => $row['full_name'],
    "national_id"      => $row['national_id'],
    "profile_complete" => $profileComplete
]);
?>
