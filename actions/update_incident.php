<<<<<<< HEAD
<?php

require_once("../class/incidents.class.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request'
    ]);

    exit;
}

$id = intval($_POST['id'] ?? 0);

$name        = trim($_POST['incident_name'] ?? '');
$location    = trim($_POST['location'] ?? '');
$severity    = trim($_POST['severity'] ?? '');
$status      = trim($_POST['status'] ?? '');
// $description = trim($_POST['description'] ?? '');

if (
    $id <= 0 ||
    empty($name) ||
    empty($location) ||
    empty($severity) ||
    empty($status) 
) {

    echo json_encode([
        'status' => 'error',
        'message' => 'All fields are required'
    ]);

    exit;
}

$incident = new incident();

$result = $incident->updateIncident(
    $id,
    $name,
    $location,
    $severity,
    $status
);

if ($result) {

    echo json_encode([
        'status' => 'success',
        'message' => 'Incident updated successfully'
    ]);
} else {

    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to update incident'
    ]);
}
=======
<?php

require_once("../class/incidents.class.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request'
    ]);

    exit;
}

$id = intval($_POST['id'] ?? 0);

$name        = trim($_POST['incident_name'] ?? '');
$location    = trim($_POST['location'] ?? '');
$severity    = trim($_POST['severity'] ?? '');
$status      = trim($_POST['status'] ?? '');
// $description = trim($_POST['description'] ?? '');

if (
    $id <= 0 ||
    empty($name) ||
    empty($location) ||
    empty($severity) ||
    empty($status) 
) {

    echo json_encode([
        'status' => 'error',
        'message' => 'All fields are required'
    ]);

    exit;
}

$incident = new incident();

$result = $incident->updateIncident(
    $id,
    $name,
    $location,
    $severity,
    $status
);

if ($result) {

    echo json_encode([
        'status' => 'success',
        'message' => 'Incident updated successfully'
    ]);
} else {

    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to update incident'
    ]);
}
>>>>>>> a2bd2e69c4ac9840f7cbf5a9fa1f22a9c525c7e8
