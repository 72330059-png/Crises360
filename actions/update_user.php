<?php 
require_once("../class/index.class.php");
$indexx = new index();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    // $password = $_POST['pass'];
    $password = trim($_POST['pass']); 

    $role = $_POST['role'];

    $existing = $indexx->checkDuplicateuserUpdate($name, $email, $role, $id);

    if (!empty($existing)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'A user with same name, email and role already exists.'
        ]);
        exit;
    }

    $indexx->updateuser($id, $name, $email, $password, $role);

    echo json_encode([
        'status' => 'success',
        'message' => 'User updated successfully!'
    ]);
}
