<?php

session_start();
header('Content-Type: application/json');

require_once("../class/news.class.php");

if (!isset($_SESSION['logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$news = new news();

$id           = $_POST['id'] ?? null;
$title        = $news->clean($_POST['title'] ?? '');
$content      = $news->clean($_POST['content'] ?? '');
$category     = $news->clean($_POST['category'] ?? '');
$type         = $news->clean($_POST['type'] ?? '');
$status       = $news->clean($_POST['status'] ?? '');
$featured     = $_POST['featured'] ?? 0;
$publish_date = $news->clean($_POST['publish_date'] ?? '');

if (!$id || !$title || !$content || !$category || !$status) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required fields'
    ]);
    exit;
}

$allowedStatus = ['Published', 'Draft'];

if (!in_array($status, $allowedStatus)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
    exit;
}

// UPDATE
$result = $news->updateNews(
    $id,
    $title,
    $content,
    $category,
    $type,
    $status,
    $featured,
    $publish_date
);

// IMAGE (optional)
if (!empty($_FILES['image']['name'])) {
    $target_dir = "../uploads/";
    $image = time() . "_" . $_FILES['image']['name'];

    move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image);

    $news->updateNewsImage($id, $image);
}

echo json_encode([
    'status' => $result ? 'success' : 'error',
    'message' => $result ? 'News updated successfully' : 'Update failed'
]);
