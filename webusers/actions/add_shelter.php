<?php
session_start();
header('Content-Type: application/json');
require_once("../class/municipality.class.php");

if (!isset($_SESSION['logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

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
    return null; // explicitly null so we know to fall back
}

$municipality = new Municipality();

$org_id      = $_SESSION['org_id'] ?? 0;
$shelter_name = $municipality->clean($_POST['shelter_name'] ?? '');
$location     = $municipality->clean($_POST['location'] ?? '');
$capacity     = $_POST['capacity'] ?? 0;

if (!$municipality->validateInt($org_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid organization']);
    exit;
}
if (empty($shelter_name)) {
    echo json_encode(['status' => 'error', 'message' => 'Shelter name required']);
    exit;
}
if (empty($location)) {
    echo json_encode(['status' => 'error', 'message' => 'Location required']);
    exit;
}
if (!$municipality->validateInt($capacity)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid capacity']);
    exit;
}

// Step 1: try to geocode the shelter's location text
$coords = getCoordinates($location);

// Step 2: if geocoding failed, fall back to the organization's own lat/lng
if (!$coords) {
    $org = $municipality->getOrganizationById((int)$org_id);
    $coords = [
        'lat' => $org['lat'] ?? null,
        'lng' => $org['lng'] ?? null
    ];
}

$data = [
    'organization_id' => (int)$org_id,
    'shelter_name'    => trim($shelter_name),
    'location'        => trim($location),
    'capacity'        => (int)$capacity,
    'occupied'        => 0,
    'lat'             => $coords['lat'],
    'lng'             => $coords['lng']
];

$result = $municipality->addShelter($data);

if (is_array($result) && isset($result['status'])) {
    echo json_encode($result);
    exit;
}

echo json_encode(['status' => 'success', 'message' => 'Shelter added successfully']);