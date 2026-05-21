<?php
require_once('../class/index.class.php');
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
  exit;
}

$index = new Index();
$data = $index->getAdminById($_SESSION['id']);
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
