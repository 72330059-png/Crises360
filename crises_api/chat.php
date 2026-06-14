<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$data     = json_decode(file_get_contents("php://input"), true);
$messages = $data["messages"] ?? [];
$system   = $data["system"]   ?? "";

// Build full conversation
$fullMessages = [];

if (!empty($system)) {
    $fullMessages[] = [
        "role"    => "system",
        "content" => $system
    ];
}

if (empty($messages)) {
    $fullMessages[] = [
        "role"    => "user",
        "content" => "Hello"
    ];
} else {
    foreach ($messages as $msg) {
        $fullMessages[] = [
            "role"    => $msg["role"] === "assistant" ? "assistant" : "user",
            "content" => $msg["content"]
        ];
    }
}

$payload = [
    "model"      => "llama-3.1-8b-instant",
    "messages"   => $fullMessages,
    "max_tokens" => 1024
];

$ch = curl_init("https://api.groq.com/openai/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST,           true);
curl_setopt($ch, CURLOPT_POSTFIELDS,     json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER,     [
    "Content-Type: application/json",
    "Authorization: Bearer " . getenv("GROQ_API_KEY")
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 120);

$response  = curl_exec($ch);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode(["error" => $curlError]);
    exit;
}

// Parse Groq response
// Groq returns: {"choices":[{"message":{"content":"Hi!"}}]}
$groqData = json_decode($response, true);
$text = $groqData["choices"][0]["message"]["content"] ?? "Sorry, I could not respond.";

// Return in format your Android app expects
echo json_encode([
    "content" => [
        ["type" => "text", "text" => $text]
    ]
]);
?>