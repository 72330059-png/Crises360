<?php

session_start();

header('Content-Type: application/json');

require_once("../class/municipality.class.php");

$municipality = new Municipality();

if (!isset($_POST['id'])) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Need ID missing'
    ]);

    exit;
}

$id = (int)$_POST['id'];

$result = $municipality->deleteNeed($id);

if ($result === true) {

    echo json_encode([
        'status' => 'success',
        'message' => 'Need deleted successfully'
    ]);

} else {

    echo json_encode([
        'status' => 'error',
        'message' => 'Delete failed'
    ]);

}