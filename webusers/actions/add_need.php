<?php
session_start();
header('Content-Type: application/json');
require_once("../class/municipality.class.php");

$municipality = new Municipality();

if (!isset($_SESSION['org_id'])) {

    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized'
    ]);

    exit;
}

$data = [

    'organization_id' => (int)$_SESSION['org_id'],

    'need_name' => $_POST['need_name'] ?? '',

    'category' => $_POST['category'] ?? '',

    'quantity' => (int)($_POST['quantity'] ?? 0),

    'priority' => $_POST['priority'] ?? '',

    'description' => $_POST['description'] ?? ''

];

$result = $municipality->addNeed($data);
if ($result === true || is_numeric($result)) {
    $municipality->insertNotification(
        'New need added: ' . $_POST['need_name'] . ' — Priority: ' . $_POST['priority'],
        'need'
    );
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add need']);
}
