<?php
session_start();
header("Content-Type: application/json");
require_once("../class/police.class.php");

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
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    $text = $data['choices'][0]['message']['content'] ?? '';
    $text = preg_replace('/```json\s*/i', '', $text);
    $text = preg_replace('/```\s*/i', '', $text);
    $result = json_decode(trim($text), true);

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
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: Crisis360App/1.0']);
    $response = curl_exec($ch);
    curl_close($ch);
    usleep(300000);
    $data = json_decode($response, true);
    if (!empty($data)) {
        return ['lat' => (float)$data[0]['lat'], 'lng' => (float)$data[0]['lon']];
    }
    return null;
}

// ─── STEP 3: Smart geocode with variants ─────────────────────────────────────
function smartGeocode(string $location): array {
    $resolved = groqResolveLocation($location);

    $queries = [];
    if ($resolved) {
        $queries[] = $resolved['canonical'];
        foreach ($resolved['variants'] as $v) {
            if (!in_array($v, $queries)) $queries[] = $v;
        }
    }
    if (!in_array($location, $queries)) $queries[] = $location;

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
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$police    = new police();
$name      = $police->clean($_POST['organization_name'] ?? '');
$location  = $police->clean($_POST['location']          ?? '');
$email     = $police->clean($_POST['email']             ?? '');
$password  = $_POST['password']                         ?? '';
$callsign  = $police->clean($_POST['callsign']          ?? '');
$unit_type = $police->clean($_POST['unit_type']         ?? '');

if (empty($name) || empty($location) || empty($email) || empty($password) || empty($callsign) || empty($unit_type)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

// Check duplicate email
$checkSql = "SELECT id FROM users WHERE email = ?";
$existing = $police->getRowSafe($checkSql, [$email]);
if ($existing) {
    echo json_encode(['status' => 'error', 'message' => 'email_duplicate']);
    exit;
}

// Geocode
$coords = smartGeocode($location);
if (!$coords['found']) {
    echo json_encode(['status' => 'error', 'message' => 'location_not_found']);
    exit;
}

$result = $police->addPoliceUnit($name, $location, $email, $password, $callsign, $unit_type, $coords['lat'], $coords['lng']);

if ($result) {
    echo json_encode(['status' => 'success', 'message' => 'Police unit added successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add unit']);
}