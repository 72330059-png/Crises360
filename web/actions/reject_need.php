<?php

header('Content-Type: application/json');

require_once('../class/municipality.class.php');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request'
    ]);

    exit;
}

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid ID'
    ]);

    exit;
}

$mun = new muni();

$result = $mun->rejectNeed($id);

if ($result) {

    echo json_encode([
        'status' => 'success',
        'message' => ' Cannot fullfill now'
    ]);

} else {

    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to update status'
    ]);
}