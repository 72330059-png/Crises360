<?php

header("Content-Type: application/json");
require_once "db.php";

if (!$conn) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]);
    exit;
}

$full_name      = $_POST['full_name'] ?? '';
$national_id    = $_POST['national_id'] ?? '';
$phone          = $_POST['phone'] ?? '';
$gender         = $_POST['gender'] ?? '';
$dob            = $_POST['dob'] ?? '';
$family_status  = $_POST['family_status'] ?? '';
$blood_group    = $_POST['blood_group'] ?? '';
$father_name    = $_POST['father_name'] ?? '';
$mother_name    = $_POST['mother_name'] ?? '';
$country        = $_POST['country'] ?? '';
$place_of_birth = $_POST['place_of_birth'] ?? '';

$username       = $_POST['username'] ?? '';
$password       = $_POST['password'] ?? '';

// VALIDATION
if (
    empty($full_name) ||
    empty($phone) ||
    empty($username) ||
    empty($password)
) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing required fields"
    ]);
    exit;
}

// CHECK USERNAME
$check = $conn->prepare("SELECT id FROM members WHERE username = ?");
$check->bind_param("s", $username);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Username already exists"
    ]);
    exit;
}
$check->close();

// HASH PASSWORD
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// INSERT
$stmt = $conn->prepare("
    INSERT INTO members
    (full_name, national_id, phone, gender, dob, family_status,
     blood_group, father_name, mother_name, country, place_of_birth,
     username, password)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sssssssssssss",
    $full_name,
    $national_id,
    $phone,
    $gender,
    $dob,
    $family_status,
    $blood_group,
    $father_name,
    $mother_name,
    $country,
    $place_of_birth,
    $username,
    $hashedPassword
);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Account created successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();

?>