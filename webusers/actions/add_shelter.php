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

$org_id = $_SESSION['org_id'] ?? 0;

$shelter_name = $municipality->clean($_POST['shelter_name'] ?? '');

$location = $municipality->clean($_POST['location'] ?? '');

$capacity = $_POST['capacity'] ?? 0;


if (!$municipality->validateInt($org_id)) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid organization'
    ]);

    exit;
}

if (empty($shelter_name)) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Shelter name required'
    ]);

    exit;
}

if (empty($location)) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Location required'
    ]);

    exit;
}

if (!$municipality->validateInt($capacity)) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid capacity'
    ]);

    exit;
}


$data = [

    'organization_id' => (int)$org_id,

    'shelter_name' => trim($shelter_name),

    'location' => trim($location),

    'capacity' => (int)$capacity,

    'occupied' => 0

];


$result = $municipality->addShelter($data);


if (is_array($result) && isset($result['status'])) {

    echo json_encode($result);

    exit;
}


echo json_encode([
    'status' => 'success',
    'message' => 'Shelter added successfully'
]);
