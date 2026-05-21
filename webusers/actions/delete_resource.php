<?php

session_start();

header("Content-Type: application/json");

require_once("../class/municipality.class.php");

$municipality = new Municipality();

$id = (int)$_POST['resource_id'];

$result = $municipality->deleteResource($id);

if ($result) {

    echo json_encode([

        "status" => "success",

        "message" => "Resource deleted successfully"

    ]);
} else {

    echo json_encode([

        "status" => "error",

        "message" => "Failed to delete resource"

    ]);
}
