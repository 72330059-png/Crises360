<?php

session_start();

header('Content-Type: application/json');

require_once("../class/incidents.class.php");

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

$incident = new incident();

$incident_name = $incident->clean($_POST['incident_name'] ?? '');

$location = $incident->clean($_POST['location'] ?? '');

$severity = $incident->clean($_POST['severity'] ?? '');

$status = $incident->clean($_POST['status'] ?? '');

$description = $incident->clean($_POST['description'] ?? '');

if (
    empty($incident_name) ||
    empty($location) ||
    empty($severity) ||
    empty($status) ||
    empty($description)
) {

    echo json_encode([
        'status' => 'error',
        'message' => 'All fields are required'
    ]);

    exit;
}

$result = $incident->insertIncident(
    $incident_name,
    $location,
    $severity,
    $status,
    $description
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
} else {

    echo json_encode([
        'status' => 'success',
        'message' => 'Incident added successfully'
    ]);
}
