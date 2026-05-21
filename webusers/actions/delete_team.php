<?php

session_start();

require_once("../class/hospital.class.php");

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

    echo json_encode([
        "status" => "error",
        "message" => "Invalid Request"
    ]);

    exit;
}

$team_id = $_POST['team_id'];

$hospital = new hospital_dashboard();

$result = $hospital->deleteTeam($team_id);

if (is_array($result) && isset($result['status'])) {

    echo json_encode([
        "status" => "error",
        "message" => $result['message']
    ]);

    exit;
}

echo json_encode([
    "status" => "success",
    "message" => "Team deleted successfully"
]);
