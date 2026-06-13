<?php
session_start();
require_once("../class/DAL.class.php");
require_once("../class/hospitals.class.php");
require_once("../class/municipality.class.php");

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$dal     = new DAL();
$hospObj = new hospital();
$muniObj = new muni();

$action   = $_GET['action'] ?? '';
$dateFrom = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
$dateTo   = $_GET['to']   ?? date('Y-m-d');

$dateFrom = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom) ? $dateFrom : date('Y-m-d', strtotime('-30 days'));
$dateTo   = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)   ? $dateTo   : date('Y-m-d');

function buildDateAxis(string $from, string $to): array
{
    $dates = [];
    $d = new DateTime($from);
    $end = new DateTime($to);
    while ($d <= $end) {
        $dates[] = $d->format('Y-m-d');
        $d->modify('+1 day');
    }
    return $dates;
}

function buildSeries(array $rawRows, array $dates, string $dateKey = 'd', string $valKey = 'cnt'): array
{
    $map = [];
    foreach ($rawRows as $r) $map[$r[$dateKey]] = (int)$r[$valKey];
    $out = [];
    foreach ($dates as $date) $out[] = $map[$date] ?? 0;
    return $out;
}

switch ($action) {

    //SHELTER 
    case 'shelter_overview':
        $total     = (int)$muniObj->totalCapacity();
        $occupied  = (int)$muniObj->totalOccupied();
        $available = (int)$muniObj->availableCapacity();
        $maint     = max(0, $total - $occupied - $available);
        echo json_encode([
            'status' => 'success',
            'data'   => [
                'total'       => $total,
                'occupied'    => $occupied,
                'available'   => $available,
                'maintenance' => $maint,
            ]
        ]);
        break;

    // HOSPITALS
    case 'hospital_status':
        $hospitals = $hospObj->getAllHospitals();
        $stable = 0;
        $warning = 0;
        $dangerous = 0;
        foreach ($hospitals as $h) {
            switch ($h['hospital_status']) {
                case 'Safe':
                    $stable++;
                    break;
                case 'Warning':
                    $warning++;
                    break;
                case 'Dangerous':
                    $dangerous++;
                    break;
                default:
                    $warning++;
                    break;
            }
        }
        echo json_encode([
            'status' => 'success',
            'data'   => [
                'stable'    => $stable,
                'warning'   => $warning,
                'dangerous' => $dangerous,
                'total'     => count($hospitals),
            ]
        ]);
        break;

    // CASUALTIES 
    case 'casualties':
        $demoData = $dal->getdata("SELECT 
            SUM(male_injured)     AS male_inj,
            SUM(female_injured)   AS fem_inj,
            SUM(children_injured) AS child_inj,
            SUM(male_martyrs)     AS male_mart,
            SUM(female_martyrs)   AS fem_mart,
            SUM(children_martyrs) AS child_mart
            FROM hospital_demographics");
        $demo = $demoData[0] ?? [];
        $totalInjured = (int)($demo['male_inj']  ?? 0) + (int)($demo['fem_inj']  ?? 0) + (int)($demo['child_inj']  ?? 0);
        $totalMartyrs = (int)($demo['male_mart'] ?? 0) + (int)($demo['fem_mart'] ?? 0) + (int)($demo['child_mart'] ?? 0);
        echo json_encode([
            'status' => 'success',
            'data'   => [
                'injured' => $totalInjured,
                'martyrs' => $totalMartyrs,
                'demo' => [
                    'injured' => [
                        'males'    => (int)($demo['male_inj']   ?? 0),
                        'females'  => (int)($demo['fem_inj']    ?? 0),
                        'children' => (int)($demo['child_inj']  ?? 0),
                    ],
                    'martyrs' => [
                        'males'    => (int)($demo['male_mart']  ?? 0),
                        'females'  => (int)($demo['fem_mart']   ?? 0),
                        'children' => (int)($demo['child_mart'] ?? 0),
                    ],
                ],
            ]
        ]);
        break;

    // ROADS 
    case 'roads_status':
        $mapSafe   = (int)($dal->getdata("SELECT COUNT(*) c FROM map_roads WHERE status='open' AND is_active=1")[0]['c'] ?? 0);
        $mapWarn   = (int)($dal->getdata("SELECT COUNT(*) c FROM map_roads WHERE status='warning' AND is_active=1")[0]['c'] ?? 0);
        $mapClosed = (int)($dal->getdata("SELECT COUNT(*) c FROM map_roads WHERE status='closed' AND is_active=1")[0]['c'] ?? 0);
        $polSafe   = (int)($dal->getdata("SELECT COUNT(*) c FROM police_roads WHERE road_type='safe' AND is_active=1")[0]['c'] ?? 0);
        $polBlock  = (int)($dal->getdata("SELECT COUNT(*) c FROM police_roads WHERE road_type='blocked' AND is_active=1")[0]['c'] ?? 0);

        $safe       = $mapSafe + $polSafe;
        $restricted = $mapWarn;
        $closed     = $mapClosed + $polBlock;
        $total      = $safe + $restricted + $closed;
        if ($total === 0) $total = 1;

        echo json_encode([
            'status' => 'success',
            'data'   => [
                'safe'       => $safe,
                'restricted' => $restricted,
                'closed'     => $closed,
                'total'      => $total,
            ]
        ]);
        break;

    // NEEDS FULFILLMENT (date-ranged)
    case 'needs_fulfillment':
        $needsRaw = $dal->getdata(
            "SELECT category, status, COUNT(*) as cnt
             FROM needs
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY category, status
             ORDER BY category ASC",
            [$dateFrom, $dateTo]
        );

        $catMap = [];
        foreach ($needsRaw as $row) {
            $cat = strtolower(trim($row['category']));
            if (!isset($catMap[$cat])) $catMap[$cat] = count($catMap);
        }
        if (empty($catMap)) {
            $catMap = ['food' => 0, 'water' => 1, 'medical' => 2, 'shelter' => 3, 'fuel' => 4, 'other' => 5];
        }

        $categories  = array_keys($catMap);
        $labels      = array_map('ucfirst', $categories);
        $n           = count($categories);
        $fulfilled   = array_fill(0, $n, 0);
        $inProgress  = array_fill(0, $n, 0);
        $notFulfill  = array_fill(0, $n, 0);

        foreach ($needsRaw as $row) {
            $cat = strtolower(trim($row['category']));
            $idx = $catMap[$cat] ?? null;
            if ($idx === null) continue;
            $status = strtolower(trim($row['status']));
            switch ($status) {
                case 'fulfilled':
                    $fulfilled[$idx]  += (int)$row['cnt'];
                    break;
                case 'in_progress':
                    $inProgress[$idx] += (int)$row['cnt'];
                    break;
                default:
                    $notFulfill[$idx] += (int)$row['cnt'];
                    break;
            }
        }

        echo json_encode([
            'status' => 'success',
            'data'   => [
                'labels'       => $labels,
                'fulfilled'    => $fulfilled,
                'inProgress'   => $inProgress,
                'notFulfilled' => $notFulfill,
            ]
        ]);
        break;

    // ALERTS & TEAMS TIME SERIES
    case 'alerts_teams_series':
        $allDates = buildDateAxis($dateFrom, $dateTo);

        $alertsSeries = $dal->getdata(
            "SELECT DATE(created_at) as d, COUNT(*) as cnt
             FROM alerts
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY DATE(created_at)
             ORDER BY d ASC",
            [$dateFrom, $dateTo]
        );

        $teamsSeries = $dal->getdata(
            "SELECT DATE(created_at) as d, COUNT(*) as cnt
             FROM hospital_teams
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY DATE(created_at)
             ORDER BY d ASC",
            [$dateFrom, $dateTo]
        );

        echo json_encode([
            'status' => 'success',
            'data' => [
                'timeLabels' => array_map(fn($d) => date('M j', strtotime($d)), $allDates),
                'rawDates'   => $allDates,
                'alerts'     => buildSeries($alertsSeries, $allDates),
                'teams'      => buildSeries($teamsSeries,  $allDates),
            ]
        ]);
        break;

    // ALERTS BY REGION (for modal) 
    case 'alerts_by_region':
        $alertsByRegion = $dal->getdata(
            "SELECT region, severity, COUNT(*) as cnt
             FROM alerts
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY region, severity
             ORDER BY cnt DESC",
            [$dateFrom, $dateTo]
        );
        echo json_encode(['status' => 'success', 'data' => $alertsByRegion]);
        break;

    // INCIDENTS OVERVIEW + SERIES
    case 'incidents_overview':
        $allDates = buildDateAxis($dateFrom, $dateTo);

        $incDateRow = $dal->getdata(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status != 'Resolved' AND status != 'In Progress' THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN status = 'Resolved'   THEN 1 ELSE 0 END) AS resolved,
                SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) AS investigating
             FROM incidents
             WHERE DATE(reported_at) BETWEEN ? AND ?",
            [$dateFrom, $dateTo]
        );
        $incRow = $incDateRow[0] ?? [];

        $incByDay = $dal->getdata(
            "SELECT DATE(reported_at) as d,
                    SUM(CASE WHEN status != 'Resolved' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'Resolved'  THEN 1 ELSE 0 END) as resolved
             FROM incidents
             WHERE DATE(reported_at) BETWEEN ? AND ?
             GROUP BY DATE(reported_at)
             ORDER BY d ASC",
            [$dateFrom, $dateTo]
        );

        echo json_encode([
            'status' => 'success',
            'data'   => [
                'total'         => (int)($incRow['total']        ?? 0),
                'active'        => (int)($incRow['active']       ?? 0),
                'resolved'      => (int)($incRow['resolved']     ?? 0),
                'investigating' => (int)($incRow['investigating'] ?? 0),
                'timeLabels'    => array_map(fn($d) => date('M j', strtotime($d)), $allDates),
                'byDay'         => buildSeries(array_map(fn($r) => ['d' => $r['d'], 'cnt' => $r['active']],   $incByDay), $allDates),
                'resolvedByDay' => buildSeries(array_map(fn($r) => ['d' => $r['d'], 'cnt' => $r['resolved']], $incByDay), $allDates),
            ]
        ]);
        break;

    // SEVERITY SCORE 
    case 'severity_score':
        $hospitals = $hospObj->getAllHospitals();
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
        $sevClass = $sevScore >= 75 ? 'critical' : ($sevScore >= 50 ? 'high' : ($sevScore >= 25 ? 'medium' : 'low'));

        echo json_encode([
            'status' => 'success',
            'data'   => [
                'score' => $sevScore,
                'class' => $sevClass,
            ]
        ]);
        break;

    // FULL DASHBOARD (everything at once, optional convenience) 
    case 'full_dashboard':
        echo json_encode(['status' => 'error', 'message' => 'Use individual actions or implement aggregation here']);
        break;

    case 'teams_by_date':
        $specificDate = $_GET['date'] ?? $dateFrom;
        $specificDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $specificDate) ? $specificDate : $dateFrom;
        $teamsData = $dal->getdata(
            "SELECT team_name, status, current_location
         FROM hospital_teams
         WHERE DATE(created_at) = ?",
            [$specificDate]
        );
        echo json_encode(['status' => 'success', 'data' => $teamsData]);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
        break;
}
