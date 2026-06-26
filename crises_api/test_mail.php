<?php
header('Content-Type: application/json');

require_once 'PHPMailer/Exception.php';
require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';

echo json_encode(['status' => 'includes ok']);
?>
