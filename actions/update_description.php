<<<<<<< HEAD
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

$id = $incident->clean($_POST['id'] ?? '');

$description = $incident->clean($_POST['description'] ?? '');

if (
    empty($id) ||
    empty($description)
) {

    echo json_encode([
        'status' => 'error',
        'message' => 'All fields are required'
    ]);

    exit;
}

if (!$incident->validateInt($id)) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid incident id'
    ]);

    exit;
}

$result = $incident->updateDescription($id, $description);

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
        'message' => 'Description updated successfully'
    ]);
=======
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

$id = $incident->clean($_POST['id'] ?? '');

$description = $incident->clean($_POST['description'] ?? '');

if (
    empty($id) ||
    empty($description)
) {

    echo json_encode([
        'status' => 'error',
        'message' => 'All fields are required'
    ]);

    exit;
}

if (!$incident->validateInt($id)) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid incident id'
    ]);

    exit;
}

$result = $incident->updateDescription($id, $description);

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
        'message' => 'Description updated successfully'
    ]);
>>>>>>> a2bd2e69c4ac9840f7cbf5a9fa1f22a9c525c7e8
}