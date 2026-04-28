<?php 
require_once("../class/index.class.php");
$indexx = new index();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    // $password = $_POST['pass'];
    $password = trim($_POST['pass']); // may be empty

    $role = $_POST['role'];

    // Hash password
    // $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Check duplicate BUT exclude the same user (id != id)
    $existing = $indexx->checkDuplicateuserUpdate($name, $email, $role, $id);

    if (!empty($existing)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'A user with same name, email and role already exists.'
        ]);
        exit;
    }

    // Update user
    $indexx->updateuser($id, $name, $email, $password, $role);

    echo json_encode([
        'status' => 'success',
        'message' => 'User updated successfully!'
    ]);
}
