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

// Get need info BEFORE rejecting
$need = $mun->getNeedById($id);
$result = $mun->rejectNeed($id);

if ($result) {
    if ($need) {
        $mun->insertNeedNotification(
            $need['organization_id'],
            'Your need "' . $need['need_name'] . '" has been REJECTED ❌',
            'need'
        );
    }
    echo json_encode(['status' => 'success', 'message' => 'Cannot fulfill now']);
}else {

    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to update status'
    ]);
}