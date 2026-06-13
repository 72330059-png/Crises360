<?php
session_start();
require_once('../class/DAL.class.php');

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['type'] !== 'municipality') {
    echo json_encode(['count' => 0, 'notifications' => []]);
    exit;
}

$orgId = (int)($_SESSION['org_id'] ?? 0);
$dal   = new DAL();

$notifications = $dal->getdata(
    "SELECT * FROM notifications WHERE target_org_id = ? AND is_read = 0 ORDER BY created_at DESC",
    [$orgId]
);
$count = count($notifications);

echo json_encode([
    'count'         => $count,
    'notifications' => $notifications
]);