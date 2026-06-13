<?php
session_start();
require_once('../class/DAL.class.php');

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in'])) {
    echo json_encode(['count' => 0, 'notifications' => []]);
    exit;
}

$dal           = new DAL();
$count         = (int)$dal->countUnread();
$notifications = $dal->getUnreadNotifications();

echo json_encode([
    'count'         => $count,
    'notifications' => $notifications
]);