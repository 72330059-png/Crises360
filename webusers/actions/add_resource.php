<?php

session_start();

header("Content-Type: application/json");

require_once("../class/municipality.class.php");

$municipality = new Municipality();

$data = [

    "organization_id" => $_SESSION['org_id'],

    "resource_name" => $_POST['resource_name'],

    "category" => $_POST['category'],

    "address" => $_POST['address'],

    "contact_number" => $_POST['contact_number'],

    "opening_hours" => $_POST['opening_hours'],

    "status" => $_POST['status'],

    "notes" => $_POST['notes']

];
if (!$municipality->validatePhone($_POST['contact_number'])) {

    echo json_encode([

        'status' => 'error',

        'message' => 'Invalid phone number'

    ]);

    exit;
}
$result = $municipality->addResource($data);

if ($result) {

    echo json_encode([

        "status" => "success",

        "message" => "Resource added successfully"

    ]);
} else {

    echo json_encode([

        "status" => "error",

        "message" => "Failed to add resource"

    ]);
}
