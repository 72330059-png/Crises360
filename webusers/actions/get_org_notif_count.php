<?php
session_start();
require_once('../class/DAL.class.php');
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in'])) {
    echo json_encode(['count' => 0]);
    exit;
}

$orgType = $_SESSION['type']   ?? '';
$orgId   = (int)($_SESSION['org_id'] ?? 0);
$count   = 0;

if ($orgType === 'police' && $orgId) {
    $dal = new DAL();

    $sentRow = $dal->getRowSafe(
        "SELECT COUNT(*) as cnt
         FROM police_missions pm
         JOIN police_units pu ON pu.current_mission_id = pm.mission_id
         WHERE pu.organization_id = ? AND pm.status = 'sent'",
        [$orgId]
    );
    $sentCount = (int)($sentRow['cnt'] ?? 0);

    $cancelRow = $dal->getRowSafe(
        "SELECT COUNT(*) as cnt FROM notifications
         WHERE type = 'mission_canceled'
         AND target_org_id = ?
         AND is_read = 0",
        [$orgId]
    );
    $canceledCount = (int)($cancelRow['cnt'] ?? 0);

    $count = $sentCount + $canceledCount;

} elseif ($orgType === 'municipality' && $orgId) {
    $dal = new DAL();
    $row = $dal->getRowSafe(
        "SELECT COUNT(*) as cnt FROM notifications
         WHERE target_org_id = ? AND is_read = 0",
        [$orgId]
    );
    $count = (int)($row['cnt'] ?? 0);

} elseif ($orgType === 'hospital' && $orgId) {
    require_once('../class/hospital.class.php');
    $hospital = new hospital_dashboard();
    $count    = (int)$hospital->getHospitalNotifCount($orgId);
}

echo json_encode(['count' => $count]);