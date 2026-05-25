<?php
session_start();
require_once("../class/incidents.class.php");

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$incident = new incident();
$range = $_GET['range'] ?? 'week';

if ($range === 'month') {

    $rows = $incident->incidentsLastMonth();
    $total = $incident->totalIncidentsLastMonth();
    $resolved = $incident->resolvedIncidentsLastMonth();

    $labels = [];
    $values = [];
    foreach ($rows as $row) {
        $labels[] = date('M d', strtotime($row['day_date']));
        $values[] = (int)$row['total'];
    }

} else {

    $rows = $incident->incidentsThisWeek();
    $total = $incident->totalIncidentsThisWeek();
    $resolved = $incident->resolvedIncidentsThisWeek();

    $dayMap = [
        'Monday'    => 0,
        'Tuesday'   => 0,
        'Wednesday' => 0,
        'Thursday'  => 0,
        'Friday'    => 0,
        'Saturday'  => 0,
        'Sunday'    => 0
    ];
    foreach ($rows as $row) {
        if (isset($dayMap[$row['day_name']])) {
            $dayMap[$row['day_name']] = (int)$row['total'];
        }
    }
    $labels = array_keys($dayMap);
    $values = array_values($dayMap);
}

echo json_encode([
    'labels'   => $labels,
    'values'   => $values,
    'total'    => $total,
    'resolved' => $resolved,
    'pending'  => $total - $resolved
]);