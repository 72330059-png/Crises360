<?php
session_start();
header("Content-Type: application/json");
require_once("../class/hospitals.class.php");

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
        return [
            'lat' => (float)$data[0]['lat'],
            'lng' => (float)$data[0]['lon']
        ];
    }
    return null;
}

function groqGeocode($location) {
    $prompt = "Give me the latitude and longitude of \"$location\" in Lebanon. Reply ONLY with valid JSON like this: {\"lat\": 33.8938, \"lng\": 35.5018}. No explanation, no markdown, nothing else.";

    $body = json_encode([
        'model'    => 'llama-3.1-8b-instant',
        'messages' => [
            [
                'role'    => 'system',
                'content' => 'You are a Lebanese geography expert. You know every village, city, and area in Lebanon. Always reply ONLY with valid JSON containing lat and lng. No explanation, no markdown.'
            ],
            [
                'role'    => 'user',
                'content' => $prompt
            ]
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
    $text = trim($text);

    $coords = json_decode($text, true);
    if (!empty($coords['lat']) && !empty($coords['lng'])) {
        return [
            'lat' => (float)$coords['lat'],
            'lng' => (float)$coords['lng']
        ];
    }
    return null;
}

function getCoordinates($location) {
    // Try Nominatim first
    $coords = nominatimGeocode($location);
    if ($coords) return $coords;

    // Fallback to Groq
    $coords = groqGeocode($location);
    if ($coords) return $coords;

    // Nothing worked
    return ['lat' => null, 'lng' => null];
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name            = $_POST['name']            ?? '';
    $location        = $_POST['location']        ?? '';
    $email           = $_POST['email']           ?? '';
    $password        = $_POST['password']        ?? '';
    $total_beds      = $_POST['total_beds']      ?? 0;
    $hospital_status = $_POST['hospital_status'] ?? 'Safe';

    if (empty($name) || empty($location) || empty($email) || empty($password) || empty($total_beds)) {
        echo json_encode(["success" => false, "message" => "Missing required fields"]);
        exit;
    }

    // GET LAT/LNG
    $coords = getCoordinates($location);
    $lat = $coords['lat'];
    $lng = $coords['lng'];

    $hospital = new hospital();
    $result = $hospital->insertHospital(
        $name, $location, $email, $password,
        $total_beds, $hospital_status,
        $lat, $lng
    );

    if ($result === true || is_numeric($result)) {
        echo json_encode(["success" => true, "message" => "Hospital added successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => $result]);
    }

} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}