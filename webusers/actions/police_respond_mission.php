<?php
session_start();
require_once('../class/police.class.php');

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['type'] !== 'police') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$data       = json_decode(file_get_contents('php://input'), true);
$mission_id = (int)($data['mission_id'] ?? 0);
$action     = $data['action'] ?? '';
$org_id     = (int)$_SESSION['org_id'];

if (!$mission_id || !in_array($action, ['accept', 'reject'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

$police = new Police();

// Fetch mission title and unit name
$missionRow = $police->getRowSafe(
    "SELECT pm.title, o.name AS unit_name
     FROM police_missions pm
     JOIN police_units pu ON pu.current_mission_id = pm.mission_id
     JOIN organizations o ON o.id = pu.organization_id
     WHERE pm.mission_id = ? AND pu.organization_id = ?",
    [$mission_id, $org_id]
);

$missionTitle = $missionRow['title']     ?? 'Mission #' . $mission_id;
$unitName     = $missionRow['unit_name'] ?? 'Unknown Unit';

if ($action === 'accept') {
    $police->updateMissionStatus($mission_id, 'active');
    $police->executeSafe(
        "UPDATE police_units SET status = 'on_mission' 
         WHERE organization_id = ? AND current_mission_id = ?",
        [$org_id, $mission_id]
    );
    $police->insertNotification(
        '"' . $missionTitle . '" was ACCEPTED by ' . $unitName,
        'mission'
    );
} else {
    $police->updateMissionStatus($mission_id, 'rejected');
    $police->executeSafe(
        "UPDATE police_units SET current_mission_id = NULL, status = 'available' 
         WHERE organization_id = ? AND current_mission_id = ?",
        [$org_id, $mission_id]
    );
    $police->insertNotification(
        '"' . $missionTitle . '" was REJECTED by ' . $unitName,
        'mission'
    );
}

echo json_encode(['status' => 'success']);