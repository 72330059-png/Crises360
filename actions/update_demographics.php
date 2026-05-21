<?php

session_start();

header('Content-Type: application/json');

require_once("../class/hospital.class.php");

if (!isset($_SESSION['logged_in'])) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized'
    ]);

    exit;
}

$hospital = new hospital_dashboard();


$hospital_id = $_POST['hospital_id'] ?? 0;

$filter = $_POST['filter'] ?? '';

$martyrs = $_POST['martyrs'] ?? 0;

$injured = $_POST['injured'] ?? 0;


if (!$hospital->validateInt($hospital_id)) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid hospital id'
    ]);

    exit;
}

if (!$hospital->validateInt($martyrs)) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid martyrs value'
    ]);

    exit;
}

if (!$hospital->validateInt($injured)) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid injured value'
    ]);

    exit;
}


$hospital_id = (int)$hospital_id;
$martyrs = (int)$martyrs;
$injured = (int)$injured;


if ($filter == "all") {

    echo json_encode([
        'status' => 'error',
        'message' => 'All is calculated automatically'
    ]);

    exit;
}


if ($filter == "male") {

    $sql = "UPDATE hospital_demographics
            SET
                male_martyrs = ?,
                male_injured = ?
            WHERE hospital_id = ?";

    $result = $hospital->executeSafe($sql, [
        $martyrs,
        $injured,
        $hospital_id
    ]);
}

elseif ($filter == "female") {

    $sql = "UPDATE hospital_demographics
            SET
                female_martyrs = ?,
                female_injured = ?
            WHERE hospital_id = ?";

    $result = $hospital->executeSafe($sql, [
        $martyrs,
        $injured,
        $hospital_id
    ]);
}


elseif ($filter == "children") {

    $sql = "UPDATE hospital_demographics
            SET
                children_martyrs = ?,
                children_injured = ?
            WHERE hospital_id = ?";

    $result = $hospital->executeSafe($sql, [
        $martyrs,
        $injured,
        $hospital_id
    ]);
}

else {

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid filter'
    ]);

    exit;
}


if (is_array($result) && isset($result['status']) && $result['status'] == 'error') {

    echo json_encode([
        'status' => 'error',
        'message' => $result['message']
    ]);

    exit;
}

echo json_encode([
    'status' => 'success',
    'message' => 'Demographics updated successfully'
]);