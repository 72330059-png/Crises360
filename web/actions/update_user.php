
<?php 
require_once("../class/users.class.php");
$indexx = new users();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
///id- name- pass ...

    // $id = $_POST['id'];
    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $email = $_POST['email'];
    // $password = $_POST['pass'];
    $password = trim($_POST['pass']); 

    $role = $_POST['role'];
    $existing = $indexx->checkDuplicateuser($name, $email, $role, $id);

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
