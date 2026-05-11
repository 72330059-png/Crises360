<?php
require_once('../class/users.class.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$userId = intval($_POST['id'] ?? 0);

if ($userId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'user ID missing']);
    exit;
}

$info = new users();
$result = $info->deleteuser($userId);

if ($result === true) {
    echo json_encode(['status' => 'success', 'message' => 'user deleted successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete phone']);
}
