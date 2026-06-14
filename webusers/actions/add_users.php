<?php 
session_start();
require_once("../class/index.class.php");
$indexx = new index();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['pass'];
    $role = $_POST['role'];

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $existing = $indexx->checkDuplicateuser($name, $email, $role);

    if (!empty($existing)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'A user with same name, email and role already exists.'
        ]);
        exit;
    }

    $indexx->insertuser($name, $email, $hashed, $role);

    echo json_encode([
        'status' => 'success',
        'message' => 'User added successfully!'
    ]);
}
