<?php
function sendGmailOAuth($to, $subject, $htmlBody)
{
    $clientId     = getenv('GMAIL_CLIENT_ID');
    $clientSecret = getenv('GMAIL_CLIENT_SECRET');
    $refreshToken = getenv('GMAIL_REFRESH_TOKEN');
    $fromEmail    = 'mourtadadouaa@gmail.com';

    // Step 1: Get access token using refresh token
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id'     => $clientId,
        'client_secret' => $clientSecret,
        'refresh_token' => $refreshToken,
        'grant_type'    => 'refresh_token',
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $tokenResponse = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (!isset($tokenResponse['access_token'])) {
        return ['success' => false, 'error' => 'Failed to get access token: ' . json_encode($tokenResponse)];
    }

    $accessToken = $tokenResponse['access_token'];

    // Step 2: Build the raw email (RFC 2822 format)
    $rawEmail = "From: Crisis360 <{$fromEmail}>\r\n";
    $rawEmail .= "To: {$to}\r\n";
    $rawEmail .= "Subject: {$subject}\r\n";
    $rawEmail .= "MIME-Version: 1.0\r\n";
    $rawEmail .= "Content-Type: text/html; charset=UTF-8\r\n";
    $rawEmail .= "\r\n";
    $rawEmail .= $htmlBody;

    // Step 3: Base64url encode it
    $encodedEmail = rtrim(strtr(base64_encode($rawEmail), '+/', '-_'), '=');

    // Step 4: Send via Gmail API
    $ch = curl_init('https://gmail.googleapis.com/gmail/v1/users/me/messages/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['raw' => $encodedEmail]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $sendResponse = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($sendResponse['id'])) {
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => json_encode($sendResponse)];
    }
}