<?php
date_default_timezone_set('Asia/Beirut');
header('Content-Type: application/json');
include 'db.php';

$user_id       = intval($_POST['user_id']       ?? 0);
$full_name     = trim($_POST['full_name']        ?? '');
$national_id   = trim($_POST['national_id']      ?? '');
$phone         = trim($_POST['phone']            ?? '');
$dob           = trim($_POST['dob']              ?? '');
$gender        = trim($_POST['gender']           ?? '');
$family_status = trim($_POST['family_status']    ?? '');
$blood_group   = trim($_POST['blood_group']      ?? '');
$father_name   = trim($_POST['father_name']      ?? '');
$mother_name   = trim($_POST['mother_name']      ?? '');
$country       = trim($_POST['country']          ?? '');
$place_of_birth= trim($_POST['place_of_birth']   ?? '');

if ($user_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid user ID"]);
    exit;
}

$stmt = $conn->prepare("
    UPDATE members SET
        full_name      = ?,
        national_id    = ?,
        phone          = ?,
        dob            = ?,
        gender         = ?,
        family_status  = ?,
        blood_group    = ?,
        father_name    = ?,
        mother_name    = ?,
        country        = ?,
        place_of_birth = ?
    WHERE id = ?
");
$stmt->bind_param(
    "sssssssssssi",
    $full_name, $national_id, $phone, $dob,
    $gender, $family_status, $blood_group,
    $father_name, $mother_name, $country,
    $place_of_birth, $user_id
);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Profile updated"]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}

$conn->close();
?>