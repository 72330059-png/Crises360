<?php

session_start();

header('Content-Type: application/json');

require_once("../class/municipality.class.php");

$municipality = new Municipality();

$data = [

    'id' => (int)($_POST['id'] ?? 0),

    'need_name' => $_POST['need_name'] ?? '',

    'category' => $_POST['category'] ?? '',

    'quantity' => (int)($_POST['quantity'] ?? 0),

    'priority' => $_POST['priority'] ?? '',

    'status' => $_POST['status'] ?? '',

    'description' => $_POST['description'] ?? ''

];

$result = $municipality->updateNeed($data);

if ($result === true) {

    echo json_encode([
        'status' => 'success'
    ]);
} else {

    echo json_encode([
        'status' => 'error',
        'message' => 'Update failed'
    ]);
}
