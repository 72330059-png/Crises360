<?php

session_start();

header('Content-Type: application/json');

require_once("../class/municipality.class.php");

if (!isset($_SESSION['org_id'])) {

    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized access"
    ]);

    exit;
}

$municipality = new Municipality();

$id = (int)($_POST['id'] ?? 0);

$total_amount = $municipality->clean($_POST['total_amount'] ?? '');

$donation_type = $municipality->clean($_POST['donation_type'] ?? '');

if (!$municipality->validateInt($id)) {

    echo json_encode([
        "status" => "error",
        "message" => "Invalid donation id"
    ]);

    exit;
}

if (!$municipality->validateInt($total_amount)) {

    echo json_encode([
        "status" => "error",
        "message" => "Invalid amount"
    ]);

    exit;
}

$sql = "UPDATE donations SET

        total_amount=?,
        donation_type=?

        WHERE id=?";

$result = $municipality->executeSafe($sql, [

    $total_amount,
    $donation_type,
    $id

]);

if (is_array($result) && isset($result['status'])) {

    echo json_encode([
        "status" => "error",
        "message" => $result['message']
    ]);

    exit;
}

echo json_encode([
    "status" => "success",
    "message" => "Donation updated successfully"
]);
