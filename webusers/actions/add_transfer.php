<?php

session_start();

require_once("../class/hospital.class.php");
// echo "<pre>";

// print_r($_POST);

// exit;
$hospital = new hospital_dashboard();

$hospital_id = $_POST['hospital_id'];

$destination_organization_id =
$_POST['destination_organization_id'];

$patients_count = $_POST['patients_count'];

// $status = $_POST['status'];

$result = $hospital->addTransfer(
    $hospital_id,
    $destination_organization_id,
    $patients_count
);
// var_dump($result);
// exit;
if($result){

    /*UPDATE TOTAL PATIENTS*/

    // $hospital->decreasePatients(
    //     $hospital_id,
    //     $patients_count
    // );

    echo json_encode([
        "success" => true
    ]);

}else{

    echo json_encode([
        "success" => false
    ]);

}