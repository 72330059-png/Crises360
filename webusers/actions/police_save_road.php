<?php
session_start();
require_once("../class/police.class.php");

if (!isset($_SESSION['logged_in']) || $_SESSION['type'] !== 'police') {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit;
}

$police   = new Police();
$org_id   = (int)$_SESSION['org_id'];
$location = $_SESSION['org_location'];

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['status'=>'error','message'=>'Invalid input']); exit;
}

$road_name = trim($data['road_name'] ?? '');
$road_type = trim($data['road_type'] ?? 'blocked');
$reason    = trim($data['reason']    ?? '');
// $notes     = trim($data['notes']     ?? '');
$region    = trim($data['region']    ?? $location);
$points    = $data['points']         ?? [];

if (!$road_name) {
    echo json_encode(['status'=>'error','message'=>'Road name required']); exit;
}

$id = $police->addPoliceRoad($org_id, $road_name, $road_type, $points, $reason, $region);

if ($id) {
    echo json_encode(['status'=>'success','id'=>(int)$id]);
} else {
    echo json_encode(['status'=>'error','message'=>'Failed to save road']);
}