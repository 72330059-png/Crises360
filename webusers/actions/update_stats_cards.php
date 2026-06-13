<?php
session_start();
header('Content-Type: application/json');
require_once('../class/hospital.class.php');

if (!isset($_SESSION['logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}
if ($_SESSION['type'] !== 'hospital') {
    echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
    exit;
}

$hospitalObj = new hospital_dashboard();
$orgId       = (int)$_SESSION['org_id'];
$hospitalData = $hospitalObj->getHospitalByOrganization($orgId);
$hospital_id  = $hospitalData['id'];

if (!$hospitalData || $hospitalData['id'] != $hospital_id) {
    echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
    exit;
}

$total_patients = (int)$_POST['total_patients'];
$critical_cases = (int)$_POST['critical_cases'];
$available_beds = (int)$_POST['available_beds'];
$total_beds     = (int)$_POST['total_beds'];
$available_icu  = (int)$_POST['available_icu'];
$icu_beds       = (int)$_POST['icu_beds'];
$staff_on_duty  = (int)$_POST['staff_on_duty'];
$ambulances     = (int)$_POST['ambulances'];

if ($critical_cases > $total_patients) {
    echo json_encode(['status' => 'error', 'message' => 'Critical cases cannot exceed total patients.']);
    exit;
}
if ($available_beds > $total_beds) {
    echo json_encode(['status' => 'error', 'message' => 'Available beds cannot exceed total beds.']);
    exit;
}
if ($available_icu > $icu_beds) {
    echo json_encode(['status' => 'error', 'message' => 'Available ICU beds cannot exceed total ICU beds.']);
    exit;
}

$r1 = $hospitalObj->updateHospitalCards(
    $hospital_id,
    $available_beds,
    $total_beds,
    $available_icu,
    $icu_beds,
    $staff_on_duty,
    $ambulances
);

$r2 = $hospitalObj->updateDailyStatsCards(
    $hospital_id,
    $total_patients,
    $critical_cases
);

if (!$r1 || !$r2) {
    echo json_encode(['status' => 'error', 'message' => 'Update failed.']);
    exit;
}

echo json_encode(['status' => 'success', 'message' => 'Cards updated successfully.']);