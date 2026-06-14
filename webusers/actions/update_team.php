<?php
session_start();
require_once("../class/hospital.class.php");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] != "POST") {

    echo json_encode([
        "status" => "error",
        "message" => "Invalid request"
    ]);

    exit;
}

$team_id = $_POST['team_id'];
$team_name = $_POST['team_name'];
$status = $_POST['status'];
$current_location = $_POST['current_location'];

$hospital = new hospital_dashboard();

$update = $hospital->updateTeam(
    $team_id,
    $team_name,
    $status,
    $current_location
);

if ($update) {

    echo json_encode([
        "status" => "success"
    ]);

} else {

    echo json_encode([
        "status" => "error",
        "message" => "Database update failed"
    ]);

}