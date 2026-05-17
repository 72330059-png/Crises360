<?php

header("Content-Type: application/json");

require_once("../class/hospitals.class.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // GET DATA
    $name = $_POST['name'] ?? '';
    $location = $_POST['location'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $total_beds = $_POST['total_beds'] ?? 0;
    $hospital_status = $_POST['hospital_status'] ?? 'Stable';

    // VALIDATION
    if (
        empty($name) ||
        empty($location) ||
        empty($email) ||
        empty($password) ||
        empty($total_beds)
    ) {

        echo json_encode([
            "success" => false,
            "message" => "Missing required fields"
        ]);

        exit;
    }

    $hospital = new hospital();

    $result = $hospital->insertHospital(
        $name,
        $location,
        $email,
        $password,
        $total_beds,
        $hospital_status
    );

    // SUCCESS
    if ($result === true || is_numeric($result)) {

        echo json_encode([
            "success" => true,
            "message" => "Hospital added successfully"
        ]);

    } else {

        echo json_encode([
            "success" => false,
            "message" => $result
        ]);
    }

} else {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request"
    ]);
}