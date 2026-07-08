<?php
session_start();
require_once("class/DAL.class.php");
require_once("class/hospitals.class.php");
require_once("class/alerts.class.php");
require_once("class/incidents.class.php");
require_once("class/municipality.class.php");
require_once("class/police.class.php");

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

//  DATE RANGE 
$dateFrom = isset($_GET['from']) ? $_GET['from'] : date('Y-m-d', strtotime('-30 days'));
$dateTo   = isset($_GET['to'])   ? $_GET['to']   : date('Y-m-d');
$dateFrom = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom) ? $dateFrom : date('Y-m-d', strtotime('-30 days'));
$dateTo   = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)   ? $dateTo   : date('Y-m-d');

$dal     = new DAL();
$hospObj = new hospital();

//  INCIDENTS 
$incidents = $dal->getdata(
    "SELECT
        id,
        incident_name,
        location,
        status,
        severity,
        reported_at,
        resolved_at,
        CASE
            WHEN resolved_at IS NOT NULL AND reported_at IS NOT NULL
            THEN TIMESTAMPDIFF(MINUTE, reported_at, resolved_at)
            ELSE NULL
        END AS response_minutes
     FROM incidents
     WHERE DATE(reported_at) BETWEEN ? AND ?
     ORDER BY reported_at ASC",
    [$dateFrom, $dateTo]
);

// NEEDS 
$needs = $dal->getdata(
    "SELECT
        n.id,
        n.category,
        n.description,
        n.status,
        n.priority,
        o.name      AS organization_name,
        o.location  AS organization_location,
        o.type      AS organization_type,
        n.created_at
     FROM needs n
     LEFT JOIN organizations o ON o.id = n.organization_id
     WHERE DATE(n.created_at) BETWEEN ? AND ?
     ORDER BY n.created_at ASC",
    [$dateFrom, $dateTo]
);

// HOSPITALS 
// Includes infrastructure_status, power_status, water_status for trend analysis
$hospitals = $dal->getdata(
    "SELECT
        h.id,
        o.name                  AS hospital_name,
        o.location              AS location,
        h.hospital_status,
        h.infrastructure_status,
        h.power_status,
        h.water_status,
        h.total_beds,
        h.available_beds,
        h.icu_beds,
        h.available_icu_beds,
        h.staff_on_duty,
        h.ambulances
     FROM hospitals h
     LEFT JOIN organizations o ON o.id = h.organization_id
     ORDER BY o.name ASC"
);

//HOSPITAL TEAMS 
$hospitalTeams = $dal->getdata(
    "SELECT
        ht.id,
        ht.team_name,
        ht.status,
        ht.current_location,
        ht.created_at,
        o.name      AS hospital_name,
        o.location  AS region
     FROM hospital_teams ht
     LEFT JOIN hospitals h   ON h.id  = ht.hospital_id
     LEFT JOIN organizations o ON o.id = h.organization_id
     ORDER BY o.name ASC, ht.team_name ASC"
);

// HOSPITAL DEMOGRAPHICS (CASUALTIES) 

$demographics = $dal->getdata(
    "SELECT
        o.name          AS hospital_name,
        hd.male_injured,
        hd.female_injured,
        hd.children_injured,
        hd.male_martyrs,
        hd.female_martyrs,
        hd.children_martyrs,
        NULL            AS recorded_at
     FROM hospital_demographics hd
     LEFT JOIN hospitals h  ON h.id = hd.hospital_id
     LEFT JOIN organizations o ON o.id = h.organization_id
     ORDER BY o.name ASC"
);

//  SHELTERS 
$shelters = $dal->getdata(
    "SELECT
        s.shelter_name  AS name,
        s.location      AS region,
        s.capacity,
        s.occupied,
        s.available,
        s.status,
        o.name          AS organization_name
     FROM shelters s
     LEFT JOIN organizations o ON o.id = s.organization_id
     ORDER BY s.location, s.shelter_name"
);

//  ROADS 
$mapRoads = $dal->getdata(
    "SELECT
        name,
        status,
        created_at
     FROM map_roads
     WHERE is_active = 1"
);

$polRoads = $dal->getdata(
    "SELECT
        road_name,
        road_type   AS status,
        region,
        reason,
        created_at
     FROM police_roads
     WHERE is_active = 1
     ORDER BY region, road_name"
);

//  MAP ROUTES 
$mapRoutes = $dal->getdata(
    "SELECT
        r.id,
        r.from_name,
        r.to_name,
        r.region,
        r.route_status,
        r.notes,
        r.created_at
     FROM map_routes r
     ORDER BY r.region, r.from_name ASC"
);

//  MAP ZONES 
$mapZones = $dal->getdata(
    "SELECT
        id,
        name,
        type,
        region,
        radius_meters,
        is_active,
        created_at
     FROM map_zones
     WHERE is_active = 1
     ORDER BY region, type, name ASC"
);

// ALERTS 
$alerts = $dal->getdata(
    "SELECT
        id,
        alert_message,
        severity,
        region,
        created_at
     FROM alerts
     WHERE DATE(created_at) BETWEEN ? AND ?
     ORDER BY created_at ASC",
    [$dateFrom, $dateTo]
);

// MONTHLY INCIDENT TREND (last 6 months) 
$monthlyIncidents = $dal->getdata(
    "SELECT
        DATE_FORMAT(reported_at, '%Y-%m') AS month,
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'Resolved'    THEN 1 ELSE 0 END) AS resolved,
        SUM(CASE WHEN status != 'Resolved'   THEN 1 ELSE 0 END) AS active,
        AVG(CASE WHEN resolved_at IS NOT NULL
                 THEN TIMESTAMPDIFF(MINUTE, reported_at, resolved_at)
                 ELSE NULL END) AS avg_response_min
     FROM incidents
     WHERE reported_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
     GROUP BY DATE_FORMAT(reported_at, '%Y-%m')
     ORDER BY month ASC"
);

//  MONTHLY ALERTS TREND (last 6 months) 
$monthlyAlerts = $dal->getdata(
    "SELECT
        DATE_FORMAT(created_at, '%Y-%m') AS month,
        COUNT(*) AS total,
        SUM(CASE WHEN severity = 'Critical' THEN 1 ELSE 0 END) AS critical
     FROM alerts
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
     GROUP BY DATE_FORMAT(created_at, '%Y-%m')
     ORDER BY month ASC"
);

//  REGION STATS 
$regionStats = $dal->getdata(
    "SELECT
        location                                                        AS region,
        COUNT(*)                                                        AS total_incidents,
        SUM(CASE WHEN status != 'Resolved' THEN 1 ELSE 0 END)          AS active,
        SUM(CASE WHEN status = 'Resolved'  THEN 1 ELSE 0 END)          AS resolved,
        AVG(CASE WHEN resolved_at IS NOT NULL
                 THEN TIMESTAMPDIFF(MINUTE, reported_at, resolved_at)
                 ELSE NULL END)                                         AS avg_response_min
     FROM incidents
     WHERE DATE(reported_at) BETWEEN ? AND ?
     GROUP BY location
     ORDER BY total_incidents DESC",
    [$dateFrom, $dateTo]
);

//  SHELTER TREND — live shelters table only
$shelterTrend = $dal->getdata(
    "SELECT
        DATE_FORMAT(created_at, '%Y-%m') AS month,
        AVG(occupied)  AS avg_occupied,
        AVG(capacity)  AS avg_capacity
     FROM shelters
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
     GROUP BY DATE_FORMAT(created_at, '%Y-%m')
     ORDER BY month ASC"
);

// $hospitals = $hospObj->getAllHospitals();
$hospTotal = count($hospitals);
$hospDangerous = 0;
foreach ($hospitals as $h) {
    if ($h['hospital_status'] === 'Dangerous') $hospDangerous++;
}

$critInc = (int)($dal->getdata(
    "SELECT COUNT(*) c FROM incidents
     WHERE status != 'Resolved' AND DATE(reported_at) BETWEEN ? AND ?",
    [$dateFrom, $dateTo]
)[0]['c'] ?? 0);

$totalInc = max(1, (int)($dal->getdata(
    "SELECT COUNT(*) c FROM incidents
     WHERE DATE(reported_at) BETWEEN ? AND ?",
    [$dateFrom, $dateTo]
)[0]['c'] ?? 1));

$critAlerts = (int)($dal->getdata(
    "SELECT COUNT(*) c FROM alerts
     WHERE severity='Critical' AND DATE(created_at) BETWEEN ? AND ?",
    [$dateFrom, $dateTo]
)[0]['c'] ?? 0);

$totalAlerts = max(1, (int)($dal->getdata(
    "SELECT COUNT(*) c FROM alerts
     WHERE DATE(created_at) BETWEEN ? AND ?",
    [$dateFrom, $dateTo]
)[0]['c'] ?? 1));

$sevScore = min(100, (int)(
    ($critInc    / $totalInc)    * 40 +
    ($critAlerts / $totalAlerts) * 30 +
    ($hospDangerous / max(1, $hospTotal)) * 30
));


// BUILD PAYLOAD & SEND TO PYTHON 
$payload = json_encode([
    'dateFrom'         => $dateFrom,
    'dateTo'           => $dateTo,
    'incidents'        => $incidents,
    'needs'            => $needs,
    'hospitals'        => $hospitals,
    'hospitalTeams'    => $hospitalTeams,
    'demographics'     => $demographics,
    'shelters'         => $shelters,
    'mapRoads'         => $mapRoads,
    'polRoads'         => $polRoads,
    'mapRoutes'        => $mapRoutes,
    'mapZones'         => $mapZones,
    'alerts'           => $alerts,
    'monthlyIncidents' => $monthlyIncidents,
    'monthlyAlerts'    => $monthlyAlerts,
    'regionStats'      => $regionStats,
    'shelterTrend'     => $shelterTrend,
    'severityScore'    => $sevScore,

]);

$tmpIn  = tempnam(sys_get_temp_dir(), 'rp_in_')  . '.json';
$tmpOut = tempnam(sys_get_temp_dir(), 'rp_out_') . '.xlsx';

file_put_contents($tmpIn, $payload);

$pyScript  = __DIR__ . '/ai/generate_report_excel.py';
$pythonExe = 'python3';
$cmd = '"' . $pythonExe . '" "' . $pyScript . '" '
     . escapeshellarg($tmpIn) . ' '
     . escapeshellarg($tmpOut);
exec($cmd . ' 2>&1', $output, $code);

if ($code !== 0 || !file_exists($tmpOut)) {
    http_response_code(500);
    echo "Excel generation failed.<br>Error: " . implode("\n", $output);
    unlink($tmpIn);
    exit;
}

unlink($tmpIn);
$filename = 'Crisis_Report_' . $dateFrom . '_to_' . $dateTo . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($tmpOut));
header('Cache-Control: no-cache');
readfile($tmpOut);
unlink($tmpOut);
exit;