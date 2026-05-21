<?php

session_start();

header('Content-Type: application/json');

require_once("../class/municipality.class.php");

if (!isset($_SESSION['org_id'])) {

    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized access"
    ]);

    exit;
}

$municipality = new Municipality();

$id = (int)($_POST['id'] ?? 0);

if (!$municipality->validateInt($id)) {

    echo json_encode([
        "status" => "error",
        "message" => "Invalid donation id"
    ]);

    exit;
}

$result = $municipality->deleteDonation($id);

if (is_array($result) && isset($result['status'])) {

    echo json_encode([
        "status" => "error",
        "message" => $result['message']
    ]);

    exit;
}

echo json_encode([
    "status" => "success",
    "message" => "Donation deleted successfully"
]);
