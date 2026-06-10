<?php
date_default_timezone_set('Asia/Beirut');
header('Content-Type: application/json');
include 'db.php';
require_once 'mailer_helper.php';

$email = trim($_POST['email'] ?? '');

// ── Validation ────────────────────────────────────────────────
if (empty($email)) {
    echo json_encode(["status" => "error", "message" => "Email is required"]);
    exit;
}

// ── Find user ─────────────────────────────────────────────────
$stmt = $conn->prepare("SELECT id, full_name FROM members WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

// ── Always return success (don't reveal if email exists) ──────
if (!$row) {
    echo json_encode([
        "status"  => "success",
        "message" => "If this email is registered, a reset link has been sent."
    ]);
    exit;
}

// ── Generate reset token ──────────────────────────────────────
$token  = bin2hex(random_bytes(32));
$expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

// ── Save token to database ────────────────────────────────────
$update = $conn->prepare("
    UPDATE members
    SET reset_token = ?, reset_token_expiry = ?
    WHERE id = ?
");
$update->bind_param("ssi", $token, $expiry, $row['id']);
$update->execute();

// ── Build reset link ──────────────────────────────────────────
// Change this to your actual server URL when going live
$resetLink = "http://localhost/crises_api/reset_password.php?token=" . $token;

// ── Send email ────────────────────────────────────────────────
$name    = $row['full_name'];
$subject = "Crises App — Password Reset Request";
$body    = "
<div style='font-family:sans-serif;max-width:460px;margin:auto;padding:32px;
            border:1px solid #eee;border-radius:16px'>
  <h2 style='color:#2d5a27'>Password Reset</h2>
  <p>Hello <strong>{$name}</strong>,</p>
  <p style='color:#555'>
    We received a request to reset your password.
    Click the button below — the link expires in <strong>1 hour</strong>.
  </p>
  <div style='text-align:center;margin:28px 0'>
    <a href='{$resetLink}'
       style='padding:14px 32px;background:#2d5a27;color:white;
              border-radius:8px;text-decoration:none;font-weight:bold;font-size:15px'>
      Reset My Password
    </a>
  </div>
  <p style='color:#999;font-size:12px'>
    If you did not request this, you can safely ignore this email.
    Your password will not change.
  </p>
</div>
";

$sent = sendMail($email, $subject, $body);

echo json_encode([
    "status"  => "success",
    "message" => "If this email is registered, a reset link has been sent."
]);

$conn->close();
?>