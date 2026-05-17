<?php
session_start();
header('Content-Type: application/json');

require_once("../class/hospitals.class.php");

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request'
    ]);
    exit;
}

$hospital = new hospital();

$name = $hospital->clean($_POST['name'] ?? '');
$location = $hospital->clean($_POST['location'] ?? '');
$email = $hospital->clean($_POST['email'] ?? '');
$password = $hospital->clean($_POST['password'] ?? '');
$total_beds = $hospital->clean($_POST['total_beds'] ?? '');
$hospital_status = $hospital->clean($_POST['hospital_status'] ?? 'Stable');

if (empty($name) || empty($location) || empty($email) || empty($password) || empty($total_beds)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'All fields are required'
    ]);
    exit;
}

if (!$hospital->validateInt($total_beds)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Total beds must be a number'
    ]);
    exit;
}

$result = $hospital->insertHospital2(
    $name,
    $location,
    $email,
    $password,
    $total_beds,
    $hospital_status
);

if ($result === false) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to insert hospital'
    ]);
} else {
    echo json_encode([
        'status' => 'success',
        'message' => 'Hospital added successfully'
    ]);
}
