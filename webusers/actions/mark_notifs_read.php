<?php
session_start();
header('Content-Type: application/json');
require_once('../class/DAL.class.php');

$orgId = (int)($_SESSION['org_id'] ?? 0);
$id    = (int)($_POST['id'] ?? 0);

if (!$orgId || !$id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$dal = new DAL();
$dal->executeSafe(
    "UPDATE notifications SET is_read = 1 WHERE id = ? AND target_org_id = ?",
    [$id, $orgId]
);

echo json_encode(['status' => 'success']);