<?php
session_start();
require_once("../class/hospital.class.php");

$hospital    = new hospital_dashboard();
$transfer_id = (int)$_POST['transfer_id'];

$updated = $hospital->updateTransferStatus($transfer_id, 'completed');

echo json_encode(["success" => (bool)$updated]);