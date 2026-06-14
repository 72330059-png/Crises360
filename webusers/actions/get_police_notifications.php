<?php
session_start();
require_once('../class/DAL.class.php');
require_once('../class/police.class.php');

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['type'] !== 'police') {
    echo json_encode(['count' => 0, 'sentMissions' => [], 'canceledNotifs' => []]);
    exit;
}

$orgId  = (int)($_SESSION['org_id'] ?? 0);
$police = new Police();

$sentMissions   = $police->getSentMissions($orgId);
$canceledNotifs = $police->getCanceledMissionNotifs($orgId);
$count          = count($sentMissions) + count($canceledNotifs);

echo json_encode([
    'count'          => $count,
    'sentMissions'   => $sentMissions,
    'canceledNotifs' => $canceledNotifs
]);