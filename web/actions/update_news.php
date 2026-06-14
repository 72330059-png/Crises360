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

$title = $news->clean($_POST['title'] ?? '');
$content = $news->clean($_POST['content'] ?? '');
$category = $news->clean($_POST['category'] ?? '');
$type = $news->clean($_POST['type'] ?? '');
$status = $news->clean($_POST['status'] ?? '');
$featured = ($_POST['featured'] ?? 0) == 1 ? 1 : 0;
// $featured = isset($_POST['featured']) ? 1 : 0;
$publish_date = $news->clean($_POST['publish_date'] ?? '');

if (
    !$id ||
    empty($title) ||
    empty($content) ||
    empty($category) ||
    empty($status)
) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required fields'
    ]);

    exit;
}

$allowedTypes = ['News', 'Article'];

if (!in_array($type, $allowedTypes)) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid type'
    ]);

    exit;
}

$allowedCategories = [
    'Weather',
    'Traffic',
    'Safety',
    'Medical',
    'Infrastructure',
    'General',
    'Tech',
    'Sports',
    'Politics',
    'Economy'
];

if (!in_array($category, $allowedCategories)) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid category'
    ]);

    exit;
}

$allowedStatus = ['Published', 'Draft'];

if (!in_array($status, $allowedStatus)) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid status'
    ]);

    exit;
}

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

if (!empty($_FILES['image']['name'])) {

    if (!$news->validateImage($_FILES['image'])) {

        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid image type'
        ]);

        exit;
    }

    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExt)) {

        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid image extension'
        ]);

        exit;
    }

    $target_dir = "../uploads/";

    $image = uniqid() . "." . $ext;

    move_uploaded_file(
        $_FILES['image']['tmp_name'],
        $target_dir . $image
    );

    $news->updateNewsImage($id, $image);
}

echo json_encode([
    'status' => $result ? 'success' : 'error',
    'message' => $result
        ? 'News updated successfully'
        : 'Update failed'
]);
