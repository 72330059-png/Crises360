<?php
session_start();
header("Content-Type: application/json");
require_once("../class/DAL.class.php");

function nominatimGeocode($location) {
    $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
        'format' => 'json', 'q' => $location . ', Lebanon',
        'limit' => 1, 'countrycodes' => 'lb',
    ]);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: Crisis360App/1.0']);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    if (!empty($data)) return ['lat' => (float)$data[0]['lat'], 'lng' => (float)$data[0]['lon'], 'zoom' => 13];
    return null;
}

function groqGeocode($location) {
    $prompt = "Give me the latitude and longitude of \"$location\" in Lebanon. Reply ONLY with valid JSON like: {\"lat\": 33.8938, \"lng\": 35.5018, \"zoom\": 13}. No explanation, no markdown.";
    $body = json_encode([
        'model' => 'llama-3.1-8b-instant',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a Lebanese geography expert. Always reply ONLY with valid JSON containing lat, lng and zoom.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.1, 'max_tokens' => 50
    ]);
    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
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
        return ['lat' => (float)$coords['lat'], 'lng' => (float)$coords['lng'], 'zoom' => 13];
    return null;
}

$location = trim($_POST['location'] ?? '');
if (!$location) {
    echo json_encode(['lat' => 33.8547, 'lng' => 35.8623, 'zoom' => 9, 'fallback' => true]);
    exit;
}

$coords = nominatimGeocode($location);
if (!$coords) $coords = groqGeocode($location);
if (!$coords) $coords = ['lat' => 33.8547, 'lng' => 35.8623, 'zoom' => 9, 'fallback' => true];

echo json_encode($coords);