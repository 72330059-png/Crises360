<?php

require_once("../class/police.class.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request'
    ]);

    exit;
}

$title = trim($_POST['title'] ?? '');
$priority = trim($_POST['priority'] ?? '');
$description = trim($_POST['description'] ?? '');
// $unit_id = intval($_POST['unit_id'] ?? 0);
$units = $_POST['units'] ?? [];

if (
    empty($title) ||
    empty($priority) ||
    empty($description) 
) {

    echo json_encode([
        'status' => 'error',
        'message' => 'All fields are required'
    ]);

    exit;
}

$police = new police();

$result = $police->addMission(
    $title,
    strtolower($priority),
    $description,
    'active',
    $units
);
if ($result) {

    echo json_encode([
        'status' => 'success',
        'message' => 'Mission added successfully'
    ]);
} else {

    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to add mission'
    ]);
}
