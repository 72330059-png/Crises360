<?php
require_once('../class/index.class.php');
header('Content-Type: application/json');
session_start();

// 1) Check login
if (!isset($_SESSION['id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
  exit;
}

// 2) Fetch admin data
$index = new Index();
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
