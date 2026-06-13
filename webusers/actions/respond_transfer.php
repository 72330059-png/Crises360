<?php
session_start();
require_once("../class/hospital.class.php");

$hospital    = new hospital_dashboard();
$transfer_id = (int)$_POST['transfer_id'];
$action      = $_POST['action']; // 'accepted' or 'rejected'
$notif_id = (int)$_POST['notif_id'];

if (!in_array($action, ['accepted', 'rejected'])) {
    echo json_encode(["success" => false, "message" => "Invalid action"]);
    exit;
}

$updated = $hospital->updateTransferStatus($transfer_id, $action);

if ($updated) {
    $hospital->markHospitalNotifRead($notif_id);
    // Get transfer info to notify hospital X
    $transfer = $hospital->getTransferById($transfer_id);
    if ($action === 'accepted') {

        $patients = (int)$transfer['patients_count'];

        // Hospital X (sender)
        $senderHospitalId = $transfer['hospital_id'];

        // Hospital Y (receiver)
        $destinationHospital = $hospital->getHospitalByOrganization(
            $transfer['destination_organization_id']
        );

        $destinationHospitalId = $destinationHospital['id'];

        // Current totals
        $senderPatients = $hospital->getTotalPatients($senderHospitalId);

        $destinationPatients = $hospital->getTotalPatients($destinationHospitalId);

        // Update sender hospital
        $hospital->updateTotalPatients(
            $senderHospitalId,
            max(0, $senderPatients - $patients)
        );

        // Update destination hospital
        $hospital->updateTotalPatients(
            $destinationHospitalId,
            $destinationPatients + $patients
        );
    }
    // Get name of hospital Y 
    $responderName = $_SESSION['org_name'];

    if ($action === 'accepted') {
        $message = "✅ " . $responderName . " accepted your transfer request of " . $transfer['patients_count'] . " patient(s).";
        $type    = 'transfer_accepted';
    } else {
        $message = "❌ " . $responderName . " rejected your transfer request of " . $transfer['patients_count'] . " patient(s).";
        $type    = 'transfer_rejected';
    }

    // Notify hospital X  and get their org_id from the transfer
    $senderOrgId = $hospital->getOrgIdByHospitalId($transfer['hospital_id']);

    $hospital->addHospitalNotification(
        $senderOrgId,            // to: hospital X
        $_SESSION['org_id'],     // from: hospital Y
        $transfer_id,
        $message,
        $type
    );

    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update"]);
}
