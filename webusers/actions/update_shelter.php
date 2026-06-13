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

$shelter_name = $municipality->clean($_POST['shelter_name'] ?? '');

$location = $municipality->clean($_POST['location'] ?? '');

$capacity = $_POST['capacity'] ?? 0;

$occupied = $_POST['occupied'] ?? 0;


if (!$municipality->validateInt($id)) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid shelter id'
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

if (!$municipality->validateInt($occupied)) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid occupied value'
    ]);

    exit;
}


$capacity = (int)$capacity;

$occupied = (int)$occupied;



if ($occupied > $capacity) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Occupied cannot exceed capacity'
    ]);

    exit;
}


$data = [

    'id' => (int)$id,

    'shelter_name' => trim($shelter_name),

    'location' => trim($location),

    'capacity' => $capacity,

    'occupied' => $occupied

];


$result = $municipality->updateShelter($data);


if (is_array($result) && isset($result['status'])) {

    echo json_encode($result);

    exit;
}

echo json_encode([
    'status'  => 'success',
    'message' => 'Shelter updated successfully'
]);
