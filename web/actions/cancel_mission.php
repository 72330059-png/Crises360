<?php
session_start();
require_once("../class/police.class.php");
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$mission_id = isset($_POST['mission_id']) ? (int)$_POST['mission_id'] : 0;

if (!$mission_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid mission ID']);
    exit;
}

$police = new police();
$result = $police->cancelMission($mission_id);

echo json_encode($result);