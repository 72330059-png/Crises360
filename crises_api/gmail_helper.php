<?php

function getGmailAccessToken() {
    $ch = curl_init('https://oauth2.googleapis.com/token');

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id'     => getenv('GMAIL_CLIENT_ID'),
        'client_secret' => getenv('GMAIL_CLIENT_SECRET'),
        'refresh_token' => getenv('GMAIL_REFRESH_TOKEN'),
        'grant_type'    => 'refresh_token'
    ]));

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    return $response['access_token'] ?? null;
}

function sendEmail($to, $subject, $body) {
    $accessToken = getGmailAccessToken();

    if (!$accessToken) return false;

    $raw  = "To: $to\r\n";
    $raw .= "Subject: $subject\r\n";
    $raw .= "MIME-Version: 1.0\r\n";
    $raw .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
    $raw .= $body;

    $encoded = rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');

    $ch = curl_init('https://gmail.googleapis.com/gmail/v1/users/me/messages/send');

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'raw' => $encoded
    ]));

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    curl_close($ch);

    return $result !== false;
}