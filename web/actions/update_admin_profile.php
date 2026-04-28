
<?php 
require_once('../class/index.class.php');
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$name  = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$rawPassword = trim($_POST['profilePassword'] ?? '');

if (empty($name) || empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

$index = new Index();

/* 🔥 ONLY hash if password is provided */
$password = null;
if ($rawPassword !== '') {
    $password = password_hash($rawPassword, PASSWORD_DEFAULT);
}

$updated = $index->updateAdmin($_SESSION['id'], $name, $email, $password);

if ($updated) {
    $_SESSION['name'] = $name;
    echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update profile']);
}
?>