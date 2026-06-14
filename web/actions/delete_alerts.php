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

$id = $alert->clean($_POST['id'] ?? '');

if (empty($id)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Alert ID required'
    ]);
    exit;
}

$result = $alert->deleteAlert($id);

if ($result) {
    echo json_encode([
        'status' => 'success',
        'message' => 'alert deleted successfully'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to delete alert'
    ]);
}