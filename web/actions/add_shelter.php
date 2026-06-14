<?php
session_start();
header('Content-Type: application/json');
require_once("../class/municipality.class.php");

// define('GROQ_API_KEY', 'GROQ_API_KEY_Secret');
// define('GROQ_API_KEY', getenv('GROQ_API_KEY'));

function nominatimGeocode($location) {
    $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
        'format' => 'json', 'q' => $location . ', Lebanon',
        'limit' => 1, 'countrycodes' => 'lb',
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
    if (!empty($data)) return ['lat' => (float)$data[0]['lat'], 'lng' => (float)$data[0]['lon']];
    return null;
}

function groqGeocode($location) {
    $prompt = "Give me the latitude and longitude of \"$location\" in Lebanon. Reply ONLY with valid JSON like this: {\"lat\": 33.8938, \"lng\": 35.5018}. No explanation, no markdown, nothing else.";
    $body = json_encode([
        'model' => 'llama-3.1-8b-instant',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a Lebanese geography expert. Always reply ONLY with valid JSON containing lat and lng.'],
            ['role' => 'user', 'content' => $prompt]
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
        'Authorization: Bearer ' . GROQ_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    $text = preg_replace('/```json\s*/i', '', $data['choices'][0]['message']['content'] ?? '');
    $text = preg_replace('/```\s*/i', '', $text);
    $coords = json_decode(trim($text), true);
    if (!empty($coords['lat']) && !empty($coords['lng']))
        return ['lat' => (float)$coords['lat'], 'lng' => (float)$coords['lng']];
    return null;
}

function getCoordinates($location) {
    $coords = nominatimGeocode($location);
    if ($coords) return $coords;
    $coords = groqGeocode($location);
    if ($coords) return $coords;
    return ['lat' => null, 'lng' => null];
}

if (!isset($_SESSION['logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit;
}
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']); exit;
}

$shelter = new muni();
$organization_id       = $shelter->clean($_POST['organization_id']       ?? '');
if ($organization_id === 'new') $organization_id = '';
$organization_name     = $shelter->clean($_POST['organization_name']     ?? '');
$organization_location = $shelter->clean($_POST['organization_location'] ?? '');
$organization_email    = $shelter->clean($_POST['organization_email']    ?? '');
$organization_password = $shelter->clean($_POST['organization_password'] ?? '');
$shelter_name          = $shelter->clean($_POST['shelter_name']          ?? '');
$location              = $shelter->clean($_POST['location']              ?? '');
$capacity              = $shelter->clean($_POST['capacity']              ?? '');

if (empty($shelter_name) || empty($location) || empty($capacity)) {
    echo json_encode(['status' => 'error', 'message' => 'Shelter fields are required']); exit;
}
if (empty($organization_id)) {
    if (empty($organization_name) || empty($organization_location) || empty($organization_email) || empty($organization_password)) {
        echo json_encode(['status' => 'error', 'message' => 'Municipality fields are required']); exit;
    }
}

// GEOCODE SHELTER LOCATION
$shelterCoords = getCoordinates($location);

// GEOCODE ORGANIZATION LOCATION (only if adding new municipality)
$orgCoords = ['lat' => null, 'lng' => null];
if (empty($organization_id) && !empty($organization_location)) {
    $orgCoords = getCoordinates($organization_location);
}

$result = $shelter->insertShelter(
    $organization_id,
    $organization_name,
    $organization_location,
    $organization_email,
    $organization_password,
    $shelter_name,
    $location,
    $capacity,
    $shelterCoords['lat'], $shelterCoords['lng'],  // shelter lat/lng
    $orgCoords['lat'],     $orgCoords['lng']        // org lat/lng
);

if (is_array($result) && isset($result['status']) && $result['status'] == 'error') {
    echo json_encode(['status' => 'error', 'message' => $result['message']]);
} elseif (!$result) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add shelter']);
} else {
    echo json_encode(['status' => 'success', 'message' => 'Shelter added successfully']);
}