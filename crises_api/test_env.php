<?php
header('Content-Type: application/json');
echo json_encode([
    'MAIL_USERNAME' => getenv('MAIL_USERNAME'),
    'MAIL_HOST'     => getenv('MAIL_HOST'),
    'MAIL_PASSWORD' => getenv('MAIL_PASSWORD') ? 'SET' : 'NOT SET',
    'php_version'   => phpversion()
]);
?>
