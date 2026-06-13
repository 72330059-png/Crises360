<?php
session_start();
require_once('../class/DAL.class.php');
header('Content-Type: application/json');

$dal = new DAL();
$id  = (int)($_POST['id'] ?? 0);

$dal->markAsRead($id);
echo json_encode(['status' => 'success']);