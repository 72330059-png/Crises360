<?php
// TEMP DIAGNOSTIC — run this directly in browser: yourdomain.com/path/diagnose.php
// Delete this file when done. Do not leave it on a public server.
require_once("../class/municipality.class.php");
header('Content-Type: text/plain');

echo "=== STEP 1: Is GROQ_API_KEY defined as a constant? ===\n";
echo defined('GROQ_API_KEY') ? "YES, length=" . strlen(GROQ_API_KEY) . "\n" : "NO - this is likely your bug\n";

echo "\n=== STEP 2: Is it available via getenv() / \$_ENV instead? ===\n";
echo "getenv('GROQ_API_KEY'): " . (getenv('GROQ_API_KEY') ? "FOUND (length " . strlen(getenv('GROQ_API_KEY')) . ")" : "not found") . "\n";
echo "\$_ENV['GROQ_API_KEY']: " . (isset($_ENV['GROQ_API_KEY']) ? "FOUND (length " . strlen($_ENV['GROQ_API_KEY']) . ")" : "not found") . "\n";

echo "\n=== STEP 3: Test Nominatim directly for 'Saida' ===\n";
$url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
    'format' => 'json', 'q' => 'Saida, Lebanon',
    'limit' => 1, 'countrycodes' => 'lb',
]);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: Crisis360App/1.0']);
$response = curl_exec($ch);
$err = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "URL: $url\n";
echo "HTTP code: $httpCode\n";
echo "cURL error: " . ($err ?: 'none') . "\n";
echo "Raw response: " . var_export($response, true) . "\n";

echo "\n=== STEP 4: Test Groq directly (only if key was found above) ===\n";
$key = defined('GROQ_API_KEY') ? GROQ_API_KEY : (getenv('GROQ_API_KEY') ?: ($_ENV['GROQ_API_KEY'] ?? null));
if (!$key) {
    echo "SKIPPED - no API key available in any form\n";
} else {
    $body = json_encode([
        'model' => 'llama-3.1-8b-instant',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a Lebanese geography expert. Always reply ONLY with valid JSON containing lat and lng.'],
            ['role' => 'user', 'content' => 'Give me the latitude and longitude of "Saida" in Lebanon. Reply ONLY with valid JSON like this: {"lat": 33.8938, "lng": 35.5018}. No explanation, no markdown, nothing else.']
        ],
        'temperature' => 0.1, 'max_tokens' => 50
    ]);
    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $key
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP code: $httpCode\n";
    echo "cURL error: " . ($err ?: 'none') . "\n";
    echo "Raw response: " . var_export($response, true) . "\n";
}

echo "\n=== STEP 5: PHP version + error display settings ===\n";
echo "PHP version: " . PHP_VERSION . "\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "allow_url_fopen: " . ini_get('allow_url_fopen') . "\n";

echo "\n=== STEP 6: Outbound network check (can this server reach the internet at all?) ===\n";
$test = @file_get_contents('https://api.ipify.org');
echo "Outbound test result: " . ($test ?: 'FAILED - server may be blocking outbound HTTPS requests') . "\n";