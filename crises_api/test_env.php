<?php
header('Content-Type: application/json');
echo json_encode([
    'MAIL_FROM'     => getenv('MAIL_FROM'),
    'MAIL_NAME'     => getenv('MAIL_NAME'),
    'MAIL_PASSWORD' => getenv('MAIL_PASSWORD') ? 'SET' : 'NOT SET',
    'php_version'   => phpversion()
]);
?>
