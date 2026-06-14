<?php
ob_start();
require_once("../class/users.class.php");
$indexx = new users();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = trim($_POST['pass']); 
    $role = $_POST['role'];
    $existing = $indexx->checkDuplicateuser($name, $email, $role, $id);

    if (!empty($existing)) {
        ob_end_clean();
        echo json_encode([
            'status' => 'error',
            'message' => 'A user with same name, email and role already exists.'
        ]);
        exit;
    }

    $indexx->updateuser($id, $name, $email, $password, $role);
    ob_end_clean();
    echo json_encode([
        'status' => 'success',
        'message' => 'User updated successfully!'
    ]);
}