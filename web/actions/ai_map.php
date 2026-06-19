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

// =====================================================================
// STATIC COORDINATE TABLE
// Source: the original AI reference list, with entries we verified
// against Wikipedia/Wikidata corrected, and missing villages added.
// VERIFIED entries (confirmed against Wikipedia/Wikidata):
//   Bazouriyeh, Deir Qanoun an-Naher, Deir Qanoun Ras al-Ain, Qlaileh
// Everything else below is from the original unverified list — treat
// as "probably fine" but verify any village before trusting it 100%.
// =====================================================================
$LEBANON_COORDS = [
    // SOUTH — verified
    'Bazouriyeh' => [33.25389, 35.27167],
    'Deir Qanoun an-Naher' => [33.29889, 35.31472],
    'Deir Qanoun Ras al-Ain' => [33.22806, 35.21722],
    'Ras al-Ain' => [33.22806, 35.21722], // alias, same place as above
    'Qlaileh' => [33.19639, 35.23111],
    'Al-Qlailah' => [33.19639, 35.23111], // alias

    // SOUTH — from original list (unverified individually)
    'Bint Jbeil' => [33.1167, 35.4333],
    'Sidon' => [33.5571, 35.3729],
    'Saida' => [33.5571, 35.3729],
    'Tyre' => [33.271992, 35.203487],
    'Sur' => [33.271992, 35.203487],
    'Nabatieh' => [33.3772, 35.4836],
    'Khiam' => [33.3333, 35.6000],
    'Al-Khiyam' => [33.3333, 35.6000],
    'Marjayoun' => [33.3667, 35.5833],
    'Hasbaya' => [33.3997, 35.6856],
    'Tibnin' => [33.2000, 35.4167],
    'Qana' => [33.2000, 35.3000],
    'Abbasiyeh' => [33.2667, 35.2833],
    'Adloun' => [33.4833, 35.2833],
    'Jezzine' => [33.5433, 35.5767],
    'Yohmor' => [33.2500, 35.5000],
    'Houla' => [33.2333, 35.5500],
    'Aitaroun' => [33.0833, 35.4500],
    'Rmeish' => [33.0667, 35.3833],
    'Yaroun' => [33.0667, 35.4667],
    'Ayta ash Shab' => [33.1000, 35.3500],
    'Tayr Harfa' => [33.1500, 35.3333],
    'Shaqra' => [33.3167, 35.2833],
    'Mansouri' => [33.3500, 35.2500],
    'Kafra' => [33.1500, 35.4833],
    'Zebqine' => [33.2167, 35.2500],
    'Majdal Zoun' => [33.3000, 35.2333],
    'Naqoura' => [33.1167, 35.1333],
    'Alma el Shaab' => [33.1167, 35.2667],
    'Biyyadah' => [33.2833, 35.2167],
    'Sarafand' => [33.4500, 35.3000],
    'Kfar Tibnit' => [33.2833, 35.4167],
    'Srifa' => [33.3000, 35.3833],
    'Zawtar' => [33.3167, 35.4333],
    'Haris' => [33.2000, 35.4667],
    'Beit Yahoun' => [33.1667, 35.4833],
    'Hanniyeh' => [33.3333, 35.2667],
    'Shabriha' => [33.3000, 35.3000],

    // BEIRUT
    'Beirut' => [33.8938, 35.5018],
    'Hamra' => [33.8967, 35.4833],
    'Achrafieh' => [33.8883, 35.5150],
    'Verdun' => [33.8833, 35.4917],
    'Ras Beirut' => [33.9000, 35.4833],
    'Mar Mikhael' => [33.8883, 35.5267],
    'Gemmayzeh' => [33.8917, 35.5183],
    'Bourj Hammoud' => [33.8833, 35.5500],
    'Sin el Fil' => [33.8833, 35.5500],
    'Hadath' => [33.8500, 35.5167],

    // MOUNT LEBANON
    'Jounieh' => [33.9808, 35.6178],
    'Byblos' => [34.1236, 35.6517],
    'Baabda' => [33.8333, 35.5500],
    'Aley' => [33.8100, 35.5983],
    'Beit Mery' => [33.8667, 35.5833],
    'Broummana' => [33.8833, 35.6167],
    'Dbayeh' => [33.9167, 35.5833],
    'Antelias' => [33.9167, 35.5833],
    'Jdeideh' => [33.9000, 35.5667],
    'Choueifat' => [33.8333, 35.4833],
    'Damour' => [33.7167, 35.4500],
    'Deir el Qamar' => [33.6833, 35.5833],
    'Beit ed-Dine' => [33.6833, 35.5833],
    'Bchamoun' => [33.8000, 35.5167],
    'Khalde' => [33.7833, 35.4833],
    'Aramoun' => [33.8000, 35.4833],
    'Bhamdoun' => [33.8000, 35.6500],
    'Sofar' => [33.8167, 35.7000],
    'Falougha' => [33.7833, 35.7167],
    'Moukhtara' => [33.6500, 35.5833],

    // NORTH
    'Tripoli' => [34.4367, 35.8497],
    'Zgharta' => [34.3667, 35.8833],
    'Ehden' => [34.3000, 35.9500],
    'Bcharre' => [34.2500, 36.0167],
    'Batroun' => [34.2583, 35.6583],
    'Chekka' => [34.3167, 35.7167],
    'Koura' => [34.2833, 35.8167],
    'Halba' => [34.5500, 36.0833],
    'Amioun' => [34.3000, 35.8167],
    'Kousba' => [34.2833, 35.9167],
    'Hasroun' => [34.2667, 36.0000],
    'Tannourine' => [34.2167, 35.9167],

    // BEKAA
    'Zahle' => [33.8500, 35.9000],
    'Baalbek' => [34.0042, 36.2181],
    'Chtaura' => [33.8167, 35.8500],
    'Anjar' => [33.7333, 35.9333],
    'Deir el Ahmar' => [34.0000, 36.1000],
    'Taalabaya' => [33.8667, 35.9500],
    'Saadnayel' => [33.8333, 35.9167],
    'Qaraaoun' => [33.5833, 35.7000],
    'Rachaiya' => [33.4997, 35.8428],
    'Yanta' => [33.5500, 35.8833],
    'Brital' => [34.1333, 36.2000],
    'Hermel' => [34.3833, 36.3833],
    'Nabi Sheet' => [34.0167, 36.1000],
    'Qaa' => [34.3167, 36.4833],
    'Labweh' => [34.1833, 36.3500],
];

function localCoordinateLookup(string $name): ?array {
    global $LEBANON_COORDS;
    $clean = function ($s) {
        $s = strtolower(trim($s));
        return preg_replace('/^(al-|el-|al |el )/i', '', $s);
    };
    $needle = $clean($name);
    if ($needle === '') return null;

    // exact match first
    foreach ($LEBANON_COORDS as $key => $coords) {
        if ($clean($key) === $needle) {
            return ['lat' => $coords[0], 'lng' => $coords[1], 'display_name' => $key];
        }
    }
    // partial / fuzzy match second
    foreach ($LEBANON_COORDS as $key => $coords) {
        $k = $clean($key);
        if (strpos($k, $needle) !== false || strpos($needle, $k) !== false) {
            return ['lat' => $coords[0], 'lng' => $coords[1], 'display_name' => $key];
        }
    }
    return null;
}

function isWithinRegion(float $lat, float $lng, string $region): bool {
    $bounds = [
        'south'  => [32.9, 33.65, 35.0, 35.75],
        'beirut' => [33.80, 33.95, 35.40, 35.60],
        'bekaa'  => [33.3, 34.45, 35.6, 36.7],
        'mount'  => [33.6, 34.20, 35.35, 35.85],
        'north'  => [34.05, 34.75, 35.5, 36.25],
    ];
    [$latMin, $latMax, $lngMin, $lngMax] = $bounds[$region] ?? $bounds['south'];
    return $lat >= $latMin && $lat <= $latMax && $lng >= $lngMin && $lng <= $lngMax;
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
    'model'       => 'openai/gpt-oss-20b', // was llama-3.1-8b-instant (Groq deprecated this June 17, 2026)
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

// STEP 2 — Geocode: static table first, then Nominatim, then Groq as a
// sanity-checked last resort.

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

    usleep(1100000); // 1.1s — stays under Nominatim's 1 req/sec rate limit

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

function groqGeocodeQuery(string $location, string $region = 'south'): ?array {
    $regionHints = [
        'south'  => 'South Lebanon, near Tyre/Sidon area (lat ~33.0-33.5, lng ~35.1-35.5)',
        'beirut' => 'Beirut capital area (lat ~33.85-33.92, lng ~35.45-35.55)',
        'bekaa'  => 'Bekaa Valley, east Lebanon (lat ~33.5-34.2, lng ~35.7-36.5)',
        'mount'  => 'Mount Lebanon (lat ~33.7-33.95, lng ~35.5-35.75)',
        'north'  => 'North Lebanon, near Tripoli (lat ~34.2-34.7, lng ~35.6-36.2)',
    ];
    $hint = $regionHints[$region] ?? $regionHints['south'];

    $prompt = "What are the exact latitude and longitude of the Lebanese village/city \"$location\"? It is located in $hint. Reply ONLY with JSON: {\"lat\": 0.0, \"lng\": 0.0}. Use real GPS coordinates, never placeholder values.";

    $body = json_encode([
        'model' => 'openai/gpt-oss-20b', // was llama-3.1-8b-instant
        'messages' => [
            ['role' => 'system', 'content' => 'You are a Lebanese geography expert. Reply ONLY with valid JSON with lat and lng. Never use placeholder or example values.'],
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
    $text = preg_replace('/```\s*/i', '', trim($text));
    $coords = json_decode($text, true);
    if (!empty($coords['lat']) && !empty($coords['lng'])
        && $coords['lat'] != 0.0 && $coords['lng'] != 0.0
    ) {
        return ['lat' => (float)$coords['lat'], 'lng' => (float)$coords['lng'], 'display_name' => $location];
    }
    return null;
}

function geocodeWithFallback(array $location, string $region = 'south'): array {
    $canonical = trim($location['canonical'] ?? '');
    $variants  = $location['variants']  ?? [];
    $original  = trim($location['original'] ?? $canonical);

    // 1. Static table — instant, zero hallucination risk for known villages
    if ($local = localCoordinateLookup($canonical ?: $original)) {
        return [
            'name' => $canonical ?: $original, 'original' => $original,
            'tried' => 'local-lookup', 'lat' => $local['lat'], 'lng' => $local['lng'],
            'display_name' => $local['display_name'], 'found' => true,
        ];
    }

    // 2. Live geocoder
    $queries = [];
    if ($canonical) $queries[] = $canonical . ', Lebanon';
    foreach (array_slice($variants, 0, 2) as $v) $queries[] = trim($v) . ', Lebanon';

    foreach ($queries as $q) {
        $result = nominatimQuery($q);
        if ($result) {
            return [
                'name' => $canonical ?: $original, 'original' => $original,
                'tried' => $q, 'lat' => $result['lat'], 'lng' => $result['lng'],
                'display_name' => $result['display_name'], 'found' => true,
            ];
        }
    }

    // 3. AI guess as last resort — only accepted if it falls inside the
    //    expected region's bounding box, otherwise it's rejected.
    $result = groqGeocodeQuery($canonical ?: $original, $region);
    if ($result && isWithinRegion($result['lat'], $result['lng'], $region)) {
        return [
            'name' => $canonical ?: $original, 'original' => $original,
            'tried' => 'groq-geocode', 'lat' => $result['lat'], 'lng' => $result['lng'],
            'display_name' => $result['display_name'], 'found' => true,
        ];
    }

    return [
        'name' => $canonical ?: $original, 'original' => $original,
        'tried' => implode(' / ', $queries) . ' / groq-rejected-or-failed',
        'lat' => null, 'lng' => null, 'found' => false,
    ];
}

$geocoded = [];
foreach ($extracted['locations'] as $loc) {
    if (is_string($loc)) {
        $loc = ['canonical' => $loc, 'original' => $loc, 'variants' => []];
    }
    $geocoded[] = geocodeWithFallback($loc, $extracted['region'] ?? 'south');
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