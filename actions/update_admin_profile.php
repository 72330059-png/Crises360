
<?php
require_once('../class/users.class.php');
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$name  = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$rawPassword = trim($_POST['profilePassword'] ?? '');

if ($name === '' || $email === '') {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

$index = new users();


$password = null;
if ($rawPassword !== '') {
    $password = password_hash($rawPassword, PASSWORD_DEFAULT);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid email format'
    ]);
    exit;
}

$updated = $index->updateAdmin($_SESSION['id'], $name, $email, $password);

if ($updated) {
    $_SESSION['name'] = $name;
    echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update profile']);
}
?>