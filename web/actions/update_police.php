<?php
session_start();
require_once("../class/police.class.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request'
    ]);
    exit;
}
$unit_id = intval($_POST['unit_id'] ?? 0);
$org_id  = intval($_POST['org_id'] ?? 0);
$name        = trim($_POST['pol_name'] ?? '');
$location    = trim($_POST['location'] ?? '');
$callsign    = trim($_POST['callsign'] ?? '');
$type   = trim($_POST['type'] ?? '');
// $mission  = trim($_POST['mission'] ?? '');
$status      = trim($_POST['status'] ?? '');
if (
    $unit_id <= 0 || $org_id <= 0 || empty($name) || empty($location) || empty($callsign) || empty($type) || empty($status)
) {
    echo json_encode([
        'status' => 'error',
        'message' => 'All fields are required'
    ]);
    exit;
}

$pol = new police();

$result = $pol->updatepolice(
    $unit_id,
    $org_id,
    $name,
    $location,
    $callsign,
    $type,
    // $mission,
    $status
);

if ($result) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Incident updated successfully'
    ]);
} else {

    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to update incident'
    ]);
}
