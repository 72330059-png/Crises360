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

$hospital_id = $_POST['hospital_id'];
$team_name = trim($_POST['team_name']);
$status = trim($_POST['status']);
$current_location = trim($_POST['current_location']);

$members = json_decode($_POST['members'], true);

$hospital = new hospital_dashboard();
$team_id = $hospital->addTeam(
    $hospital_id,
    $team_name,
    $status,
    $current_location
);

if (is_array($team_id) && isset($team_id['status'])) {

    echo json_encode([
        "status" => "error",
        "message" => $team_id['message']
    ]);

    exit;
}

foreach ($members as $member) {

    $member_name = trim($member['member_name']);
    $role = trim($member['role']);

    if (empty($member_name) || empty($role)) {
        continue;
    }

    $result = $hospital->addTeamMember(
        $team_id,
        $member_name,
        $role
    );

    if (is_array($result) && isset($result['status'])) {

        echo json_encode([
            "status" => "error",
            "message" => $result['message']
        ]);

        exit;
    }
}

echo json_encode([
    "status" => "success",
    "message" => "Team added successfully"
]);