<?php
date_default_timezone_set('Asia/Beirut');
header('Content-Type: application/json');
include 'db.php';

$full_name    = trim($_POST['full_name']    ?? '');
$national_id  = trim($_POST['national_id']  ?? '');
$email        = trim($_POST['email']        ?? '');
$password     = trim($_POST['password']     ?? '');

// ── Validation ────────────────────────────────────────────────
if (empty($full_name) || empty($national_id) || empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Full name, National ID, email and password are required"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "Invalid email address"]);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(["status" => "error", "message" => "Password must be at least 6 characters"]);
    exit;
}

// ── Check duplicate national ID ───────────────────────────────
$c1 = $conn->prepare("SELECT id FROM members WHERE national_id = ?");
$c1->bind_param("s", $national_id);
$c1->execute();
if ($c1->get_result()->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "This National ID is already registered"]);
    exit;
}

// ── Check duplicate email ─────────────────────────────────────
$c2 = $conn->prepare("SELECT id FROM members WHERE email = ?");
$c2->bind_param("s", $email);
$c2->execute();
if ($c2->get_result()->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "This email is already registered"]);
    exit;
}

// ── Hash password ─────────────────────────────────────────────
$hashed = password_hash($password, PASSWORD_DEFAULT);

// ── Insert new member ─────────────────────────────────────────
// Only full_name, national_id, email, password at registration.
// Everything else (phone, dob, etc.) is filled later from the dashboard.
$stmt = $conn->prepare("
    INSERT INTO members (full_name, national_id, email, password)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("ssss", $full_name, $national_id, $email, $hashed);

if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Account created successfully. Please log in."
    ]);
} else {
    echo json_encode([
        "status"  => "error",
        "message" => "Database error: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>