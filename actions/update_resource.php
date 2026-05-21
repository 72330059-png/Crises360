<?php

session_start();

header("Content-Type: application/json");

require_once("../class/municipality.class.php");

$municipality = new Municipality();

$data = [

    "resource_id" => $_POST['resource_id'],

    "resource_name" => $_POST['resource_name'],

    "category" => $_POST['category'],

    "address" => $_POST['address'],

    "contact_number" => $_POST['contact_number'],

    "opening_hours" => $_POST['opening_hours'],

    "status" => $_POST['status'],

    "notes" => $_POST['notes']

];

$result = $municipality->updateResource($data);

if ($result) {

    echo json_encode([

        "status" => "success",

        "message" => "Resource updated successfully"

    ]);

} else {

    echo json_encode([

        "status" => "error",

        "message" => "Failed to update resource"

    ]);

}