<?php
session_start();
require_once('../class/DAL.class.php');
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in'])) {
    echo json_encode(['count' => 0]);
    exit;
}

$dal = new DAL();
$count = $dal->countUnread(); 

echo json_encode(['count' => (int)$count]);