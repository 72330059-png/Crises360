<?php
session_start();
require_once('../class/users.class.php');
header('Content-Type: application/json');

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
