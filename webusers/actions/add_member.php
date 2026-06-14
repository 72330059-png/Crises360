<?php
session_start();
require_once("../class/hospital.class.php");
header("Content-Type: application/json");

$hospital = new hospital_dashboard();

$result = $hospital->addTeamMember(
    $_POST['team_id'],
    trim($_POST['member_name']),
    trim($_POST['role'])
);

echo json_encode([
    "status" => "success"
]);