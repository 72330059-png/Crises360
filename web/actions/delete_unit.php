
<?php

require_once('../class/police.class.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request'
    ]);

    exit;
}

$id = intval($_POST['id'] ?? 0);
// var_dump($id);
// exit;

if ($id <= 0) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid incident ID'
    ]);

    exit;
}

$pol = new police();

$result = $pol->deletepolice($id);

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