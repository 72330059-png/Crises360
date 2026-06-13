<?php
session_start();
require_once('../class/police.class.php');
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['profilePassword'] ?? '');
$phone = trim($_POST['phone'] ?? '');

if ($name === '' || $email === '') {
    echo json_encode(['status' => 'error', 'message' => 'Name and email are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
    exit;
}
$police = new Police();

if ($_SESSION['type'] === 'hospital') {
    if (!$police->validatePhone($phone)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid phone number'
        ]);
        exit;
    }
}

if ($password !== '') {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $updated = $police->executeSafe(
        "UPDATE organizations SET name=?, email=?, password=? WHERE id=?",
        [$name, $email, $hashed, (int)$_SESSION['org_id']]
    );
} else {
    $updated = $police->executeSafe(
        "UPDATE organizations SET name=?, email=? WHERE id=?",
        [$name, $email, (int)$_SESSION['org_id']]
    );
}
if ($_SESSION['type'] === 'hospital')
{
    $police->executeSafe(
        "UPDATE hospitals
         SET phone = ?
         WHERE organization_id = ?",
        [$phone, (int)$_SESSION['org_id']]
    );
}
if ($updated) {
    $_SESSION['org_name'] = $name;
    echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update profile']);
}