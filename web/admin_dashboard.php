<?php
session_start();
require_once("class/DAL.class.php");
require_once("class/incidents.class.php");
require_once("class/alerts.class.php");
require_once("class/hospitals.class.php");
require_once("class/municipality.class.php");
require_once("class/police.class.php");
require('class/users.class.php');

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
$usersObj = new users();
$incident  = new incident();
$alertsObj = new alert();
$hospital  = new hospital();

$teamUsers = $usersObj->getTeamActivity();
$activeIncidents  = $incident->activeIncidents();
$activeAlerts     = $alertsObj->totalAlerts();
$responseTeams    = $hospital->totalTeamsAllHospitals();
$reportsGenerated = 34;

// Chart
$weekData = $incident->incidentsThisWeek();
$dayMap   = [
    'Monday'    => 0,
    'Tuesday'   => 0,
    'Wednesday' => 0,
    'Thursday'  => 0,
    'Friday'    => 0,
    'Saturday'  => 0,
    'Sunday'    => 0
];
foreach ($weekData as $row) {
    if (isset($dayMap[$row['day_name']])) {
        $dayMap[$row['day_name']] = (int)$row['total'];
    }
}
$chartLabels = json_encode(array_keys($dayMap));
$chartValues = json_encode(array_values($dayMap));

$totalThisWeek    = $incident->totalIncidentsThisWeek();
$resolvedThisWeek = $incident->resolvedIncidentsThisWeek();
$pendingThisWeek  = $totalThisWeek - $resolvedThisWeek;
$recentAlerts = $alertsObj->getRecentAlerts(4);

$mun     = new muni();
$policeObj = new police();
$totalHospitals     = $hospital->totalHospitals();
$stableHospitals    = $hospital->availableHospitals();
$hospitalPct        = $totalHospitals > 0 ? round(($stableHospitals / $totalHospitals) * 100) : 0;
$totalCapacity      = $mun->totalCapacity();
$availableCapacity  = $mun->availableCapacity();
$shelterPct         = $totalCapacity > 0 ? round(($availableCapacity / $totalCapacity) * 100) : 0;
$totalUnits     = $policeObj->getTotalUnits();
$availableUnits = $policeObj->countAvailableUnits();
$policePct      = $totalUnits > 0 ? round(($availableUnits / $totalUnits) * 100) : 0;
$activeIncidentsLastWeek = $incident->activeIncidentsLastWeek();
$alertsLastWeek          = $alertsObj->totalAlertsLastWeek();
$teamsLastWeek           = $hospital->totalTeamsLastWeek();
$teamsThisWeek           = $responseTeams;

$incidentChange = $activeIncidentsLastWeek > 0
    ? round((($activeIncidents - $activeIncidentsLastWeek) / $activeIncidentsLastWeek) * 100)
    : 0;

$alertChange = $alertsLastWeek > 0
    ? round((($activeAlerts - $alertsLastWeek) / $alertsLastWeek) * 100)
    : 0;

$teamsChange = $teamsThisWeek - $teamsLastWeek;
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <?php include('includes/header.php'); ?>

    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background-color: #f7f9fc;
            font-family: 'Sora', sans-serif;
        }

        .stat-card {
            background: #fff;
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 18px;
            padding: 22px;
            transition: transform .22s ease, box-shadow .22s ease;
            cursor: default;
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: -25px;
            right: -25px;
            width: 85px;
            height: 85px;
            border-radius: 50%;
            opacity: .11;
            transition: transform .3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.09);
        }

        .stat-card:hover::after {
            transform: scale(1.4);
        }

        .stat-card.danger {
            border-left: 3px solid #dc3545;
        }

        .stat-card.warning {
            border-left: 3px solid #ffc107;
        }

        .stat-card.success {
            border-left: 3px solid #198754;
        }

        .stat-card.primary {
            border-left: 3px solid #0d6efd;
        }

        .stat-card.danger::after {
            background: #dc3545;
        }

        .stat-card.warning::after {
            background: #ffc107;
        }

        .stat-card.success::after {
            background: #198754;
        }

        .stat-card.primary::after {
            background: #0d6efd;
        }

        .stat-icon-wrap {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 14px;
        }

        .stat-card.danger .stat-icon-wrap {
            background: rgba(220, 53, 69, 0.12);
        }

        .stat-card.warning .stat-icon-wrap {
            background: rgba(255, 193, 7, 0.15);
        }

        .stat-card.success .stat-icon-wrap {
            background: rgba(25, 135, 84, 0.11);
        }

        .stat-card.primary .stat-icon-wrap {
            background: rgba(13, 110, 253, 0.10);
        }

        .stat-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
            color: #8a93a6;
            margin-bottom: 4px;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
            letter-spacing: -1.5px;
            line-height: 1;
            margin-bottom: 8px;
            color: #1a1f2e;
            font-variant-numeric: tabular-nums;
        }

        .stat-change {
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .stat-change .lbl {
            color: #8a93a6;
            font-weight: 400;
            margin-left: 2px;
        }

        .dash-card {
            background: #fff;
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 18px;
            overflow: hidden;
        }

        .dash-card-head {
            padding: 18px 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .dash-card-head h6 {
            font-size: 14px;
            font-weight: 700;
            color: #1a1f2e;
            margin: 0;
            letter-spacing: -.2px;
        }

        .dash-card-head a {
            font-size: 12px;
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
            opacity: .8;
            transition: opacity .15s;
        }

        .dash-card-head a:hover {
            opacity: 1;
        }

        .chart-totals {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            margin-top: 12px;
        }

        .chart-total-item {
            padding: 14px 0;
            text-align: center;
        }

        .chart-total-item:not(:last-child) {
            border-right: 1px solid rgba(0, 0, 0, 0.05);
        }

        .chart-total-item .ct-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: #8a93a6;
            margin-bottom: 5px;
        }

        .chart-total-item .ct-value {
            font-size: 24px;
            font-weight: 700;
            color: #1a1f2e;
            letter-spacing: -1px;
        }

        .chart-total-item .ct-change {
            font-size: 11px;
            font-weight: 700;
        }

        .alert-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 22px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
            transition: background .15s;
            cursor: default;
        }

        .alert-row:last-child {
            border-bottom: none;
        }

        .alert-row:hover {
            background: #f7f9fc;
        }

        .alert-row .a-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .alert-row .a-title {
            font-size: 13px;
            font-weight: 600;
            color: #1a1f2e;
            margin-bottom: 2px;
        }

        .alert-row .a-sub {
            font-size: 11px;
            color: #8a93a6;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
            animation: dotpulse 2.2s infinite;
        }

        @keyframes dotpulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.4);
                opacity: .7;
            }
        }

        .res-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .res-row:last-child {
            margin-bottom: 0;
        }

        .res-icon-wrap {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: #eef2ff;
            color: #0d6efd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .res-bar-label {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 600;
            color: #1a1f2e;
            margin-bottom: 5px;
        }

        .res-bar-label small {
            color: #8a93a6;
            font-weight: 400;
        }

        .progress {
            height: 7px;
            background: #eef0f5;
            border-radius: 99px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            border-radius: 99px;
            width: 0%;
            transition: width 1.4s cubic-bezier(.4, 0, .2, 1);
        }

        .progress-bar.bg-success {
            background: linear-gradient(90deg, #10b981, #34d399) !important;
        }

        .progress-bar.bg-warning {
            background: linear-gradient(90deg, #f59e0b, #fbbf24) !important;
        }

        .res-pct {
            font-size: 12px;
            font-weight: 700;
            color: #1a1f2e;
            min-width: 34px;
            text-align: right;
        }

        .team-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 22px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
            transition: background .15s;
            cursor: default;
        }

        .team-row:last-child {
            border-bottom: none;
        }

        .team-row:hover {
            background: #f7f9fc;
        }

        .avatars {
            display: flex;
        }

        .av {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 2px solid #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
        }

        .av:not(:first-child) {
            margin-left: -8px;
        }

        .av-a {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .av-b {
            background: #d1fae5;
            color: #065f46;
        }

        .av-c {
            background: #fef3c7;
            color: #92400e;
        }

        .team-name {
            font-size: 13px;
            font-weight: 600;
            color: #1a1f2e;
            margin-bottom: 2px;
        }

        .team-sub {
            font-size: 11px;
            color: #8a93a6;
        }

        .status-pill {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .4px;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 99px;
        }

        .sp-active {
            background: #d1fae5;
            color: #065f46;
        }

        .sp-standby {
            background: #fef3c7;
            color: #92400e;
        }

        .live-pill {
            display: flex;
            align-items: center;
            gap: 7px;
            background: #fff;
            border: 1px solid rgba(0, 0, 0, 0.07);
            border-radius: 10px;
            padding: 7px 14px;
            font-size: 12px;
            color: #8a93a6;
            font-weight: 500;
        }

        .live-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, .2);
            animation: dotpulse 2s infinite;
        }

        .dash-welcome h4 {
            font-size: 20px;
            font-weight: 700;
            color: #1a1f2e;
            margin-bottom: 3px;
            letter-spacing: -.3px;
        }

        .dash-welcome p {
            font-size: 13px;
            color: #8a93a6;
            margin: 0;
        }

        .fade-up {
            opacity: 0;
            transform: translateY(14px);
            animation: fadeUp .45s ease forwards;
        }

        @keyframes fadeUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-up:nth-child(1) {
            animation-delay: .05s;
        }

        .fade-up:nth-child(2) {
            animation-delay: .12s;
        }

        .fade-up:nth-child(3) {
            animation-delay: .19s;
        }

        .fade-up:nth-child(4) {
            animation-delay: .26s;
        }

        .live-pill {
            font-size: 12px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        @media (max-width: 576px) {
            .live-pill .live-date {
                display: none;
            }

        }

        .dash-welcome h4 {
            font-size: 22px !important;
        }

        @media (max-width: 576px) {
            .d-flex.justify-content-between.align-items-center.mb-4 {
                flex-wrap: wrap;
                gap: 10px;
            }

            .dash-welcome h4 {
                font-size: 18px !important;
            }

            .live-pill {
                font-size: 11px;
                padding: 5px 10px;
            }
        }

        @media (max-width: 767px) {
            .col-md-3 {
                width: 50%;
            }

            .stat-number {
                font-size: 28px;
            }
        }

        @media (max-width: 480px) {
            .col-md-3 {
                width: 100%;
            }
        }

        @media (max-width: 767px) {

            .col-lg-8,
            .col-lg-4 {
                width: 100% !important;
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }

            .col-lg-6 {
                width: 100% !important;
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }
        }

        @media (max-width: 420px) {
            .chart-totals {
                grid-template-columns: 1fr;
            }

            .chart-total-item:not(:last-child) {
                border-right: none;
                border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            }

            .ct-value {
                font-size: 20px;
            }
        }

        @media (max-width: 400px) {
            .res-bar-label {
                flex-direction: column;
                gap: 1px;
            }

            .res-bar-label small {
                align-self: flex-end;
            }
        }

        @media (max-width: 380px) {
            .team-sub {
                display: none;
            }
        }

        .a-title {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 180px;
        }

        @media (max-width: 480px) {
            .a-title {
                max-width: 130px;
            }

            .a-sub {
                font-size: 10px;
            }
        }

        @media (max-width: 576px) {
            .main-content main.p-4 {
                padding: 14px !important;
            }

            .dash-card-head {
                padding: 14px 16px;
            }

            .dash-card-head h6 {
                font-size: 13px;
            }

            .p-4 {
                padding: 16px !important;
            }
        }

        @media (max-width: 576px) {
            .stat-card {
                padding: 16px;
            }

            .stat-icon-wrap {
                width: 38px;
                height: 38px;
                font-size: 17px;
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>

    <?php include('includes/sidebar.php'); ?>
    <?php include('includes/nav.php'); ?>

    <div class="main-content">
        <div class="flex-grow-1 overflow-auto" style="background:#f7f9fc;">
            <main class="p-4">

                <!-- Header Row -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <!-- <div class="dash-welcome">
                        <h4>Welcome back, douaam 👋</h4>
                        <p>Here's what's happening across your operations</p>
                    </div> -->
                    <div class="dash-welcome">
                        <h4 style="font-size:28px; font-weight:700; color:#1a1f2e; letter-spacing:-.5px; margin:0; display:flex; align-items:center; gap:8px;">
                            Command Center
                            <i class="bi bi-arrow-up-right" style="font-size:18px; color:#0d6efd;"></i>
                        </h4>
                    </div>
                    <!-- <div class="live-pill">
                        <span class="live-dot"></span>
                        Live &nbsp;·&nbsp; May 12 – 18, 2025
                    </div> -->
                    <div class="live-pill">
                        <span class="live-dot"></span>
                        Live &nbsp;·&nbsp; <?php
                                            $today = new DateTime();
                                            $dayOfWeek = (int)$today->format('N'); // 1=Mon, 7=Sun
                                            $monday = clone $today;
                                            $monday->modify('-' . ($dayOfWeek - 1) . ' days');
                                            $sunday = clone $monday;
                                            $sunday->modify('+6 days');
                                            echo $monday->format('M j') . ' – ' . $sunday->format('j, Y');
                                            ?>
                    </div>
                </div>

                <!-- Stat Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3 fade-up">
                        <div class="stat-card danger">
                            <div class="stat-icon-wrap bg-danger bg-opacity-10">
                                <i class="bi bi-exclamation-triangle text-danger"></i>
                            </div>
                            <div class="stat-label">Active Incidents</div>
                            <!-- <div class="stat-number" data-target="23" id="cnt1">0</div> -->
                            <div class="stat-number" data-target="<?php echo $activeIncidents; ?>" id="cnt1">0</div>
                            <div class="stat-change <?= $incidentChange >= 0 ? 'text-danger' : 'text-success' ?>">
                                <i class="bi <?= $incidentChange >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short' ?>"></i>
                                <?= abs($incidentChange) ?>%
                                <span class="lbl">from last week</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 fade-up">
                        <div class="stat-card warning">
                            <div class="stat-icon-wrap bg-warning bg-opacity-10">
                                <i class="bi bi-bell text-warning"></i>
                            </div>
                            <div class="stat-label">Active Alerts</div>
                            <!-- <div class="stat-number" data-target="58" id="cnt2">0</div> -->
                            <div class="stat-number" data-target="<?php echo $activeAlerts; ?>" id="cnt2">0</div>

                            <div class="stat-change <?= $alertChange >= 0 ? 'text-warning' : 'text-success' ?>">
                                <i class="bi <?= $alertChange >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short' ?>"></i>
                                <?= abs($alertChange) ?>%
                                <span class="lbl">from last week</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 fade-up">
                        <div class="stat-card success">
                            <div class="stat-icon-wrap bg-success bg-opacity-10">
                                <i class="bi bi-people text-success"></i>
                            </div>
                            <div class="stat-label">Response Teams</div>
                            <!-- <div class="stat-number" data-target="12" id="cnt3">0</div> -->
                            <div class="stat-number" data-target="<?php echo $responseTeams; ?>" id="cnt3">0</div>
                            <div class="stat-change <?= $teamsChange >= 0 ? 'text-success' : 'text-danger' ?>">
                                <i class="bi <?= $teamsChange >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short' ?>"></i>
                                <?= $teamsChange >= 0 ? '+' : '' ?><?= $teamsChange ?>
                                <span class="lbl">teams this week</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 fade-up">
                        <div class="stat-card primary">
                            <div class="stat-icon-wrap bg-primary bg-opacity-10">
                                <i class="bi bi-file-earmark-text text-primary"></i>
                            </div>
                            <div class="stat-label">Reports Generated</div>
                            <!-- <div class="stat-number" data-target="34" id="cnt4">0</div> -->
                            <div class="stat-number" data-target="<?php echo $reportsGenerated; ?>" id="cnt4">0</div>
                            <div class="stat-change text-danger">
                                <i class="bi bi-arrow-down-short"></i> 4%
                                <span class="lbl">from last week</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-lg-8">
                        <div class="dash-card h-100">
                            <div class="dash-card-head">
                                <h6>Incident Overview</h6>
                                <!-- <select class="form-select form-select-sm w-auto border-0 bg-light" style="font-size:12px;border-radius:8px!important">
                                    <option>This Week</option>
                                    <option>Last Month</option>
                                </select> -->
                                <select id="chartRangeSelect" class="form-select form-select-sm w-auto border-0 bg-light"
                                    style="font-size:12px;border-radius:8px!important">
                                    <option value="week">This Week</option>
                                    <option value="month">Last Month</option>
                                </select>
                            </div>
                            <div class="p-3 px-4">
                                <div style="position:relative;width:100%;height:200px;">
                                    <canvas id="incidentChart" role="img" aria-label="Line chart showing daily incident counts Mon through Sun">Daily incidents: Mon 34, Tue 32, Wed 45, Thu 38, Fri 29, Sat 19, Sun 41.</canvas>
                                </div>
                                <div class="chart-totals">
                                    <div class="chart-total-item">
                                        <div class="ct-label">Total</div>
                                        <div class="ct-value" id="ct-total"><?php echo $totalThisWeek; ?></div>
                                        <div class="ct-change text-primary">This week</div>
                                    </div>
                                    <div class="chart-total-item">
                                        <div class="ct-label">Resolved</div>
                                        <div class="ct-value" id="ct-resolved"><?php echo $resolvedThisWeek; ?></div>
                                        <div class="ct-change text-success">↑ closed</div>
                                    </div>
                                    <div class="chart-total-item">
                                        <div class="ct-label">Pending</div>
                                        <div class="ct-value" id="ct-pending"><?php echo $pendingThisWeek; ?></div>
                                        <div class="ct-change text-danger">↓ open</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- <div class="col-lg-4">
                        <div class="dash-card h-100">
                            <div class="dash-card-head">
                                <h6>Recent Alerts</h6>
                                <a href="#">View all</a>
                            </div>
                            <div class="alert-row">
                                <div class="a-icon bg-danger bg-opacity-10">
                                    <i class="bi bi-exclamation-triangle text-danger"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="a-title">Flood warning — Sector 7</div>
                                    <div class="a-sub">High severity · 2 min ago</div>
                                </div>
                                <span class="pulse-dot" style="background:#dc3545;box-shadow:0 0 0 3px rgba(220,53,69,.18)"></span>
                            </div>
                            <div class="alert-row">
                                <div class="a-icon bg-warning bg-opacity-10">
                                    <i class="bi bi-exclamation-triangle text-warning"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="a-title">Fire reported in Downtown</div>
                                    <div class="a-sub">Medium severity · 15 min ago</div>
                                </div>
                                <span class="pulse-dot" style="background:#ffc107;box-shadow:0 0 0 3px rgba(255,193,7,.18)"></span>
                            </div>
                            <div class="alert-row">
                                <div class="a-icon bg-primary bg-opacity-10">
                                    <i class="bi bi-info-circle text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="a-title">Weather update: Heavy rain</div>
                                    <div class="a-sub">Low severity · 1 hr ago</div>
                                </div>
                                <span class="pulse-dot" style="background:#0d6efd;box-shadow:0 0 0 3px rgba(13,110,253,.18)"></span>
                            </div>
                            <div class="alert-row">
                                <div class="a-icon bg-danger bg-opacity-10">
                                    <i class="bi bi-exclamation-triangle text-danger"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="a-title">Road blockage — Highway 3</div>
                                    <div class="a-sub">High severity · 2 hrs ago</div>
                                </div>
                                <span class="pulse-dot" style="background:#dc3545;box-shadow:0 0 0 3px rgba(220,53,69,.18)"></span>
                            </div>
                        </div>
                    </div> -->
                    <div class="col-lg-4">
                        <div class="dash-card h-100">
                            <div class="dash-card-head">
                                <h6>Recent Alerts</h6>
                                <a href="alerts.php">View all</a>
                            </div>

                            <?php if (empty($recentAlerts)): ?>
                                <div class="p-4 text-center text-muted" style="font-size:13px;">
                                    No alerts found
                                </div>
                            <?php else: ?>
                                <?php foreach ($recentAlerts as $alert):

                                    if ($alert['severity'] === 'Critical') {
                                        $iconClass  = 'bi-exclamation-triangle';
                                        $colorHex   = '#dc3545';
                                        $bgClass    = 'bg-danger';
                                        $shadowRgba = 'rgba(220,53,69,.18)';
                                    } elseif ($alert['severity'] === 'Warning') {
                                        $iconClass  = 'bi-exclamation-triangle';
                                        $colorHex   = '#ffc107';
                                        $bgClass    = 'bg-warning';
                                        $shadowRgba = 'rgba(255,193,7,.18)';
                                    } else {
                                        $iconClass  = 'bi-info-circle';
                                        $colorHex   = '#0d6efd';
                                        $bgClass    = 'bg-primary';
                                        $shadowRgba = 'rgba(13,110,253,.18)';
                                    }

                                    $createdAt  = new DateTime($alert['created_at']);
                                    $now        = new DateTime();
                                    $diff       = $now->diff($createdAt);

                                    if ($diff->days >= 1) {
                                        $timeAgo = $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
                                    } elseif ($diff->h >= 1) {
                                        $timeAgo = $diff->h . ' hr' . ($diff->h > 1 ? 's' : '') . ' ago';
                                    } elseif ($diff->i >= 1) {
                                        $timeAgo = $diff->i . ' min ago';
                                    } else {
                                        $timeAgo = 'Just now';
                                    }
                                ?>
                                    <div class="alert-row">
                                        <div class="a-icon <?= $bgClass ?> bg-opacity-10">
                                            <i class="bi <?= $iconClass ?>" style="color:<?= $colorHex ?>"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="a-title"><?= htmlspecialchars($alert['alert_message']) ?></div>
                                            <div class="a-sub">
                                                <?= $alert['severity'] ?> · <?= $timeAgo ?>
                                            </div>
                                        </div>
                                        <span class="pulse-dot"
                                            style="background:<?= $colorHex ?>;
                                 box-shadow:0 0 0 3px <?= $shadowRgba ?>">
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="dash-card">
                            <div class="dash-card-head">
                                <h6>Resource Status</h6>
                                <a href="hospitals.php">View all</a>
                            </div>
                            <div class="p-4">

                                <!-- HOSPITALS -->
                                <div class="res-row">
                                    <div class="res-icon-wrap">
                                        <i class="bi bi-hospital"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="res-bar-label">
                                            <span>Hospitals</span>
                                            <small><?= $stableHospitals ?> / <?= $totalHospitals ?> stable</small>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" data-w="<?= $hospitalPct ?>"></div>
                                        </div>
                                    </div>
                                    <span class="res-pct"><?= $hospitalPct ?>%</span>
                                </div>

                                <!-- SHELTERS -->
                                <div class="res-row">
                                    <div class="res-icon-wrap">
                                        <i class="bi bi-house-door"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="res-bar-label">
                                            <span>Shelters</span>
                                            <small><?= $availableCapacity ?> / <?= $totalCapacity ?> spots free</small>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar <?= $shelterPct < 30 ? 'bg-warning' : 'bg-success' ?>"
                                                data-w="<?= $shelterPct ?>"></div>
                                        </div>
                                    </div>
                                    <span class="res-pct"><?= $shelterPct ?>%</span>
                                </div>

                                <!-- POLICE -->
                                <div class="res-row">
                                    <div class="res-icon-wrap">
                                        <i class="bi bi-shield-check"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="res-bar-label">
                                            <span>Police Units</span>
                                            <small><?= $availableUnits ?> / <?= $totalUnits ?> available</small>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar <?= $policePct < 30 ? 'bg-warning' : 'bg-success' ?>"
                                                data-w="<?= $policePct ?>"></div>
                                        </div>
                                    </div>
                                    <span class="res-pct"><?= $policePct ?>%</span>
                                </div>

                            </div>
                        </div>
                    </div>

                    <?php
                    function time_ago($datetime)
                    {
                        $diff = time() - strtotime($datetime);
                        if ($diff < 60)     return 'Just now';
                        if ($diff < 3600)   return floor($diff / 60) . ' min ago';
                        if ($diff < 86400)  return floor($diff / 3600) . ' hr ago';
                        return floor($diff / 86400) . ' days ago';
                    }
                    ?>

                    <div class="col-lg-6">
                        <div class="dash-card">
                            <div class="dash-card-head">
                                <h6>Team Activity</h6>
                                <a href="users.php">View all</a>
                            </div>

                            <?php foreach ($teamUsers as $user):
                                $words = explode(' ', trim($user['name']));
                                $initials = strtoupper(substr($words[0], 0, 1));
                                if (isset($words[1])) $initials .= strtoupper(substr($words[1], 0, 1));

                                $isOnline = $user['ustatus'] === 'online';
                                $pillClass = $isOnline ? 'sp-active' : 'sp-standby';
                                $pillText  = $isOnline ? 'Active' : 'Offline';

                                $avClass = $user['role'] === 'admin' ? 'av-a' : 'av-b';

                                $lastActivity = $user['last_activity']
                                    ? time_ago($user['last_activity'])
                                    : 'Never logged in';
                            ?>
                                <div class="team-row">
                                    <div class="avatars">
                                        <div class="av <?= $avClass ?>"><?= $initials ?></div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="team-name"><?= htmlspecialchars($user['name']) ?></div>
                                        <div class="team-sub">
                                            <?= htmlspecialchars($user['role']) ?> · <?= $lastActivity ?>
                                        </div>
                                    </div>
                                    <span class="status-pill <?= $pillClass ?>"><?= $pillText ?></span>
                                </div>
                            <?php endforeach; ?>

                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <?php include('includes/script.php'); ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
    <script>
        window.addEventListener('load', function() {

            document.querySelectorAll('.stat-number[data-target]').forEach(function(el) {
                var target = +el.dataset.target;
                var start = null;
                var dur = 1100;

                function step(ts) {
                    if (!start) start = ts;
                    var p = Math.min((ts - start) / dur, 1);
                    var ease = 1 - Math.pow(1 - p, 3);
                    el.textContent = Math.round(ease * target);
                    if (p < 1) requestAnimationFrame(step);
                }
                requestAnimationFrame(step);
            });

            setTimeout(function() {
                document.querySelectorAll('.progress-bar[data-w]').forEach(function(el) {
                    el.style.width = el.dataset.w + '%';
                });
            }, 200);

            var canvas = document.getElementById('incidentChart');
            if (!canvas) return;
            var ctx = canvas.getContext('2d');

            var grad = ctx.createLinearGradient(0, 0, 0, 180);
            grad.addColorStop(0, 'rgba(13,110,253,0.15)');
            grad.addColorStop(1, 'rgba(13,110,253,0)');

            var chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo $chartLabels; ?>,
                    datasets: [{
                        label: 'Incidents',
                        data: <?php echo $chartValues; ?>,
                        borderColor: '#0d6efd',
                        borderWidth: 2.5,
                        pointBackgroundColor: '#0d6efd',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        backgroundColor: grad,
                        tension: 0.42
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#1a1f2e',
                            titleColor: '#8a93a6',
                            bodyColor: '#fff',
                            bodyFont: {
                                weight: '600'
                            },
                            padding: 10,
                            cornerRadius: 10,
                            displayColors: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: 'rgba(0,0,0,0.04)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#8a93a6',
                                font: {
                                    size: 11
                                },
                                padding: 6
                            },
                            border: {
                                display: false
                            }
                        },
                        y: {
                            grid: {
                                color: 'rgba(0,0,0,0.04)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#8a93a6',
                                font: {
                                    size: 11
                                },
                                padding: 6,
                                stepSize: 1
                            },
                            border: {
                                display: false
                            },
                            min: 0
                        }
                    }
                }
            });

            document.getElementById('chartRangeSelect').addEventListener('change', function() {

                var range = this.value;

                fetch('actions/chart_incidents.php?range=' + range)
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(data) {

                        chart.data.labels = data.labels;
                        chart.data.datasets[0].data = data.values;
                        chart.update();

                        document.getElementById('ct-total').textContent = data.total;
                        document.getElementById('ct-resolved').textContent = data.resolved;
                        document.getElementById('ct-pending').textContent = data.pending;
                    });
            });
        });
    </script>
</body>

</html>