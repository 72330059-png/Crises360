<?php
require_once('../class/index.class.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$userId = intval($_POST['id'] ?? 0);

if ($userId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Phone ID missing']);
    exit;
}

$info = new Index();
$result = $info->deleteuser($userId);

if ($result === true) {
    echo json_encode(['status' => 'success', 'message' => 'Phone deleted successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete phone']);
}
