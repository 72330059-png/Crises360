<?php
header("Content-Type: application/json");

require_once("../class/hospitals.class.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['id'] ?? 0;

    if (!$id) {
        echo json_encode([
            "success" => false,
            "message" => "Hospital ID is required"
        ]);
        exit;
    }

    $hospital = new hospital();

    $result = $hospital->deleteHospital($id);

    if ($result) {
        echo json_encode([
            "success" => true,
            "message" => "Hospital deleted successfully"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to delete hospital"
        ]);
    }

} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request"
    ]);
}