<?php
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

// ── Generate 6-digit code ─────────────────────────────────────
$code   = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// ── Save code to database ─────────────────────────────────────
$update = $conn->prepare("
    UPDATE members
    SET verify_code = ?, verify_expiry = ?
    WHERE email = ?
");
$update->bind_param("sss", $code, $expiry, $email);
$update->execute();

// ── Email body ────────────────────────────────────────────────
$name = $row['full_name'];
$body = "
<div style='font-family:sans-serif;max-width:460px;margin:auto;padding:32px;
            border:1px solid #eee;border-radius:16px;text-align:center'>
  <h2 style='color:#2d5a27;margin-bottom:8px'>Login Verification</h2>
  <p style='color:#555'>Hello <strong>{$name}</strong>,</p>
  <p style='color:#555;margin-bottom:24px'>Your one-time login code is:</p>
  <div style='font-size:44px;font-weight:bold;letter-spacing:14px;
              color:#2d5a27;background:#f4f9f4;padding:22px;border-radius:12px'>
    {$code}
  </div>
  <p style='color:#999;font-size:12px;margin-top:20px'>
    This code expires in <strong>10 minutes</strong>.<br>
    If you did not request this, ignore this email.
  </p>
</div>
";

// ── Send via Elastic Email API ────────────────────────────────
$ch = curl_init("https://api.elasticemail.com/v2/email/send");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST,           true);
curl_setopt($ch, CURLOPT_POSTFIELDS,     http_build_query([
    "apikey"          => "815CAB57B3F633A2E70D0BC1134996A344EED4FD2FFE821BB13C252E0AA026A23FA2AAC1826E9F080C23BFAB563FE11A",
    "from"            => "ayoubsaja176@gmail.com",
    "fromName"        => "Crises App",
    "to"              => $email,
    "subject"         => "Your Crises App Login Code",
    "bodyHtml"        => $body,
    "isTransactional" => true
]));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT,        30);

$result    = curl_exec($ch);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode(["status" => "error", "message" => "curl error: " . $curlError]);
    exit;
}

$resultData = json_decode($result, true);
$sent = isset($resultData["success"]) && $resultData["success"] === true;

if ($sent) {
    echo json_encode(["status" => "success", "message" => "Code sent to your email"]);
} else {
    echo json_encode([
        "status"  => "error",
        "message" => "Could not send email",
        "debug"   => $resultData
    ]);
}

$conn->close();
?>
