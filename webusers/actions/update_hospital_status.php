<?php
session_start();
require_once("../class/hospital.class.php");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

$hospital_id          = (int)$_POST['hospital_id'];
$hospital_status      = $_POST['hospital_status'];
$infrastructure_status = $_POST['infrastructure_status'];
$power_status         = $_POST['power_status'];
$water_status         = $_POST['water_status'];

$hospital = new hospital_dashboard();

$result = $hospital->updateHospitalStatus(
    $hospital_id,
    $hospital_status,
    $infrastructure_status,
    $power_status,
    $water_status
);

if (is_array($result) && isset($result['status']) && $result['status'] == 'error') {
    echo json_encode(["status" => "error", "message" => $result['message']]);
    exit;
}



echo json_encode(["status" => "success", "message" => "Hospital statuses updated successfully"]);