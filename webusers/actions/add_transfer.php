<?php
session_start();
require_once("../class/hospital.class.php");

$hospital     = new hospital_dashboard();
$hospital_id  = $_POST['hospital_id'];
$dest_org_id  = $_POST['destination_organization_id'];
$patients_count = $_POST['patients_count'];

$transfer_id = $hospital->addTransfer($hospital_id, $dest_org_id, $patients_count);

if ($transfer_id) {
    $senderName = $hospital->getHospitalNameByHospitalId($hospital_id);

    $message = "🏥 New transfer request from " . $senderName . " — " . $patients_count . " patient(s). Please accept or reject.";
    $hospital->addHospitalNotification(
        $dest_org_id,                        // to
        $_SESSION['org_id'],                 // from
        $transfer_id,                        // transfer
        $message,
        'transfer_request'
    );

    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}