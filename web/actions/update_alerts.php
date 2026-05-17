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
$id       = $alert->clean($_POST['id'] ?? '');
$message  = $alert->clean($_POST['alert_message'] ?? '');
$severity = $alert->clean($_POST['severity'] ?? '');
$region   = $alert->clean($_POST['region'] ?? '');
$status   = $alert->clean($_POST['status'] ?? 'Pending');
if (
    empty($id) ||
    empty($message) ||
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
$allowedSeverity = ['Info', 'Warning', 'Critical'];
$allowedStatus   = ['Pending', 'Sent'];
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
$result = $alert->updateAlert(
    $id,
    $message,
    $severity,
    $region,
    $status
);
if ($result) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Alert updated successfully'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Update failed'
    ]);
}
