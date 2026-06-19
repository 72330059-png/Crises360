<?php
session_start();
header("Content-Type: application/json");
require_once("../class/hospitals.class.php");

if (!defined('GROQ_API_KEY')) {
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath)) {
        foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') !== false) putenv(trim($line));
        }
    }
    define('GROQ_API_KEY', getenv('GROQ_API_KEY'));
}
error_log("GROQ_API_KEY is set: " . (GROQ_API_KEY ? 'YES' : 'NO'));  // ADD THIS

// ─── STEP 1: Ask Groq to identify location + variants ───────────────────────
function groqResolveLocation(string $locationText): ?array {
    $prompt = "You are a Lebanese geography expert. The user typed a location: \"$locationText\". Identify the real Lebanese village/city/area even if misspelled or in Arabic. Reply ONLY with valid JSON, no explanation, no markdown:\n{\"canonical\": \"standard English spelling\", \"variants\": [\"variant1\", \"variant2\", \"variant3\", \"variant4\"]}";

    $body = json_encode([
        'model'    => 'llama-3.1-8b-instant',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a Lebanese geography expert. Always reply ONLY with valid JSON containing canonical and variants. No explanation, no markdown.'],
            ['role' => 'user',   'content' => $prompt]
        ],
        'temperature' => 0.1,
        'max_tokens'  => 150
    ]);

    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . GROQ_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
      error_log("Groq curl error: " . curl_error($ch));  // ADD THIS
    error_log("Groq raw response: " . $response);      // ADD THIS
    curl_close($ch);

    $data = json_decode($response, true);
    $text = $data['choices'][0]['message']['content'] ?? '';
    $text = preg_replace('/```json\s*/i', '', $text);
    $text = preg_replace('/```\s*/i', '', $text);
    $result = json_decode(trim($text), true);
    error_log("Groq parsed result: " . print_r($result, true));  // ADD THIS

    if (!empty($result['canonical'])) return $result;
    return null;
}

// ─── STEP 2: Nominatim query ─────────────────────────────────────────────────
function nominatimQuery(string $q): ?array {
    $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
        'format'       => 'json',
        'q'            => $q,
        'limit'        => 1,
        'countrycodes' => 'lb',
    ]);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: Crisis360App/1.0']);
    $response = curl_exec($ch);
        error_log("Nominatim query: $q | curl error: " . curl_error($ch) . " | response: " . $response);  // ADD THIS

    curl_close($ch);
    usleep(100000);
    $data = json_decode($response, true);
    if (!empty($data)) {
        return ['lat' => (float)$data[0]['lat'], 'lng' => (float)$data[0]['lon']];
    }
    return null;
}

// ─── STEP 3: Smart geocode with variants ─────────────────────────────────────
function smartGeocode(string $location): array {
    // Ask Groq to resolve location name + variants
    $resolved = groqResolveLocation($location);

    $queries = [];
    if ($resolved) {
        $queries[] = $resolved['canonical'];
        foreach ($resolved['variants'] as $v) {
            if (!in_array($v, $queries)) $queries[] = $v;
        }
    }
    // Always try original input too
    if (!in_array($location, $queries)) $queries[] = $location;

    // Try each with and without ", Lebanon"
    $allQueries = [];
    foreach ($queries as $q) {
        $allQueries[] = $q;
        $allQueries[] = $q . ', Lebanon';
    }
    $allQueries = array_unique($allQueries);

    foreach ($allQueries as $q) {
        $coords = nominatimQuery($q);
        if ($coords) return ['lat' => $coords['lat'], 'lng' => $coords['lng'], 'found' => true];
    }

    return ['lat' => null, 'lng' => null, 'found' => false];
}

// ─── MAIN ─────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$name            = trim($_POST['name']            ?? '');
$location        = trim($_POST['location']        ?? '');
$email           = trim($_POST['email']           ?? '');
$password        = $_POST['password']             ?? '';
$total_beds      = $_POST['total_beds']           ?? 0;
$hospital_status = $_POST['hospital_status']      ?? 'Safe';

if (empty($name) || empty($location) || empty($email) || empty($password) || empty($total_beds)) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

// Check duplicate email
$hospital = new hospital();
$checkSql = "SELECT id FROM users WHERE email = ?";
$existing = $hospital->getRowSafe($checkSql, [$email]);
if ($existing) {
    echo json_encode(["success" => false, "message" => "email_duplicate"]);
    exit;
}

// Geocode
$coords = smartGeocode($location);
if (!$coords['found']) {
    echo json_encode(["success" => false, "message" => "location_not_found"]);
    exit;
}

$result = $hospital->insertHospital(
    $name, $location, $email, $password,
    $total_beds, $hospital_status,
    $coords['lat'], $coords['lng']
);

if ($result === true || is_numeric($result)) {
    echo json_encode(["success" => true, "message" => "Hospital added successfully"]);
} else {
    echo json_encode(["success" => false, "message" => $result]);
}