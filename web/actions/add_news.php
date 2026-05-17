<?php
require_once("../class/news.class.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    header('Content-Type: application/json');

    $news = new news();

    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'];
    $type = $_POST['type'];
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

    $status = $_POST['status'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $publish_date = $_POST['publish_date'];

    // Image upload
    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        $image = time() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image);
    }

    $result = $news->insertNews(
        $title,
        $content,
        $category,
         $type,
        $status,
        $featured,
        $image,
        $publish_date
    );

    echo json_encode([
        'status' => $result ? 'success' : 'error',
        'message' => $result ? 'News added successfully' : 'Insert failed'
    ]);
}
