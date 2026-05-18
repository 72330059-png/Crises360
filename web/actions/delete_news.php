<?php

session_start();
header('Content-Type: application/json');

require_once("../class/news.class.php");

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

$news = new news();

$id = $_POST['id'] ?? null;

if (empty($id)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID is required'
    ]);
    exit;
}

$result = $news->deleteNews($id);

echo json_encode([
    'status' => $result ? 'success' : 'error',
    'message' => $result ? 'News deleted successfully' : 'Delete failed'
]);