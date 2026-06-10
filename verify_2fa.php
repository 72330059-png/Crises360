<?php
date_default_timezone_set('Asia/Beirut');
header('Content-Type: application/json');
include 'db.php';

$email = trim($_POST['email'] ?? '');
$code  = trim($_POST['code']  ?? '');

// ── Validation ────────────────────────────────────────────────
if (empty($email) || empty($code)) {
    echo json_encode(["status" => "error", "message" => "Email and code are required"]);
    exit;
}

// ── Find user and code ────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT id, verify_code, verify_expiry
    FROM members
    WHERE email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    echo json_encode(["status" => "error", "message" => "Account not found"]);
    exit;
}

// ── Check expiry ──────────────────────────────────────────────
if (empty($row['verify_expiry']) || strtotime($row['verify_expiry']) < time()) {
    echo json_encode(["status" => "error", "message" => "Code has expired. Please request a new one."]);
    exit;
}

// ── Check code ────────────────────────────────────────────────
if ($row['verify_code'] !== $code) {
    echo json_encode(["status" => "error", "message" => "Incorrect code. Please try again."]);
    exit;
}

// ── Clear code after success ──────────────────────────────────
$clear = $conn->prepare("
    UPDATE members
    SET verify_code = NULL, verify_expiry = NULL
    WHERE id = ?
");
$clear->bind_param("i", $row['id']);
$clear->execute();

// ── Return success ────────────────────────────────────────────
echo json_encode([
    "status"  => "success",
    "message" => "Verified successfully"
]);

$conn->close();
?>