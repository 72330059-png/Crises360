<?php
session_start();
require_once('../class/police.class.php');

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['type'] !== 'police') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$mission_id = (int)($data['mission_id'] ?? 0);
$action     = $data['action'] ?? '';
$org_id     = (int)$_SESSION['org_id'];

if (!$mission_id || !in_array($action, ['accept', 'reject'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

$police = new Police();

if ($action === 'accept') {
    $police->updateMissionStatus($mission_id, 'active');
    $police->executeSafe(
        "UPDATE police_units SET status = 'on_mission' WHERE organization_id = ? AND current_mission_id = ?",
        [$org_id, $mission_id]
    );
} else {
    $police->updateMissionStatus($mission_id, 'rejected');
    $police->executeSafe(
        "UPDATE police_units SET current_mission_id = NULL, status = 'available' WHERE organization_id = ? AND current_mission_id = ?",
        [$org_id, $mission_id]
    );
}

echo json_encode(['status' => 'success']);
