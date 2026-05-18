<?php

header("Content-Type: application/json");

require_once("../class/police.class.php");

$mission_id = intval($_POST['mission_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$priority = trim($_POST['priority'] ?? '');
$description = trim($_POST['description'] ?? '');
$status = trim($_POST['status'] ?? '');
// $units = $_POST['units'] ?? [];
$units = $_POST['units'] ?? [];
if (!is_array($units)) {
    $units = [$units];
}

if (
    $mission_id <= 0 ||
    empty($title) ||
    empty($priority) ||
    empty($description) ||
    empty($status)
) {

    echo json_encode([
        'status' => 'error',
        'message' => 'All fields are required'
    ]);

    exit;
}

$police = new police();

$result = $police->updateMission(
    $mission_id,
    $title,
    $priority,
    $description,
    $status,
    $units
);

if ($result) {

    echo json_encode([
        'status' => 'success',
        'message' => 'Mission updated successfully'
    ]);
} else {

    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to update mission'
    ]);
}
