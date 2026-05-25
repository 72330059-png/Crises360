<?php
session_start();
require_once("../class/DAL.class.php");
$dal = new DAL();
$incident_id = (int)($_GET['incident_id'] ?? 0);
if(!$incident_id){
    echo json_encode(['is_resolved' => false]);
    exit;
}
$row = $dal->getRowSafe(
    "SELECT status FROM incidents WHERE id = ?",
    [$incident_id]
);
echo json_encode([
    'is_resolved' => $row && $row['status'] === 'Resolved'
]);