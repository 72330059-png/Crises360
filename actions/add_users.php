<?php

require_once("../class/users.class.php");

$indexx = new users();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $indexx->clean($_POST['name']);
    $email = $indexx->clean($_POST['email']);
    $password = $_POST['pass'];
    $role = $indexx->clean($_POST['role']);


    if (!$indexx->validateEmail($email)) {

        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid email'
        ]);

        exit;
    }

  
    $hashed = password_hash($password, PASSWORD_DEFAULT);

      $existing = $indexx->checkDuplicateuserinadd($name, $email, $role);

    if (!empty($existing)) {

        echo json_encode([
            'status' => 'error',
            'message' => 'User already exists.'
        ]);

        exit;
    }

    $result = $indexx->insertuser(
        $name,
        $email,
        $hashed,
        $role
    );

    if ($result) {

        echo json_encode([
            'status' => 'success',
            'message' => 'User added successfully!'
        ]);
    } else {

        echo json_encode([
            'status' => 'error',
            'message' => 'Insert failed'
        ]);
    }
}
