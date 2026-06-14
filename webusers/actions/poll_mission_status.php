<?php
session_start();
require_once('../class/DAL.class.php');
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['type'] !== 'police') {
    echo json_encode(['status' => 'error']);
    exit;
}

$mission_id = (int)($_GET['mission_id'] ?? 0);
$dal = new DAL();

if ($mission_id <= 0) {
    echo json_encode(['mission_status' => 'none']);
    exit;
}

$row = $dal->getRowSafe(
    "SELECT status, title FROM police_missions WHERE mission_id = ?",
    [$mission_id]
);

if ($row) {
    echo json_encode([
        'mission_status' => $row['status'],
        'title'          => $row['title']
    ]);
} else {
    // Row deleted = canceled
    echo json_encode(['mission_status' => 'none']);
}