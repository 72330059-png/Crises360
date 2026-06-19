<?php
session_start();
require_once("../class/DAL.class.php");
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
// define('GROQ_API_KEY', 'GROQ_API_KEY_Secret');
// define('GROQ_API_KEY', getenv('GROQ_API_KEY'));
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$userText = trim($_POST['text'] ?? '');
$incidentId = (int)($_POST['incident_id'] ?? 0);

if (!$userText) {
    echo json_encode(['status' => 'error', 'message' => 'No text provided']);
    exit;
}

// STEP 1 — Send text to Groq with smarter prompt

$prompt = <<<PROMPT
You are a crisis map assistant for Lebanon with deep knowledge of Lebanese geography.
The admin may write location names in Arabic, informal transliteration, or with spelling errors.
Your job is to identify the real Lebanese village/city/area and provide multiple spelling variants so geocoding can succeed.

Reply ONLY with valid JSON — no explanation, no markdown, no code blocks.

JSON format:
{
  "action": "alert" or "zone" or "road",
  "severity": "high" or "medium" or "low",
  "zone_type": "danger" or "warning" or "safe",
  "road_status": "closed" or "warning" or "open",
  "locations": [
    {
      "original": "the name as written by admin",
      "canonical": "the standard English spelling used on maps",
      "variants": ["variant1", "variant2", "variant3", "variant4"]
    }
  ],
  "description": "short English description of the situation",
  "region": "beirut" or "south" or "bekaa" or "mount" or "north"
}

Rules for locations:
- canonical = the most common official English spelling (e.g. "Bazouriyeh", "Tyre", "Bint Jbeil")
- variants = all reasonable alternative spellings that geocoders might recognize, including:
  * Arabic transliteration variations (Bazouriyeh / Bazouriye / Bazouriya / Bazzouriyeh / Bazzouriye)
  * Common informal spellings
  * French transliteration (Lebanon uses both English and French spellings)
  * The name WITHOUT the article (e.g. both "Khiyam" and "Al-Khiyam" and "El Khiam")
  * Nearby landmark or district if the name is ambiguous
- Always add ", Lebanon" context in your mind when picking variants
- If input is Arabic (e.g. بزوريه), identify the village and give canonical + all variants
- If unsure about action type, use "alert"
- If unsure about severity, use "high"
- If unsure about region, use "south"
- Always include at least one location

Common Lebanese village spelling reference (use these canonical forms):
- بزوريه / Bezouriye / Bezourieh = Bazouriyeh (south)
- عباسية / Abbassiyeh / Abassiye = Abbasiyeh (south)
- بنت جبيل = Bint Jbeil (south)
- الخيام = Khiam (south)
- صيدا = Sidon (south)
- صور = Tyre (south)
- النبطية = Nabatieh (south)
- مرجعيون = Marjayoun (south)
- شمع = Shama (south)
- بيروت = Beirut
- طرابلس = Tripoli (north)
- زحلة = Zahle (bekaa)
- بعلبك = Baalbek (bekaa)

Admin text: "$userText"
PROMPT;

$groqUrl = 'https://api.groq.com/openai/v1/chat/completions';

$groqBody = json_encode([
    'model'       => 'llama-3.1-8b-instant',
    'messages'    => [
        [
            'role'    => 'system',
            'content' => 'You are a Lebanese geography expert and crisis map assistant. Always reply ONLY with valid JSON, no explanation, no markdown, no code blocks. You know all Lebanese village spellings in Arabic and English.'
        ],
        [
            'role'    => 'user',
            'content' => $prompt
        ]
    ],
    'temperature' => 0.1,
    'max_tokens'  => 800
]);

$ch = curl_init($groqUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $groqBody);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . GROQ_API_KEY
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$groqResponse = curl_exec($ch);
$curlError    = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode(['status' => 'error', 'message' => 'Could not reach Groq: ' . $curlError]);
    exit;
}

$groqData = json_decode($groqResponse, true);

if (isset($groqData['error'])) {
    echo json_encode(['status' => 'error', 'message' => 'Groq error: ' . $groqData['error']['message']]);
    exit;
}

$aiText = $groqData['choices'][0]['message']['content'] ?? '';

if (!$aiText) {
    echo json_encode(['status' => 'error', 'message' => 'Groq returned empty response']);
    exit;
}
$aiText = preg_replace('/```json\s*/i', '', $aiText);
$aiText = preg_replace('/```\s*/i', '', $aiText);
$aiText = trim($aiText);

$extracted = json_decode($aiText, true);

if (!$extracted || !isset($extracted['locations'])) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'AI could not understand. Try being more specific.',
        'raw'     => $aiText
    ]);
    exit;
}

// STEP 2 — Geocode with multi-variant fallback


function nominatimQuery(string $q): ?array {
    $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
        'format'         => 'json',
        'q'              => $q,
        'limit'          => 1,
        'addressdetails' => 1,
        'countrycodes'   => 'lb',   // restrict to Lebanon
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: LebanonCrisisMap/2.0 (senior-project)']);
    $response = curl_exec($ch);
    curl_close($ch);

    usleep(1100000); // 0.3s — Nominatim rate limit

    $data = json_decode($response, true);
    if ($data && count($data) > 0) {
        return [
            'lat'          => (float)$data[0]['lat'],
            'lng'          => (float)$data[0]['lon'],
            'display_name' => $data[0]['display_name'],
        ];
    }
    return null;
}


function geocodeWithFallback(array $location): array {
    $canonical = trim($location['canonical'] ?? '');
    $variants  = $location['variants']  ?? [];
    $original  = trim($location['original'] ?? $canonical);

    // Build the list of queries to try, in order
    $queries = [];

    // 1. Canonical with countrycodes=lb (handled inside nominatimQuery)
    if ($canonical) $queries[] = $canonical;

    // 2. Each variant
    foreach ($variants as $v) {
        $v = trim($v);
        if ($v && !in_array($v, $queries)) $queries[] = $v;
    }

    // 3. Original as typed (might work for Arabic directly)
    if ($original && !in_array($original, $queries)) $queries[] = $original;

    // 4. Append ", Lebanon" to each as extra fallback
    $withLebanon = [];
    foreach ($queries as $q) {
        $withLebanon[] = $q . ', Lebanon';
    }
    // Merge: try without ", Lebanon" first, then with
    $allQueries = array_merge($queries, $withLebanon);
    $allQueries = array_unique($allQueries);

    foreach ($allQueries as $q) {
        $result = nominatimQuery($q);
        if ($result) {
            return [
                'name'         => $canonical ?: $original,
                'original'     => $original,
                'tried'        => $q,           // which spelling worked
                'lat'          => $result['lat'],
                'lng'          => $result['lng'],
                'display_name' => $result['display_name'],
                'found'        => true,
            ];
        }
    }

    // Nothing worked
    return [
        'name'     => $canonical ?: $original,
        'original' => $original,
        'tried'    => implode(' / ', array_slice($allQueries, 0, 5)), // for debugging
        'lat'      => null,
        'lng'      => null,
        'found'    => false,
    ];
}

$geocoded = [];
foreach ($extracted['locations'] as $loc) {
    if (is_string($loc)) {
        $loc = ['canonical' => $loc, 'original' => $loc, 'variants' => []];
    }
    $geocoded[] = geocodeWithFallback($loc);
}


// STEP 3 — Return everything to JavaScript

echo json_encode([
    'status'      => 'success',
    'action'      => $extracted['action']      ?? 'alert',
    'severity'    => $extracted['severity']    ?? 'high',
    'zone_type'   => $extracted['zone_type']   ?? 'danger',
    'road_status' => $extracted['road_status'] ?? 'closed',
    'description' => $extracted['description'] ?? $userText,
    'region'      => $extracted['region']      ?? 'south',
    'locations'   => $geocoded,
    'incident_id' => $incidentId,
]);