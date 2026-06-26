<?php
date_default_timezone_set('Asia/Beirut');
header('Content-Type: application/json');

try {
    require_once 'mailer_config.php';
    require_once 'PHPMailer/Exception.php';
    require_once 'PHPMailer/PHPMailer.php';
    require_once 'PHPMailer/SMTP.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = getenv('MAIL_HOST');
    $mail->SMTPAuth   = true;
    $mail->Username   = getenv('MAIL_USERNAME');
    $mail->Password   = getenv('MAIL_PASSWORD');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->setFrom(getenv('MAIL_USERNAME'), 'Crises App');
    $mail->addAddress(getenv('MAIL_USERNAME'));
    $mail->isHTML(true);
    $mail->Subject = 'Test';
    $mail->Body    = '<p>Test email</p>';
    $mail->send();

    echo json_encode(['status' => 'success', 'message' => 'Email sent!']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} catch (Error $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
