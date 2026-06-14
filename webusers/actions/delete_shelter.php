<?php
session_start();
header('Content-Type: application/json');
require_once("../class/municipality.class.php");

if (!isset($_SESSION['logged_in'])) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]);

    exit;
}

$municipality = new Municipality();

$id = $_POST['id'] ?? 0;

if (!$municipality->validateInt($id)) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid shelter id'
    ]);

    exit;
}


$result = $municipality->deleteShelter((int)$id);


if (is_array($result) && isset($result['status'])) {

    echo json_encode($result);

    exit;
}


echo json_encode([
    'status' => 'success',
    'message' => 'Shelter deleted successfully'
]);
