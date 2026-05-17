<?php

header("Content-Type: application/json");

require_once("../class/police.class.php");

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request'
    ]);

    exit;
}
$police = new police();
$name = $police->clean($_POST['organization_name'] ?? '');
$location = $police->clean($_POST['location'] ?? '');
$email = $police->clean($_POST['email'] ?? '');
$password = $police->clean($_POST['password'] ?? '');
$callsign = $police->clean($_POST['callsign'] ?? '');
$unit_type = $police->clean($_POST['unit_type'] ?? '');

if (
    empty($name) || empty($location) || empty($email) || empty($password) || empty($callsign) || empty($unit_type)
) {
    echo json_encode([
        'status' => 'error',
        'message' => 'unit fields are required'
    ]);

    exit;
}
$result = $police->addPoliceUnit($name, $location, $email, $password, $callsign, $unit_type);
if ($result) {

    echo json_encode([
        'status' => 'success',
        'message' => 'Police unit added successfully'
    ]);
} else {

    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to add unit'
    ]);
}
