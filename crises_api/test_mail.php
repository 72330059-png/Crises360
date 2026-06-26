<?php
date_default_timezone_set('Asia/Beirut');
header('Content-Type: application/json');
require_once 'mailer_helper.php';

$result = [];
$result['MAIL_FROM']     = getenv('MAIL_FROM');
$result['MAIL_NAME']     = getenv('MAIL_NAME');
$result['MAIL_PASSWORD'] = getenv('MAIL_PASSWORD') ? 'SET' : 'NOT SET';

$sent = false;
try {
    $sent = sendMail(getenv('MAIL_FROM'), "Test", "<p>Test email</p>");
} catch (Exception $e) {
    $result['exception'] = $e->getMessage();
}

$result['sent'] = $sent;
echo json_encode($result);
?>
