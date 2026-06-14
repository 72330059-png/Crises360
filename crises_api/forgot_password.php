<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Beirut');
header('Content-Type: application/json');
include 'db.php';

$email = trim($_POST['email'] ?? '');

// ── Validation ────────────────────────────────────────────────
if (empty($email)) {
    echo json_encode(["status" => "error", "message" => "Email is required"]);
    exit;
}

// ── Check email exists ────────────────────────────────────────
$check = $conn->prepare("SELECT id, full_name FROM members WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$row = $check->get_result()->fetch_assoc();

if (!$row) {
    echo json_encode(["status" => "error", "message" => "Email not found"]);
    exit;
}

// ── Generate reset token ──────────────────────────────────────
$token  = bin2hex(random_bytes(32));
$expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

// ── Save token to database ────────────────────────────────────
$update = $conn->prepare("
    UPDATE members
    SET reset_token = ?, reset_token_expiry = ?
    WHERE email = ?
");
$update->bind_param("sss", $token, $expiry, $email);
$update->execute();

// ── Reset link ────────────────────────────────────────────────
$resetLink = "https://crises360-mobile-api.onrender.com/reset_password.php?token=" . $token;

// ── Email body ────────────────────────────────────────────────
$name = $row['full_name'];
$body = "
<div style='font-family:sans-serif;max-width:460px;margin:auto;padding:32px;
            border:1px solid #eee;border-radius:16px;text-align:center'>
  <h2 style='color:#2d5a27;margin-bottom:8px'>Password Reset</h2>
  <p style='color:#555'>Hello <strong>{$name}</strong>,</p>
  <p style='color:#555;margin-bottom:24px'>Click the button below to reset your password:</p>
  <a href='{$resetLink}' 
     style='display:inline-block;background:#2d5a27;color:#fff;padding:14px 28px;
            border-radius:8px;text-decoration:none;font-weight:bold;font-size:16px'>
    Reset Password
  </a>
  <p style='color:#999;font-size:12px;margin-top:20px'>
    This link expires in <strong>1 hour</strong>.<br>
    If you did not request this, ignore this email.
  </p>
</div>
";

// ── Get Gmail Access Token ────────────────────────────────────
function getGmailAccessToken() {
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id'     => getenv('GMAIL_CLIENT_ID'),
        'client_secret' => getenv('GMAIL_CLIENT_SECRET'),
        'refresh_token' => getenv('GMAIL_REFRESH_TOKEN'),
        'grant_type'    => 'refresh_token'
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return $res['access_token'] ?? null;
}

// ── Send Email via Gmail API ──────────────────────────────────
$access_token = getGmailAccessToken();

if (!$access_token) {
    echo json_encode(["status" => "error", "message" => "Could not get access token"]);
    exit;
}

$sender  = getenv('GMAIL_SENDER');
$subject = "Crisis360 - Password Reset";

$raw  = "From: Crises App <{$sender}>\r\n";
$raw .= "To: {$email}\r\n";
$raw .= "Subject: {$subject}\r\n";
$raw .= "MIME-Version: 1.0\r\n";
$raw .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
$raw .= $body;

$encoded = rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');

$ch = curl_init('https://gmail.googleapis.com/gmail/v1/users/me/messages/send');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['raw' => $encoded]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $access_token,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$result   = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$sent = ($httpCode == 200);

if ($sent) {
    echo json_encode(["status" => "success", "message" => "Password reset link sent to your email"]);
} else {
    echo json_encode([
        "status"  => "error",
        "message" => "Could not send email",
        "debug"   => json_decode($result, true)
    ]);
}

$conn->close();
?>
