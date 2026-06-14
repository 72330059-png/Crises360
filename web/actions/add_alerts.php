<?php
session_start();
header('Content-Type: application/json');
require_once("../class/alerts.class.php");

if (!isset($_SESSION['logged_in'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request'
    ]);
    exit;
}

$alert = new alert();

$alert_message = $alert->clean($_POST['alert_message'] ?? '');
$severity      = $alert->clean($_POST['severity'] ?? '');
$region        = $alert->clean($_POST['region'] ?? '');
$status        = $alert->clean($_POST['status'] ?? 'Pending');

if (
    empty($alert_message) ||
    empty($severity) ||
    empty($region) ||
    empty($status)
) {
    echo json_encode([
        'status' => 'error',
        'message' => 'All fields are required'
    ]);
    exit;
}

$allowedSeverity = ['Info', 'Critical', 'Warning'];
$allowedStatus = ['Pending', 'Sent'];

if (!in_array($severity, $allowedSeverity)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid severity'
    ]);
    exit;
}

if (!in_array($status, $allowedStatus)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid status'
    ]);
    exit;
}

$result = $alert->insertAlert(
    $alert_message,
    $severity,
    $region,
    $status
);

echo json_encode([
    'status' => $result ? 'success' : 'error',
    'message' => $result ? 'Alert added successfully' : 'Insert failed'
]);