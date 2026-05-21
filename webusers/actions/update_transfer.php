<?php

session_start();

header("Content-Type: application/json");

require_once("../class/hospital.class.php");

$hospital = new hospital_dashboard();

$id = $hospital->validateInt(
    $hospital->clean($_POST['transfer_id'])
);

$destination_organization_id = $hospital->validateInt(
    $hospital->clean($_POST['destination_organization_id'])
);

$patients_count = $hospital->validateInt(
    $hospital->clean($_POST['patients_count'])
);

$status = $hospital->e(
    $hospital->clean($_POST['status'])
);

$allowedStatuses = [
    "Pending",
    "Completed",
    "Accepted",
    "Rejected"
];

if (
    !$id ||
    !$destination_organization_id ||
    !$patients_count ||
    !in_array($status, $allowedStatuses)
) {

    echo json_encode([
        "status" => "error",
        "message" => "Invalid data"
    ]);

    exit;
}

$result = $hospital->updateTransfer(
    $id,
    $destination_organization_id,
    $patients_count,
    $status
);

if ($result) {

    echo json_encode([
        "status" => "success"
    ]);

} else {

    echo json_encode([
        "status" => "error",
        "message" => "Update failed"
    ]);

}