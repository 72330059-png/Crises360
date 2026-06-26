<?php
header('Content-Type: application/json');
require_once 'mailer_helper.php';

try {
    $sent = sendMail(getenv('MAIL_USERNAME'), 'Test Email', '<p>OAuth test</p>');
    echo json_encode(['status' => $sent ? 'success' : 'failed']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
