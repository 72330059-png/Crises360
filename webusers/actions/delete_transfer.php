<?php

require_once("../class/hospital.class.php");

header("Content-Type: application/json");

$hospital = new hospital_dashboard();

$result = $hospital->deletetransfer(
    $_POST['transfer_id']
);

echo json_encode([
    "status" => "success"
]);