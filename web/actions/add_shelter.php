<?php

session_start();

header('Content-Type: application/json');

require_once("../class/municipality.class.php");

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

$shelter = new muni();

$organization_id =
    $shelter->clean($_POST['organization_id'] ?? '');

$organization_name =
    $shelter->clean($_POST['organization_name'] ?? '');

$organization_location =
    $shelter->clean($_POST['organization_location'] ?? '');

$organization_email =
    $shelter->clean($_POST['organization_email'] ?? '');

$organization_password =
    $shelter->clean($_POST['organization_password'] ?? '');

$shelter_name =
    $shelter->clean($_POST['shelter_name'] ?? '');

$location =
    $shelter->clean($_POST['location'] ?? '');

$capacity =
    $shelter->clean($_POST['capacity'] ?? '');


// VALIDATION

if (
    empty($shelter_name) ||
    empty($location) ||
    empty($capacity)
) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Shelter fields are required'
    ]);

    exit;
}


// if no organization selected
// require new municipality fields

if (
    empty($organization_id)
) {

    if (
        empty($organization_name) ||
        empty($organization_location) ||
        empty($organization_email) ||
        empty($organization_password)
    ) {

        echo json_encode([
            'status' => 'error',
            'message' => 'Municipality fields are required'
        ]);

        exit;
    }
}


$result = $shelter->insertShelter(
    $organization_id,
    $organization_name,
    $organization_location,
    $organization_email,
    $organization_password,
    $shelter_name,
    $location,
    $capacity
);


if (
    is_array($result) &&
    isset($result['status']) &&
    $result['status'] == 'error'
) {

    echo json_encode([
        'status' => 'error',
        'message' => $result['message']
    ]);
} elseif (!$result) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to add shelter'
    ]);
} else {

    echo json_encode([
        'status' => 'success',
        'message' => 'Shelter added successfully'
    ]);
}
