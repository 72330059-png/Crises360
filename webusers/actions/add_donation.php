<?php

session_start();

header('Content-Type: application/json');

require_once("../class/municipality.class.php");

if (!isset($_SESSION['org_id'])) {

    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);

    exit;
}

$municipality = new Municipality();

$organization_id = (int)$_SESSION['org_id'];

$total_amount = $municipality->clean($_POST['total_amount'] ?? '');

$donation_type = $municipality->clean($_POST['donation_type'] ?? '');

if (!$municipality->validateInt($total_amount)) {

    echo json_encode([
        "status" => "error",
        "message" => "Invalid amount"
    ]);

    exit;
}

$result = $municipality->addDonation([

    "organization_id" => $organization_id,

    "total_amount" => $total_amount,

    "donation_type" => $donation_type

]);

if (is_array($result) && isset($result['status']) && $result['status'] == 'error') {

    echo json_encode([
        "status" => "error",
        "message" => $result['message']
    ]);

    exit;
}

echo json_encode([
    "status" => "success",
    "message" => "Donation added successfully"
]);
