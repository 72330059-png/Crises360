<?php

require_once("../class/hospital.class.php");

header("Content-Type: application/json");

$hospital = new hospital_dashboard();

$result = $hospital->deleteTeamMember(
    $_POST['member_id']
);

echo json_encode([
    "status" => "success"
]);