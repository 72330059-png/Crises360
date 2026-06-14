<?php
date_default_timezone_set('Asia/Beirut');
header('Content-Type: application/json');
include 'db.php';
require_once 'gmail_helper.php'; 

$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');

// ── Validation ────────────────────────────────────────────────
if (empty($email) || empty($password)) {
    echo json_encode([
        "status" => "error",
        "message" => "Email and password are required"
    ]);
    exit;
}

// ── Find user by email ────────────────────────────────────────
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
    echo json_encode([
        "status" => "error",
        "message" => "No account found with this email"
    ]);
    exit;
}

// ── Check password ────────────────────────────────────────────
if (!password_verify($password, $row['password'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Incorrect password"
    ]);
    exit;
}

// ── Profile completeness ──────────────────────────────────────
$profileComplete = !empty($row['phone'])
                && !empty($row['dob'])
                && !empty($row['blood_group']);

// ── Generate OTP ──────────────────────────────────────────────
$code   = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// ── Save OTP ──────────────────────────────────────────────────
$update = $conn->prepare("
    UPDATE members 
    SET verify_code = ?, verify_expiry = ? 
    WHERE id = ?
");
$update->bind_param("ssi", $code, $expiry, $row['id']);
$update->execute();

// ── Send Email ────────────────────────────────────────────────
$subject = "Your Crises App Login Code";

$body = "
Hello {$row['full_name']},<br><br>
Your verification code is:<br><br>
<b style='font-size:24px;'>$code</b><br><br>
This code expires in 10 minutes.
";

sendEmail($email, $subject, $body);

// ── Require OTP (NOT logged in yet) ───────────────────────────
echo json_encode([
    "status"           => "otp_required",
    "user_id"          => $row['id'],
    "full_name"        => $row['full_name'],
    "national_id"      => $row['national_id'],
    "profile_complete" => $profileComplete,
    "message"          => "Verification code sent to your email"
]);

$conn->close();
?>
