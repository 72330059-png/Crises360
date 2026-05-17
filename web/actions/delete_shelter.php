
<?php

require_once('../class/municipality.class.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

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
        'message' => 'Invalid incident ID'
    ]);

    exit;
}

$shelter = new muni();

$result = $shelter->deleteShelter($id);

if ($result) {

    echo json_encode([
        'status' => 'success',
        'message' => 'Shleter deleted successfully'
    ]);

} else {

    echo json_encode([
        'status' => 'error',
        'message' => 'Delete failed'
    ]);

}