<?php
session_start();
header('Content-Type: application/json');
require_once('../class/hospital.class.php');

$orgId = (int)($_SESSION['org_id'] ?? 0);
$id    = (int)($_POST['id'] ?? 0);

if (!$orgId || !$id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$hospital = new hospital_dashboard();
$hospital->markHospitalNotifRead($id);

echo json_encode(['status' => 'success']);