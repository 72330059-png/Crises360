<?php
session_start();
require_once('../class/police.class.php');
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$police = new Police();
$data = $police->getRowSafe(
    "SELECT name, email FROM organizations WHERE id = ?",
    [(int)$_SESSION['org_id']]
);

if ($data) {
    echo json_encode(['status' => 'success', 'data' => $data]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Not found']);
}