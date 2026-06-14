<?php
session_start();
require_once('../class/hospital.class.php');

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['type'] !== 'hospital') {
    echo json_encode(['count' => 0, 'notifications' => []]);
    exit;
}

$orgId    = (int)($_SESSION['org_id'] ?? 0);
$hospital = new hospital_dashboard();

$notifications = $hospital->getHospitalNotifications($orgId);
$count         = (int)$hospital->getHospitalNotifCount($orgId);

echo json_encode([
    'count'         => $count,
    'notifications' => $notifications
]);