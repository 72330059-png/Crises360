<?php
session_start();
require_once('../class/map.class.php');
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$map       = new maps();
$action    = $_POST['action'] ?? '';
$id        = (int)($_POST['id'] ?? 0);
$createdBy = (int)$_SESSION['id'];  
$incidentId = (int)($_SESSION['active_incident'] ?? 0);
switch ($action) {

    case 'set_incident':
        $incId = (int)($_POST['incident_id'] ?? 0);
        $_SESSION['archive_incident'] = 0; 
        $_SESSION['view_all'] = false;        
        if ($incId === 0) {
            unset($_SESSION['active_incident']);
        } else {
            $_SESSION['active_incident'] = $incId;
        }
        echo json_encode(['status' => 'success']);
        break;

    case 'delete_alert':
        $ok = $map->deleteAlert($id);
        echo json_encode($ok
            ? ['status' => 'success']
            : ['status' => 'error', 'message' => 'Delete failed']);
        break;

    case 'delete_zone':
        $ok = $map->deleteZone($id);
        echo json_encode($ok
            ? ['status' => 'success']
            : ['status' => 'error', 'message' => 'Delete failed']);
        break;

    case 'delete_road':
        $ok = $map->deleteRoad($id);
        echo json_encode($ok
            ? ['status' => 'success']
            : ['status' => 'error', 'message' => 'Delete failed']);
        break;

    case 'add_alert':
        $result = $map->addAlert(
            $_POST['title']       ?? '',
            $_POST['severity']    ?? 'medium',
            $_POST['description'] ?? '',
            $_POST['lat']         ?? 0,
            $_POST['lng']         ?? 0,
            $_POST['region']      ?? 'unknown',
            $createdBy,
            $incidentId
        );
        echo json_encode(is_int($result) || $result === true
            ? ['status' => 'success', 'id' => (int)$result]
            : ['status' => 'error',   'message' => $result['message']]);
        break;

    case 'add_zone':
        $result = $map->addRadiusZone(
            $_POST['name']          ?? '',
            $_POST['type']          ?? 'warning',
            $_POST['center_lat']    ?? 0,
            $_POST['center_lng']    ?? 0,
            $_POST['radius_meters'] ?? 300,
            $_POST['region']        ?? 'unknown',
            $createdBy,
            $incidentId
        );
        echo json_encode(is_int($result) || $result === true
            ? ['status' => 'success', 'id' => (int)$result]
            : ['status' => 'error',   'message' => $result['message']]);
        break;

    case 'add_road':
        $result = $map->addRoad(
            $_POST['name']         ?? 'Unnamed Road',
            $_POST['status']       ?? 'open',
            $_POST['reason']       ?? '',
            json_decode($_POST['route_points'] ?? '[]', true),  
            $createdBy,
            $incidentId
        );
        echo json_encode(is_int($result) || $result === true
            ? ['status' => 'success', 'id' => (int)$result]
            : ['status' => 'error', 'message' => $result['message']]);
        break;

    case 'set_view_all':
        $val = (int)($_POST['value'] ?? 0);
        $_SESSION['archive_incident'] = 0;    
        if ($val === 1) {
            $_SESSION['view_all'] = true;
            unset($_SESSION['active_incident']);
        } else {
            unset($_SESSION['view_all']);
        }
        echo json_encode(['status' => 'success']);
        break;

    case 'set_archive':
        $_SESSION['archive_incident'] = (int)($_POST['incident_id'] ?? 0);
        $_SESSION['view_all'] = false;        
        unset($_SESSION['active_incident']);   
        echo json_encode(['status' => 'success']);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
}
