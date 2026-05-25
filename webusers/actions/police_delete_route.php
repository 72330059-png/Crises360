<?php
session_start();
require_once("../class/police.class.php");

if (!isset($_SESSION['logged_in']) || $_SESSION['type'] !== 'police') {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit;
}

$police = new Police();
$org_id = (int)$_SESSION['org_id'];

$data = json_decode(file_get_contents('php://input'), true);
$id   = (int)($data['id'] ?? 0);

if (!$id) {
    echo json_encode(['status'=>'error','message'=>'Route ID required']); exit;
}

$route = $police->getEvacRouteById($id);
if (!$route || (int)$route['organization_id'] !== $org_id) {
    echo json_encode(['status'=>'error','message'=>'Route not found or unauthorized']); exit;
}

$ok = $police->deleteEvacRoute($id);

echo json_encode($ok
    ? ['status'=>'success']
    : ['status'=>'error','message'=>'Delete failed']);