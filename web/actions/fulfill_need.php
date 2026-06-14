<?php
session_start();
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
$result = $mun->fulfillNeed($id);

if ($result) {
    // Get need info to notify the municipality
    $need = $mun->getNeedById($id);
    if ($need) {
        $mun->insertNeedNotification(
            $need['organization_id'],
            'Your need "' . $need['need_name'] . '" has been FULFILLED ✅',
            'need'
        );
    }
    echo json_encode(['status' => 'success', 'message' => 'Need fulfilled successfully']);
} else {

    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to update status'
    ]);
}
