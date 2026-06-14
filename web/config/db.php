<?php

$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            putenv(trim($line));
        }
    }
}

if (!defined('RECAPTCHA_SECRET'))   define('RECAPTCHA_SECRET',   getenv('RECAPTCHA_SECRET'));
if (!defined('GMAIL_APP_PASSWORD')) define('GMAIL_APP_PASSWORD', getenv('GMAIL_APP_PASSWORD'));
if (!defined('GROQ_API_KEY'))       define('GROQ_API_KEY',       getenv('GROQ_API_KEY'));

return [
    'servername' => getenv('DB_HOST'),
    'username'   => getenv('DB_USERNAME'),
    'password'   => getenv('DB_PASSWORD'),
    'dbname'     => getenv('DB_NAME'),
    'port'       => getenv('DB_PORT'),
];