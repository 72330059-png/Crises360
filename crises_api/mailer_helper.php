<?php
function getGmailAccessToken(): string {
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id'     => getenv('GMAIL_CLIENT_ID'),
        'client_secret' => getenv('GMAIL_CLIENT_SECRET'),
        'refresh_token' => getenv('GMAIL_REFRESH_TOKEN'),
        'grant_type'    => 'refresh_token'
    ]));

    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (!isset($response['access_token'])) {
        throw new Exception("Could not get access token: " . json_encode($response));
    }

    return $response['access_token'];
}

function sendMail(string $toEmail, string $subject, string $htmlBody): bool {
    $accessToken = getGmailAccessToken();
    $from        = getenv('MAIL_USERNAME');

    $message = "From: Crises App <{$from}>\r\n";
    $message .= "To: {$toEmail}\r\n";
    $message .= "Subject: {$subject}\r\n";
    $message .= "MIME-Version: 1.0\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
    $message .= $htmlBody;

    $encoded = rtrim(strtr(base64_encode($message), '+/', '-_'), '=');

    $ch = curl_init('https://gmail.googleapis.com/gmail/v1/users/me/messages/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['raw' => $encoded]));

    $response = json_decode(curl_exec($ch), true);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode === 200;
}
?>
