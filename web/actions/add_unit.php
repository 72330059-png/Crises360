<?php
header("Content-Type: application/json");
require_once("../class/police.class.php");

// define('GROQ_API_KEY', 'GROQ_API_KEY_Secret');
// define('GROQ_API_KEY', getenv('GROQ_API_KEY'));

function nominatimGeocode($location) {
    $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
        'format'       => 'json',
        'q'            => $location . ', Lebanon',
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
    $data = json_decode($response, true);
    if (!empty($data)) {
        return ['lat' => (float)$data[0]['lat'], 'lng' => (float)$data[0]['lon']];
    }
    return null;
}

function groqGeocode($location) {
    $prompt = "Give me the latitude and longitude of \"$location\" in Lebanon. Reply ONLY with valid JSON like this: {\"lat\": 33.8938, \"lng\": 35.5018}. No explanation, no markdown, nothing else.";
    $body = json_encode([
        'model'    => 'llama-3.1-8b-instant',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a Lebanese geography expert. Always reply ONLY with valid JSON containing lat and lng.'],
            ['role' => 'user',   'content' => $prompt]
        ],
        'temperature' => 0.1,
        'max_tokens'  => 50
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
    $coords = json_decode(trim($text), true);
    if (!empty($coords['lat']) && !empty($coords['lng'])) {
        return ['lat' => (float)$coords['lat'], 'lng' => (float)$coords['lng']];
    }
    return null;
}

function getCoordinates($location) {
    $coords = nominatimGeocode($location);
    if ($coords) return $coords;
    $coords = groqGeocode($location);
    if ($coords) return $coords;
    return ['lat' => null, 'lng' => null];
}

// ============================================================

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$police = new police();
$name      = $police->clean($_POST['organization_name'] ?? '');
$location  = $police->clean($_POST['location']          ?? '');
$email     = $police->clean($_POST['email']             ?? '');
$password  = $police->clean($_POST['password']          ?? '');
$callsign  = $police->clean($_POST['callsign']          ?? '');
$unit_type = $police->clean($_POST['unit_type']         ?? '');

if (empty($name) || empty($location) || empty($email) || empty($password) || empty($callsign) || empty($unit_type)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

// GET LAT/LNG
$coords = getCoordinates($location);
$lat = $coords['lat'];
$lng = $coords['lng'];

$result = $police->addPoliceUnit($name, $location, $email, $password, $callsign, $unit_type, $lat, $lng);

if ($result) {
    echo json_encode(['status' => 'success', 'message' => 'Police unit added successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add unit']);
}