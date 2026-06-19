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

function groqGeocodeQuery(string $location, string $region = 'south'): ?array {
    $regionHints = [
        'south'  => 'South Lebanon, near Tyre/Sidon area (lat ~33.0-33.5, lng ~35.1-35.5)',
        'beirut' => 'Beirut capital area (lat ~33.85-33.92, lng ~35.45-35.55)',
        'bekaa'  => 'Bekaa Valley, east Lebanon (lat ~33.5-34.2, lng ~35.7-36.5)',
        'mount'  => 'Mount Lebanon (lat ~33.7-33.95, lng ~35.5-35.75)',
        'north'  => 'North Lebanon, near Tripoli (lat ~34.2-34.7, lng ~35.6-36.2)',
    ];
    $hint = $regionHints[$region] ?? $regionHints['south'];

$prompt = "What are the exact latitude and longitude of the Lebanese village/city \"$location\"? It is located in $hint.

Known Lebanese coordinates reference:
SOUTH LEBANON:
- Bazouriyeh: lat 33.1833, lng 35.2167
- Bint Jbeil: lat 33.1167, lng 35.4333
- Sidon/Saida: lat 33.5571, lng 35.3729
- Tyre/Sur: lat 33.2704, lng 35.1964
- Nabatieh: lat 33.3772, lng 35.4836
- Khiam/Al-Khiyam: lat 33.3333, lng 35.6000
- Marjayoun: lat 33.3667, lng 35.5833
- Hasbaya: lat 33.3997, lng 35.6856
- Tibnin: lat 33.2000, lng 35.4167
- Qana: lat 33.2000, lng 35.3000
- Abbasiyeh: lat 33.2667, lng 35.2833
- Deir Qanoun: lat 33.3000, lng 35.2667
- Adloun: lat 33.4833, lng 35.2833
- Jezzine: lat 33.5433, lng 35.5767
- Kherbet Qanafar: lat 33.3500, lng 35.5500
- Yohmor: lat 33.2500, lng 35.5000
- Houla: lat 33.2333, lng 35.5500
- Aitaroun: lat 33.0833, lng 35.4500
- Bint Jbail: lat 33.1167, lng 35.4333
- Rmeish: lat 33.0667, lng 35.3833
- Yaroun: lat 33.0667, lng 35.4667
- Ayta ash Shab: lat 33.1000, lng 35.3500
- Tayr Harfa: lat 33.1500, lng 35.3333
- Shaqra: lat 33.3167, lng 35.2833
- Mansouri: lat 33.3500, lng 35.2500
- Kafra: lat 33.1500, lng 35.4833
- Zebqine: lat 33.2167, lng 35.2500
- Majdal Zoun: lat 33.3000, lng 35.2333
- Tyre coast: lat 33.2500, lng 35.1833
- Naqoura: lat 33.1167, lng 35.1333
- Alma el Shaab: lat 33.1167, lng 35.2667
- Biyyadah: lat 33.2833, lng 35.2167
- Sarafand: lat 33.4500, lng 35.3000
- Kfar Tibnit: lat 33.2833, lng 35.4167
- Srifa: lat 33.3000, lng 35.3833
- Zawtar: lat 33.3167, lng 35.4333
- Haris: lat 33.2000, lng 35.4667
- Beit Yahoun: lat 33.1667, lng 35.4833
- Hanniyeh: lat 33.3333, lng 35.2667
- Shabriha: lat 33.3000, lng 35.3000

BEIRUT:
- Beirut: lat 33.8938, lng 35.5018
- Hamra: lat 33.8967, lng 35.4833
- Achrafieh: lat 33.8883, lng 35.5150
- Verdun: lat 33.8833, lng 35.4917
- Ras Beirut: lat 33.9000, lng 35.4833
- Mar Mikhael: lat 33.8883, lng 35.5267
- Gemmayzeh: lat 33.8917, lng 35.5183
- Bourj Hammoud: lat 33.8833, lng 35.5500
- Sin el Fil: lat 33.8833, lng 35.5500
- Hadath: lat 33.8500, lng 35.5167

MOUNT LEBANON:
- Jounieh: lat 33.9808, lng 35.6178
- Byblos/Jbeil: lat 34.1236, lng 35.6517
- Baabda: lat 33.8333, lng 35.5500
- Aley: lat 33.8100, lng 35.5983
- Beit Mery: lat 33.8667, lng 35.5833
- Broummana: lat 33.8833, lng 35.6167
- Zahle approach/Chtaura: lat 33.8167, lng 35.8500
- Dbayeh: lat 33.9167, lng 35.5833
- Antelias: lat 33.9167, lng 35.5833
- Nahr el Kalb: lat 33.9333, lng 35.6167
- Jdeideh: lat 33.9000, lng 35.5667
- Dora: lat 33.9000, lng 35.5500
- Choueifat: lat 33.8333, lng 35.4833
- Damour: lat 33.7167, lng 35.4500
- Deir el Qamar: lat 33.6833, lng 35.5833
- Beit ed-Dine: lat 33.6833, lng 35.5833
- Bchamoun: lat 33.8000, lng 35.5167
- Khalde: lat 33.7833, lng 35.4833
- Aramoun: lat 33.8000, lng 35.4833
- Bhamdoun: lat 33.8000, lng 35.6500
- Sofar: lat 33.8167, lng 35.7000
- Falougha: lat 33.7833, lng 35.7167
- Moukhtara: lat 33.6500, lng 35.5833
- Kfarmatta: lat 33.7000, lng 35.5500

NORTH LEBANON:
- Tripoli: lat 34.4367, lng 35.8497
- Zgharta: lat 34.3667, lng 35.8833
- Ehden: lat 34.3000, lng 35.9500
- Bcharre: lat 34.2500, lng 36.0167
- Batroun: lat 34.2583, lng 35.6583
- Chekka: lat 34.3167, lng 35.7167
- Koura: lat 34.2833, lng 35.8167
- Kfar Aabida: lat 34.2333, lng 35.6833
- Halba: lat 34.5500, lng 36.0833
- Sir el Danniyeh: lat 34.3333, lng 35.9833
- Beit Mellat: lat 34.3500, lng 35.9500
- Amyoun: lat 34.3000, lng 35.8167
- Qalamoun: lat 34.4167, lng 35.8333
- Anfeh: lat 34.3500, lng 35.7333
- Amioun: lat 34.3000, lng 35.8167
- Kousba: lat 34.2833, lng 35.9167
- Hasroun: lat 34.2667, lng 36.0000
- Tannourine: lat 34.2167, lng 35.9167

BEKAA:
- Zahle: lat 33.8500, lng 35.9000
- Baalbek: lat 34.0042, lng 36.2181
- Chtaura: lat 33.8167, lng 35.8500
- Anjar: lat 33.7333, lng 35.9333
- Yohmor Bekaa: lat 33.9500, lng 36.1500
- Deir el Ahmar: lat 34.0000, lng 36.1000
- Taalabaya: lat 33.8667, lng 35.9500
- Saadnayel: lat 33.8333, lng 35.9167
- Qaraaoun: lat 33.5833, lng 35.7000
- Rachaiya: lat 33.4997, lng 35.8428
- Yanta: lat 33.5500, lng 35.8833
- Kherbet Qanafar: lat 33.7333, lng 35.9000
- Brital: lat 34.1333, lng 36.2000
- Hermel: lat 34.3833, lng 36.3833
- Nabi Sheet: lat 34.0167, lng 36.1000
- Qaa: lat 34.3167, lng 36.4833
- Labweh: lat 34.1833, lng 36.3500

Reply ONLY with JSON: {\"lat\": 0.0, \"lng\": 0.0}. Use real GPS coordinates, never placeholder values.";
    $body = json_encode([
        'model' => 'llama-3.1-8b-instant',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a Lebanese geography expert. You know exact GPS coordinates of every Lebanese village. Reply ONLY with valid JSON with lat and lng. Never use placeholder or example values.'],
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
   // ... rest of curl ...
    $coords = json_decode($text, true);
    if (!empty($coords['lat']) && !empty($coords['lng'])
        && $coords['lat'] != 0.0 && $coords['lng'] != 0.0
        && $coords['lat'] != 33.2704  // reject the old example value
    ) {
        return ['lat' => (float)$coords['lat'], 'lng' => (float)$coords['lng'], 'display_name' => $location];
    }
    return null;
}

function geocodeWithFallback(array $location ,string $region = 'south'): array {
    $canonical = trim($location['canonical'] ?? '');
    $variants  = $location['variants']  ?? [];
    $original  = trim($location['original'] ?? $canonical);

    $queries = [];
    if ($canonical) $queries[] = $canonical . ', Lebanon';
    foreach (array_slice($variants, 0, 2) as $v)
        $queries[] = trim($v) . ', Lebanon';

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

    // Nominatim failed — fall back to Groq for coordinates
    $result = groqGeocodeQuery($canonical ?: $original, $region);
    if ($result) {
        return [
            'name' => $canonical ?: $original, 'original' => $original,
            'tried' => 'groq-geocode', 'lat' => $result['lat'], 'lng' => $result['lng'],
            'display_name' => $result['display_name'], 'found' => true,
        ];
    }

    return [
        'name' => $canonical ?: $original, 'original' => $original,
        'tried' => implode(' / ', $queries),
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