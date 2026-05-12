<<<<<<< HEAD
<?php
require_once('../class/users.class.php');
header('Content-Type: application/json');
session_start();


if (!isset($_SESSION['id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
  exit;
}

$index = new users();
$data = $index->getAdminById($_SESSION['id']);
// var_dump($data);exit;
if ($data) {
  echo json_encode([
    'status' => 'success',
    'data'   => $data
  ]);
} else {
  echo json_encode([
    'status' => 'error',
    'message' => 'Admin not found'
  ]);
}
=======
<?php
require_once('../class/users.class.php');
header('Content-Type: application/json');
session_start();


if (!isset($_SESSION['id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
  exit;
}

$index = new users();
$data = $index->getAdminById($_SESSION['id']);
// var_dump($data);exit;
if ($data) {
  echo json_encode([
    'status' => 'success',
    'data'   => $data
  ]);
} else {
  echo json_encode([
    'status' => 'error',
    'message' => 'Admin not found'
  ]);
}
>>>>>>> a2bd2e69c4ac9840f7cbf5a9fa1f22a9c525c7e8
