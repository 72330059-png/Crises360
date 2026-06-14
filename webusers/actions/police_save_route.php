<?php
session_start();
error_log("SESSION: " . json_encode($_SESSION));
require_once("../class/police.class.php");

if (!isset($_SESSION['logged_in']) || $_SESSION['type'] !== 'police') {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit;
}

$police   = new Police();
$org_id   = (int)$_SESSION['org_id'];
// $user_id  = (int)$_SESSION['id'];
$location = $_SESSION['org_location'];

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['status'=>'error','message'=>'Invalid input']); exit;
}

$from_name    = trim($data['from_name']    ?? '');
$to_name      = trim($data['to_name']      ?? '');
$route_status = trim($data['route_status'] ?? 'open');
$notes        = trim($data['notes']        ?? '');
$region       = trim($data['region']       ?? $location);
$points       = $data['points']            ?? [];

if (!$from_name || !$to_name) {
    echo json_encode(['status'=>'error','message'=>'From and To are required']); exit;
}
if (count($points) < 2) {
    echo json_encode(['status'=>'error','message'=>'At least 2 waypoints required']); exit;
}

$id = $police->addEvacRoute($org_id, $from_name, $to_name, $route_status, $notes, $region, $points);

if ($id) {
    echo json_encode(['status'=>'success','id'=>(int)$id]);
} else {
    echo json_encode(['status'=>'error','message'=>'Failed to save route']);
}