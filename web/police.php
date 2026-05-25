<?php
session_start();
require_once("class/police.class.php");
require_once("class/incidents.class.php");

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
$police = new police();
$incidentObj = new incident();

$blockedRoads = $police->getBlockedRoads();
$totalUnits = $police->getTotalUnits();
$safeRoads      = $police->getSafeRoadsCount();
$evacRouteCount = $police->getEvacRoutesCount();
$recentUpdates  = $police->getRecentPoliceUpdates();
$unitsOnMission = $police->getUnitsOnMission();
$policeUnits = $police->getPoliceUnits();
$missions = $police->getPoliceMissions();
$units = $police->getAvailableUnits();
$activeIncidents = $incidentObj->getAllIncidents();
$activeIncidents = array_filter($activeIncidents, function ($i) {
    return $i['status'] !== 'Resolved';
});
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Police System</title>
    <?php include('includes/header.php'); ?>
    <style>
        body {
            background: #F4F7FE;
            font-family: 'DM Sans', sans-serif;
            color: #1B2559;
        }

        .btn-here {
            background: #F4F7FE;
            font-family: 'DM Sans', sans-serif;
            color: #4f69e8;
        }

        .btn-here:hover {
            background: #4f69e8;
            font-family: 'DM Sans', sans-serif;
            color: #ced4f1;
        }

        .main-content {
            padding: 30px 40px;
            margin-left: 250px;
        }

        .modern-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
        }

        .status-safe {
            color: #05CD99;
            font-weight: 700;
        }

        .status-warning {
            color: #FFB547;
            font-weight: 700;
        }

        .status-danger {
            color: #EE5D50;
            font-weight: 700;
        }

        .table thead th {
            color: #A3AED0;
            font-size: 12px;
        }

        #alertstablep thead {
            display: none;
        }

        iframe {
            width: 100%;
            height: 350px;
            border-radius: 15px;
            border: 0;
        }

        .db-stat-card {
            background: #fff;
            padding: 14px 16px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid #f0f2f5;
            height: 100%;
        }

        .db-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .db-content-area {
            display: flex;
            flex-direction: column;
        }

        .db-label {
            font-size: 0.78rem;
            color: #a3aed0;
            font-weight: 500;
            white-space: nowrap;
        }

        .db-main-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1b2559;
            line-height: 1.2;
        }

        .db-subtext {
            font-size: 0.75rem;
            color: #a3aed0;
            margin-top: 4px;
            white-space: nowrap;
        }

        .db-text-success {
            color: #05cd99 !important;
        }

        .db-text-danger {
            color: #ee5d50 !important;
        }

        .db-safe-isolation .modern-card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #edf2f7;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .db-safe-isolation .condition-row {
            position: relative;
            padding-bottom: 20px;
            border-bottom: 1px solid #f1f5f9;
        }

        .db-safe-isolation .modern-card .condition-row:last-of-type {
            border-bottom: none;
            padding-bottom: 0;
        }

        .db-safe-isolation .status-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .db-safe-isolation .condition-text span.fw-bold {
            color: #1a202c;
        }

        .db-safe-isolation .subtext {
            font-size: 0.75rem;
            color: #a3aed0;
        }

        .db-safe-isolation .status-label {
            font-size: 0.9rem;
            font-weight: 600;
            width: 100px;
        }


        .bg-safe {
            background-color: #f0fdf4 !important;
            color: #05cd99 !important;
        }

        .status-safe {
            color: #05cd99 !important;
        }

        .bg-warning {
            background-color: #fff8eb !important;
            color: #f97316 !important;
        }

        .status-warning {
            color: #f97316 !important;
        }

        .bg-danger {
            background-color: #fff5f5 !important;
            color: #ee5d50 !important;
        }

        .status-danger {
            color: #ee5d50 !important;
        }

        .db-map-frame {
            width: 100%;
            height: 315px;
            border-radius: 12px;
        }

        .modern-card.h-100 {
            display: flex;
            flex-direction: column;
        }

        .bg-purple {
            background-color: #f5f3ff !important;
            color: #7c3aed !important;
        }

        .db-safe-isolation .condition-row {
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 15px;
        }

        .db-safe-isolation .condition-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        /* CARD */
        .alerts-card {
            background: #fff;
            border-radius: 28px;
            padding: 28px;
            /* min-height: 460px; */
        }

        .updates-feed {
            margin-top: 10px;
        }

        /* TABLE */
        .alerts-table {
            margin-bottom: 0;
            border-collapse: separate;

        }

        /* REMOVE BOOTSTRAP */
        .alerts-table tr,
        .alerts-table td {
            border: none !important;
            background: transparent !important;
        }


        /* ROW */
        .alerts-table tbody tr {
            transition: 0.2s;
        }

        /* .alerts-table tbody tr:hover {
            transform: translateX(3px);
        } */

        /* ICON TD */
        .alert-icon-td {
            width: 80px;
            padding-right: 0 !important;
        }

        /* ICON */
        .alert-icon {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        /* COLORS */
        .danger-alert {
            background: #fff1f2;
            color: #ef4444;
        }

        .warning-alert {
            background: #fff7ed;
            color: #f97316;
        }

        .orange-alert {
            background: #fff7ed;
            color: #ea580c;
        }

        .safe-alert {
            background: #ecfdf5;
            color: #10b981;
        }

        /* TEXT */
        .alert-title {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .alert-subtext {
            font-size: 14px;
            color: #94a3b8;
        }

        /* PAGINATION */
        .alerts-pagination {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }

        .alerts-pagination button {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #64748b;
        }

        .active-page {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: #eff6ff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        #alertstablep {
            width: 100% !important;
        }

        #alertstablep td {
            padding-top: 16px !important;
            padding-bottom: 16px !important;
            vertical-align: middle;
        }

        /* FEED */
        .updates-feed {
            display: flex;
            flex-direction: column;
            gap: 22px;
            position: relative;
        }

        /* ITEM */
        .update-item {
            display: flex;
            gap: 16px;
            position: relative;
        }

        /* LINE */
        .update-line {
            position: absolute;
            left: 28px;
            top: 60px;
            width: 2px;
            height: 100%;
            background: #edf2f7;
        }

        .update-item:last-child .update-line {
            display: none;
        }

        /* ICON */
        .alert-icon {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
            z-index: 2;
            position: relative;
        }

        /* CONTENT */
        .update-content {
            flex: 1;
            padding-top: 4px;
        }

        .alert-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .alert-subtext {
            font-size: 13px;
            color: #94a3b8;
        }

        /* TIME */
        .update-time {
            font-size: 12px;
            color: #94a3b8;
            font-weight: 600;
        }

        /* DATE */
        .update-date {
            margin-top: 6px;
            font-size: 12px;
            color: #c0c8db;
        }

        .updates-wrapper::-webkit-scrollbar {
            width: 6px;
        }

        .updates-wrapper::-webkit-scrollbar-thumb {
            background: #dbe4f0;
            border-radius: 20px;
        }

        .updates-wrapper::-webkit-scrollbar-track {
            background: transparent;
        }
    </style>
</head>
<div class="modal fade" id="addUnitModal" tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 rounded-4">

            <div class="modal-header border-0">

                <h5 class="modal-title fw-bold">
                    Add Police Unit
                </h5>

                <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <form id="addUnitForm">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Police Name
                            </label>

                            <input type="text"
                                name="organization_name"
                                class="form-control"
                                required>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Region
                            </label>

                            <input type="text"
                                name="location"
                                class="form-control"
                                required>

                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Email
                            </label>

                            <input type="email"
                                name="email"
                                class="form-control"
                                required>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Password
                            </label>

                            <input type="password"
                                name="password"
                                class="form-control"
                                required>

                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Callsign
                            </label>

                            <input type="text"
                                name="callsign"
                                class="form-control"
                                required>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Unit Type
                            </label>

                            <select name="unit_type"
                                class="form-select"
                                required>

                                <option value="">
                                    Select Type
                                </option>

                                <option value="patrol">
                                    Patrol
                                </option>

                                <option value="swat">
                                    SWAT
                                </option>

                                <option value="traffic">
                                    Traffic
                                </option>

                            </select>

                        </div>

                    </div>

                    <div class="text-end">

                        <button type="submit"
                            class="btn btn-primary px-4">

                            Add Unit

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<div class="modal fade" id="addMissionModal" tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 rounded-4">

            <div class="modal-header border-0">

                <h5 class="modal-title fw-bold">
                    Add Mission
                </h5>

                <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <form id="addMissionForm">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Mission Title
                            </label>

                            <input type="text"
                                name="title"
                                class="form-control"
                                required>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Priority
                            </label>

                            <select name="priority"
                                class="form-select"
                                required>

                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>

                            </select>

                        </div>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Description
                        </label>

                        <textarea
                            name="description"
                            class="form-control"
                            rows="3"
                            required></textarea>

                    </div>
                    <div class="mb-3">
                        <label class="form-label">Related Incident</label>
                        <select name="incident_id" class="form-select">
                            <option value="0">— No specific incident —</option>
                            <?php foreach ($activeIncidents as $inc): ?>
                                <option value="<?= $inc['id']; ?>">
                                    🚨 <?= htmlspecialchars($inc['incident_name']); ?>
                                    — <?= htmlspecialchars($inc['location']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">

                        <label class="form-label">
                            Assign Unit
                        </label>

                        <select name="units[]"
                            class="form-select"
                            multiple
                            size="5"
                            required>

                            <option value="">
                                Select Unit
                            </option>

                            <?php foreach ($units as $u): ?>

                                <option value="<?= $u['unit_id']; ?>">

                                    <?= $u['callsign']; ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="text-end">

                        <button type="submit"
                            class="btn btn-primary px-4">

                            Add Mission

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<body>

    <?php include('includes/sidebar.php'); ?>
    <?php include('includes/nav.php'); ?>

    <div class="main-content">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold">Police System</h2>
                <p class="text-muted small">Monitor safe areas, road conditions, and operations</p>
            </div>
            <!-- FILTERS -->
            <div class="d-flex gap-2 mb-4">

                <!-- SEARCH -->
                <input type="text"
                    id="policeSearch"
                    class="form-control"
                    placeholder="Search units"
                    style="max-width:250px; border-radius:12px;">

                <!-- REGION FILTER -->
                <select id="regionFilter" class="form-select" style="max-width:220px; border-radius:12px;">
                    <option value="">All Regions</option>
                    <?php
                    $regions = [];
                    foreach ($policeUnits as $unit) {
                        if (!in_array($unit['location'], $regions)) {
                            $regions[] = $unit['location'];
                            echo "<option value='{$unit['location']}'>{$unit['location']}</option>";
                        }
                    }
                    ?>
                </select>

                <select id="typeFilter" class="form-select" style="max-width:220px; border-radius:12px;">
                    <option value="">All Unit Types</option>
                    <?php
                    $types = [];
                    foreach ($policeUnits as $unit) {
                        if (!in_array($unit['unit_type'], $types)) {

                            $types[] = $unit['unit_type'];
                            echo "<option value='{$unit['unit_type']}'>{$unit['unit_type']}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
        </div>

        <!-- STATS -->
        <div class="row g-3 mb-4">
            <div class="col">
                <div class="db-stat-card">
                    <div class="db-icon-box" style="background: #f0fdf4; color: #16a34a;">
                        <i class="fa-solid fa-tower-broadcast"></i>
                    </div>
                    <div class="db-content-area">
                        <span class="db-label">Total Units</span>
                        <span class="db-main-value"><?= $totalUnits ?></span>
                        <!-- <span class="db-subtext db-text-success"><i class="fa-solid fa-arrow-up"></i> 2 new today</span> -->
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="db-stat-card">
                    <div class="db-icon-box" style="background: #fff5f5; color: #ee5d50;">
                        <i class="fa-solid fa-road-barrier"></i>
                    </div>
                    <div class="db-content-area">
                        <span class="db-label">Road Blockages</span>
                        <span class="db-main-value"><?= $blockedRoads ?></span>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="db-stat-card">
                    <div class="db-icon-box" style="background: #f0fdf4; color: #05cd99;">
                        <i class="fa-solid fa-shield-heart"></i>
                    </div>
                    <div class="db-content-area">
                        <span class="db-label">Safe Roads</span>
                        <span class="db-main-value"><?= $safeRoads ?></span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="db-stat-card">
                    <div class="db-icon-box" style="background: #f5f3ff; color: #7c3aed;">
                        <i class="fa-solid fa-people-group"></i>
                    </div>

                    <div class="db-content-area">
                        <span class="db-label">Units On Mission</span>

                        <span class="db-main-value">
                            <?= $unitsOnMission ?>
                        </span>

                    </div>
                </div>
            </div>

            <div class="col">
                <div class="db-stat-card">
                    <div class="db-icon-box" style="background: #fffaf0; color: #ffb547;">
                        <i class="fa-solid fa-route"></i>
                    </div>
                    <div class="db-content-area">
                        <span class="db-label">Evacuation Routes</span>
                        <span class="db-main-value"><?= $evacRouteCount ?></span>
                    </div>
                </div>
            </div>

        </div>

        <!-- TOP SECTION -->
        <div class="row g-4">

            <div class="col-md-8">
                <div class="modern-card">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold mb-3">Active Police Operations</h6>

                        <button class="btn btn-sm btn-here rounded-pill px-3"
                            style="font-size: 12px;"
                            data-bs-toggle="modal"
                            data-bs-target="#addUnitModal">

                            <i class="fa-solid fa-plus me-1"></i> New Unit

                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle" id="policeTable">
                            <thead>
                                <tr>
                                    <th>Police Name</th>
                                    <th>Region</th>
                                    <th>callsign</th>
                                    <th>Unit type</th>
                                    <!-- <th>mission</th> -->
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php foreach ($policeUnits as $unit): ?>

                                    <?php
                                    $statusClass = '';

                                    if ($unit['status'] == 'available') {
                                        $statusClass = 'status-safe';
                                    } elseif ($unit['status'] == 'on_mission') {
                                        $statusClass = 'status-warning';
                                    }
                                    ?>

                                    <tr>

                                        <!-- Police Name -->
                                        <td class="fw-bold police_name">
                                            <?= $unit['organization_name']; ?>
                                        </td>

                                        <!-- Region -->
                                        <td class="police_location">
                                            <?= $unit['location']; ?>
                                        </td>

                                        <!-- Callsign -->
                                        <td class="callsign">
                                            <span class="badge bg-light text-dark">
                                                <?= $unit['callsign']; ?>
                                            </span>
                                        </td>

                                        <!-- Unit Type -->
                                        <td class="unit_type">
                                            <?= $unit['unit_type']; ?>
                                        </td>

                                        <!-- Mission -->
                                        <!-- <td class="unit_mission">
                                            <?= $unit['mission_title'] ?? 'No Mission'; ?>
                                        </td> -->

                                        <!-- Status -->
                                        <td class="unit_status">
                                            <span class="<?= $statusClass; ?>"><?= $unit['status'] ?>
                                            </span>
                                        </td>

                                        <!-- Actions -->
                                        <td class="text-center unit_action">
                                            <!-- <i class="fa fa-edit text-muted me-2 editBtn"
                                                style="cursor:pointer;">
                                            </i> -->
                                            <!-- action-btn.btn-view -->
                                            <button class="action-btn btn-view editpol" data-unitid="<?php echo $unit['unit_id']; ?>" data-orgid="<?php echo $unit['organization_id']; ?>">
                                                <i class="fa fa-edit" style="cursor:pointer;"></i>
                                            </button>

                                            <button class="action-btn btn-delete dltunit" data-id="<?php echo $unit['organization_id']; ?>">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- RIGHT SIDE -->
            <div class="col-md-4">
                <div class="modern-card alerts-card">
                    <!-- HEADER -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold mb-0">Recent Field Updates</h6>
                        <a href="maps.php" style="font-size:13px;font-weight:600;color:#1d6ef5;text-decoration:none;display:flex;align-items:center;gap:4px;">
                            View All <i class="fa-solid fa-arrow-right" style="font-size:11px;"></i>
                        </a>
                    </div>
                    <div class="updates-feed">

                        <?php foreach ($recentUpdates as $update): ?>

                            <?php
                            $iconClass = $update['update_type'] === 'road'
                                ? ($update['severity'] === 'blocked'
                                    ? 'danger-alert'
                                    : ($update['severity'] === 'warning'
                                        ? 'warning-alert'
                                        : 'safe-alert'))
                                : 'safe-alert';

                            $icon = $update['update_type'] === 'route'
                                ? 'fa-route'
                                : 'fa-road';
                            ?>

                            <div class="update-item">

                                <div class="update-line"></div>

                                <div class="alert-icon <?= $iconClass ?>">
                                    <i class="fa-solid <?= $icon ?>"></i>
                                </div>

                                <div class="update-content">

                                    <div class="d-flex justify-content-between align-items-start">

                                        <div>
                                            <div class="alert-title">
                                                <?= $update['title'] ?>
                                            </div>

                                            <div class="alert-subtext">
                                                <?= $update['organization_name'] ?>
                                            </div>
                                        </div>

                                        <span class="update-time">
                                            <?= date('H:i', strtotime($update['created_at'])) ?>
                                        </span>

                                    </div>

                                    <div class="update-date">
                                        <?= date('d M Y', strtotime($update['created_at'])) ?>
                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-2">
            <div class="col-12">
                <div class="modern-card">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

                        <div>
                            <h6 class="fw-bold mb-0">Active Mission Tasking</h6>
                        </div>

                        <div class="d-flex align-items-center gap-2">

                            <!-- Search -->
                            <div id="missionSearchBox"></div>

                            <!-- Button -->
                            <button class="btn btn-sm btn-here rounded-pill px-3"
                                style="font-size: 12px;" data-bs-toggle="modal" data-bs-target="#addMissionModal">
                                <i class="fa-solid fa-plus me-1"></i> New Mission
                            </button>

                        </div>

                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle" id="missionTable">
                            <thead>
                                <tr>
                                    <th>Mission Name</th>
                                    <th>Incident</th>
                                    <th>Priority</th>
                                    <th>Assigned Units</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php foreach ($missions as $mission): ?>
                                    <?php
                                    $priorityClass = '';
                                    $statusClass = '';

                                    if ($mission['priority'] == 'High') {
                                        $priorityClass = 'bg-danger';
                                    } elseif ($mission['priority'] == 'Medium') {
                                        $priorityClass = 'bg-warning text-dark';
                                    } else {
                                        $priorityClass = 'bg-success';
                                    }

                                    if ($mission['status'] == 'active') {
                                        $statusClass = 'status-warning';
                                    } elseif ($mission['status'] == 'completed') {
                                        $statusClass = 'status-safe';
                                    } else {
                                        $statusClass = 'status-danger';
                                    }

                                    ?>
                                    <tr>
                                        <!-- Mission -->
                                        <td class="fw-bold text-dark mission_title">
                                            <?= $mission['title']; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($mission['incident_name'])): ?>
                                                <span style="font-size:12px;font-weight:600;color:#e53935;">
                                                    🚨 <?= htmlspecialchars($mission['incident_name']); ?>
                                                </span>
                                                <div style="font-size:11px;color:#94a3b8;">
                                                    <?= htmlspecialchars($mission['incident_location'] ?? ''); ?>
                                                </div>
                                            <?php else: ?>
                                                <span style="font-size:12px;color:#94a3b8;">General</span>
                                            <?php endif; ?>
                                        </td>
                                        <!-- Priority -->
                                        <td class="mission_priority">
                                            <span class="badge rounded-pill <?= $priorityClass; ?>">
                                                <?= ucfirst($mission['priority']); ?>
                                            </span>
                                        </td>
                                        <!-- Units -->
                                        <td class="mission_units">
                                            <?php if ($mission['assigned_units']) : ?>
                                                <span class="badge bg-purple">
                                                    <?= $mission['assigned_units']; ?>
                                                </span>

                                            <?php else: ?>
                                                <span class="text-muted small">
                                                    No Units
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Description -->
                                        <td class="text-muted small mission_description">
                                            <?= $mission['description']; ?>
                                        </td>

                                        <!-- Status -->
                                        <td class="mission_status">
                                            <span class="<?= $statusClass; ?>">
                                                <?= $mission['status']; ?>
                                            </span>
                                        </td>

                                        <td class="text-center mission_action">

                                            <i class="fa fa-edit text-muted me-2 editMissionBtn"
                                                style="cursor:pointer;"
                                                data-id="<?= $mission['mission_id']; ?>"
                                                data-incident="<?= $mission['incident_id'] ?? 0; ?>">
                                            </i>

                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <?php include('includes/script.php'); ?>
    <script>
        $(document).ready(function() {

            var table = $('#policeTable').DataTable({

                pageLength: 6,
                order: [],
                dom: 'rt<"d-flex justify-content-between"ip>',

                language: {

                    info: "Showing _START_ to _END_ of _TOTAL_ results",

                    paginate: {
                        previous: "<",
                        next: ">"
                    }
                }
            });

            // SEARCH
            $('#policeSearch').on('keyup', function() {

                table.search(this.value).draw();

            });

            // REGION FILTER
            $('#regionFilter').on('change', function() {

                table.column(1).search(this.value).draw();

            });

            // UNIT TYPE FILTER
            $('#typeFilter').on('change', function() {

                table.column(3).search(this.value).draw();

            });
            // Enable tooltips
            var tooltipEls = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipEls.forEach(function(el) {
                new bootstrap.Tooltip(el);
            });
        });

        $(document).ready(function() {

            let missionTable = $('#missionTable').DataTable({
                pageLength: 4,
                searching: true,
                lengthChange: false,
                info: false,
                pagingType: "simple_numbers",
                autoWidth: false,
                order: [],
                dom: 'frtip',

                language: {
                    search: "",
                    searchPlaceholder: "Search missions...",
                    paginate: {
                        next: '<i class="fa-solid fa-chevron-right"></i>',
                        previous: '<i class="fa-solid fa-chevron-left"></i>'
                    }
                }
            });

            // move search input beside title/button
            $('#missionTable_filter').appendTo('#missionSearchBox');

        });

        $('#addUnitForm').on('submit', function(e) {

            e.preventDefault();

            $.ajax({

                url: 'actions/add_unit.php',

                type: 'POST',

                data: $(this).serialize(),

                dataType: 'json',

                success: function(response) {

                    if (response.status == 'success') {

                        Swal.fire({

                            icon: 'success',

                            title: 'Success',

                            text: response.message,

                            timer: 2000,

                            showConfirmButton: false

                        });

                        $('#addUnitModal').modal('hide');

                        $('#addUnitForm')[0].reset();

                        setTimeout(function() {

                            location.reload();

                        }, 1500);

                    } else {

                        Swal.fire({

                            icon: 'error',

                            title: 'Error',

                            text: response.message

                        });

                    }

                }

            });

        });

        $(document).on('click', '.dltunit', function() {

            let id = $(this).data('id');

            Swal.fire({
                title: 'Delete Unit?',
                text: "This action cannot be undone",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Delete'
            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({
                        url: 'actions/delete_unit.php',
                        type: 'POST',
                        data: {
                            id: id
                        },
                        dataType: 'json',

                        success: function(response) {

                            if (response.status === 'success') {

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                $('.dltunit[data-id="' + id + '"]').closest('tr').fadeOut();

                            } else {

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message
                                });
                            }
                        }
                    });

                }

            });

        });

        $(document).on('click', '.editpol', function() {
            let unitId = $(this).data('unitid');
            let orgId = $(this).data('orgid');
            let row = $(this).closest('tr');
            let policeName = row.find('.police_name').text().trim();
            let location = row.find('.police_location').text().trim();
            let callsign = row.find('.callsign').text().trim();
            let type = row.find('.unit_type').text().trim();
            // let mission = row.find('.unit_mission').text().trim();
            let status = row.find('.unit_status').text().trim();
            // let status = row.find('.unit-action').text().trim();

            row.find('.police_name').html(`<input type="text" class="form-control edit-pol-name" value="${policeName}"> `);
            row.find('.police_location').html(`<input type="text" class="form-control edit-location" value="${location}">`);
            row.find('.callsign').html(`<input type="text" class="form-control edit-callsign" value="${callsign}">`);

            row.find('.unit_type').html(`<select class="form-select edit-type">
            <option ${type == 'patrol' ? 'selected' : ''}>patrol</option>
            <option ${type == 'swat' ? 'selected' : ''}>swat</option>
            <option ${type == 'traffic' ? 'selected' : ''}>traffic</option>
            <option ${type == 'investigation' ? 'selected' : ''}>investigation</option>
            </select> `);

            // row.find('.unit_mission').html(`<input type="text" class="form-control edit-mission" value="${mission}">`);

            row.find('.unit_status').html(`<select class="form-select edit-status">
            <option ${status == 'available' ? 'selected' : ''}>available</option>
            <option ${status == 'on_mission' ? 'selected' : ''}>on_mission</option>
            <option ${status == 'off_duty' ? 'selected' : ''}>off_duty</option>
            </select> `);

            row.find('td:last').html(`
            <button class="btn btn-success btn-sm saveBtnpol" data-unitid="${unitId}" data-orgid="${orgId}"> Save </button>
            <button class="btn btn-secondary btn-sm cancelBtnpol" > Cancel </button>`);
        });

        $(document).on('click', '.saveBtnpol', function() {
            let unitId = $(this).data('unitid');
            let orgId = $(this).data('orgid');
            let row = $(this).closest('tr');
            let id = row.find('.cancelBtpol').data('id');
            let polName = row.find('.edit-pol-name').val();
            let inlocation = row.find('.edit-location').val();
            let incallsign = row.find('.edit-callsign').val();
            let type = row.find('.edit-type').val();
            // let mission = row.find('.edit-mission').val();
            let status = row.find('.edit-status').val();
            $.ajax({
                url: 'actions/update_police.php',
                type: 'POST',
                data: {
                    unit_id: unitId,
                    org_id: orgId,
                    pol_name: polName,
                    location: inlocation,
                    callsign: incallsign,
                    type: type,
                    // mission: mission,
                    status: status
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        window.location.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                }

            });

        });

        $(document).on('click', '.cancelBtnpol', function() {
            location.reload();
        });

        $('#addMissionForm').on('submit', function(e) {

            e.preventDefault();

            $.ajax({

                url: 'actions/add_mission.php',

                type: 'POST',

                data: $(this).serialize(),

                dataType: 'json',

                success: function(response) {

                    if (response.status == 'success') {

                        Swal.fire({

                            icon: 'success',

                            title: 'Success',

                            text: response.message,

                            timer: 1500,

                            showConfirmButton: false

                        });

                        $('#addMissionModal').modal('hide');

                        $('#addMissionForm')[0].reset();

                        setTimeout(function() {

                            location.reload();

                        }, 1200);

                    } else {

                        Swal.fire({

                            icon: 'error',

                            title: 'Error',

                            text: response.message

                        });
                    }
                }
            });
        });

        $(document).on('click', '.editMissionBtn', function() {
            let missionId = $(this).data('id');
            let row = $(this).closest('tr');
            let title = row.find('.mission_title').text().trim();
            let priority = row.find('.mission_priority').text().trim().toLowerCase();
            let description = row.find('.mission_description').text().trim();
            let status = row.find('.mission_status').text().trim().toLowerCase();
            let assignedUnits = row.find('.mission_units span').text().trim();
            assignedUnits = assignedUnits ? assignedUnits.split(',').map(unit => unit.trim()) : [];
            if (status == 'in progress') {
                status = 'active';
            }

            row.find('.mission_title').html(`<input type="text"class="form-control edit-mission-title" value="${title}">`);


            let incidentId = $(this).data('incident');

            // Add this after row.find('.mission_title').html(...)
            row.find('td:nth-child(2)').html(`
            <select class="form-select edit-incident-id">
            <option value="0">— No incident —</option>
           <?php foreach ($activeIncidents as $inc): ?>
           <option value="<?= $inc['id']; ?>" 
           ${incidentId == <?= $inc['id']; ?> ? 'selected' : ''}>
           🚨 <?= htmlspecialchars($inc['incident_name']); ?>
           </option>
           <?php endforeach; ?>
           </select>`);

            row.find('.mission_priority').html(`
            <select class="form-select edit-mission-priority">
            <option value="low" ${priority == 'low' ? 'selected' : ''}> Low </option>
            <option value="medium" ${priority == 'medium' ? 'selected' : ''}> Medium </option>
            <option value="high"  ${priority == 'high' ? 'selected' : ''}>  High  </option>
            </select>
             `);

            row.find('.mission_units').html(`
            <select class="form-select edit-units" multiple size="5">
            <?php foreach ($units as $u): ?>
            <option value="<?= $u['unit_id']; ?>">
            <?= $u['callsign']; ?>
            </option>
            <?php endforeach; ?>
            </select>`);

            row.find('.edit-units option').each(function() {
                let text = $(this).text().trim();
                if (assignedUnits.includes(text)) {
                    $(this).prop('selected', true);
                }
            });

            row.find('.mission_description').html(`<textarea class="form-control edit-mission-description">${description}</textarea> `);

            row.find('.mission_status').html(`
            <select class="form-select edit-mission-status">
            <option value="active" ${status == 'active' ? 'selected' : ''}> Active </option>
            <option value="completed" ${status == 'completed' ? 'selected' : ''}> Completed </option>
            <option value="late" ${status == 'late' ? 'selected' : ''}> Late </option>
            <option value="sent " ${status == 'sent' ? 'selected' : ''}> Sent </option>
            <option value="rejected " ${status == 'rejected' ? 'selected' : ''}> Rejected </option>
            </select> `);

            row.find('.mission_action').html(`<button class="btn btn-success btn-sm saveMissionBtn" data-id="${missionId}">
            Save
            </button>

            <button class="btn btn-secondary btn-sm cancelMissionBtn">
            Cancel
            </button> `);
        });

        $(document).on('click', '.saveMissionBtn', function() {
            let missionId = $(this).data('id');
            let row = $(this).closest('tr');
            let title = row.find('.edit-mission-title').val();
            let priority = row.find('.edit-mission-priority').val();
            let units = row.find('.edit-units').val();
            let description = row.find('.edit-mission-description').val();
            let status = row.find('.edit-mission-status').val();
            let incident_id = row.find('.edit-incident-id').val() || 0;

            $.ajax({
                url: 'actions/update_mission.php',
                type: 'POST',
                data: {
                    mission_id: missionId,
                    title: title,
                    incident_id: incident_id,
                    priority: priority,
                    description: description,
                    status: status,
                    units: units
                },
                dataType: 'json',
                traditional: true,
                success: function(response) {
                    if (response.status == 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false

                        });
                        setTimeout(function() {
                            location.reload();
                        }, 1200);

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                }
            });
        });
        $(document).on('click', '.cancelMissionBtn', function() {

            location.reload();

        });
    </script>

</body>

</html>