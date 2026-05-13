<?php
session_start();
require_once("class/DAL.class.php");

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$dal = new DAL();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Police System</title>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <?php include('includes/header.php'); ?>

    <style>
        body {
            background: #F4F7FE;
            font-family: 'DM Sans', sans-serif;
            color: #1B2559;
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
            padding: 20px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            border: 1px solid #f0f2f5;
            height: 100%;
        }

        .db-icon-box {
            width: 56px;
            height: 56px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .db-content-area {
            display: flex;
            flex-direction: column;
        }

        .db-label {
            font-size: 0.85rem;
            color: #a3aed0;
            font-weight: 500;
            white-space: nowrap;
        }

        .db-main-value {
            font-size: 1.5rem;
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
    </style>
</head>

<body>

    <?php include('includes/sidebar.php'); ?>
    <?php include('includes/nav.php'); ?>

    <div class="main-content">

        <!-- HEADER -->
        <div class="mb-4">
            <h2 class="fw-bold">Police System</h2>
            <p class="text-muted small">Monitor safe areas, road conditions, and operations</p>
        </div>

        <!-- STATS -->
        <div class="row g-3 mb-4">
            <div class="col">
                <div class="db-stat-card">
                    <div class="db-icon-box" style="background: #f0fdf4; color: #16a34a;">
                        <i class="fa-solid fa-tower-broadcast"></i>
                    </div>
                    <div class="db-content-area">
                        <span class="db-label">Active Operations</span>
                        <span class="db-main-value">8</span>
                        <span class="db-subtext db-text-success"><i class="fa-solid fa-arrow-up"></i> 2 new today</span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="db-stat-card">
                    <div class="db-icon-box" style="background: #f4f7fe; color: #4318ff;">
                        <i class="fa-solid fa-car-side"></i>
                    </div>
                    <div class="db-content-area">
                        <span class="db-label">Patrol Units</span>
                        <span class="db-main-value">48</span>
                        <span class="db-subtext">Available: 32</span>
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
                        <span class="db-main-value">12</span>
                        <span class="db-subtext db-text-danger"><i class="fa-solid fa-triangle-exclamation"></i> 3 critical</span>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="db-stat-card">
                    <div class="db-icon-box" style="background: #f0fdf4; color: #05cd99;">
                        <i class="fa-solid fa-shield-heart"></i>
                    </div>
                    <div class="db-content-area">
                        <span class="db-label">Safe Areas</span>
                        <span class="db-main-value">26</span>
                        <span class="db-subtext">All regions</span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="db-stat-card">
                    <div class="db-icon-box" style="background: #f5f3ff; color: #7c3aed;">
                        <i class="fa-solid fa-people-group"></i>
                    </div>
                    <div class="db-content-area">
                        <span class="db-label">Evacuations Today</span>
                        <span class="db-main-value">1,245</span>
                        <span class="db-subtext" style="color: #7c3aed;">+ 18% from yesterday</span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="db-stat-card">
                    <div class="db-icon-box" style="background: #fffaf0; color: #ffb547;">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <div class="db-content-area">
                        <span class="db-label">Alerts Sent</span>
                        <span class="db-main-value">56</span>
                        <span class="db-subtext">Last 24h</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- TOP SECTION -->
        <div class="row g-4">

            <!-- MAP -->
            <div class="col-md-8">
                <div class="modern-card">
                    <h6 class="fw-bold mb-3">Active Police Operations</h6>
                    <div class="table-responsive">
                        <table class="table align-middle" id="policeTable">
                            <thead>
                                <tr>
                                    <th>Operation</th>
                                    <th>Region</th>
                                    <th>Units</th>
                                    <th>Status</th>
                                    <th>Started</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td class="fw-bold">Downtown Security</td>
                                    <td>Beirut</td>
                                    <td>8</td>
                                    <td class="status-warning">In Progress</td>
                                    <td>May 18</td>
                                </tr>

                                <tr>
                                    <td class="fw-bold">Road Clearance</td>
                                    <td>Bekaa</td>
                                    <td>6</td>
                                    <td class="status-safe">Completed</td>
                                    <td>May 18</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Downtown Security</td>
                                    <td>Beirut</td>
                                    <td>8</td>
                                    <td class="status-warning">In Progress</td>
                                    <td>May 18</td>
                                </tr>

                                <tr>
                                    <td class="fw-bold">Road Clearance</td>
                                    <td>Bekaa</td>
                                    <td>6</td>
                                    <td class="status-safe">Completed</td>
                                    <td>May 18</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Downtown Security</td>
                                    <td>Beirut</td>
                                    <td>8</td>
                                    <td class="status-warning">In Progress</td>
                                    <td>May 18</td>
                                </tr>

                                <tr>
                                    <td class="fw-bold">Road Clearance</td>
                                    <td>Bekaa</td>
                                    <td>6</td>
                                    <td class="status-safe">Completed</td>
                                    <td>May 18</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Road Clearance</td>
                                    <td>Bekaa</td>
                                    <td>6</td>
                                    <td class="status-safe">Completed</td>
                                    <td>May 18</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Downtown Security</td>
                                    <td>Beirut</td>
                                    <td>8</td>
                                    <td class="status-warning">In Progress</td>
                                    <td>May 18</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Road Clearance</td>
                                    <td>Bekaa</td>
                                    <td>6</td>
                                    <td class="status-safe">Completed</td>
                                    <td>May 18</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Downtown Security</td>
                                    <td>Beirut</td>
                                    <td>8</td>
                                    <td class="status-warning">In Progress</td>
                                    <td>May 18</td>
                                </tr>

                                <tr>
                                    <td class="fw-bold">Road Clearance</td>
                                    <td>Bekaa</td>
                                    <td>6</td>
                                    <td class="status-safe">Completed</td>
                                    <td>May 18</td>
                                </tr>
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

                        <h6 class="fw-bold mb-0">
                            Recent Alerts Sent
                        </h6>


                    </div>

                    <!-- TABLE -->
                    <div class="table-responsive">

                        <table class="table alerts-table align-middle" id="alertstablep">

                            <thead>
                                <tr>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>

                                <!-- ROW -->
                                <tr>

                                    <td class="alert-icon-td">

                                        <div class="alert-icon danger-alert">
                                            <i class="fa-solid fa-triangle-exclamation"></i>
                                        </div>

                                    </td>

                                    <td>

                                        <div class="alert-title">
                                            Road blocked on Airport Road
                                        </div>

                                        <div class="alert-subtext">
                                            Target: All Civil Units • 10m ago
                                        </div>

                                    </td>

                                </tr>

                                <!-- ROW -->
                                <tr>

                                    <td class="alert-icon-td">

                                        <div class="alert-icon warning-alert">
                                            <i class="fa-solid fa-bullhorn"></i>
                                        </div>

                                    </td>

                                    <td>

                                        <div class="alert-title">
                                            Evacuation advised in Dahieh
                                        </div>

                                        <div class="alert-subtext">
                                            Target: Sector 7 Residents • 25m ago
                                        </div>

                                    </td>

                                </tr>

                                <!-- ROW -->
                                <tr>

                                    <td class="alert-icon-td">

                                        <div class="alert-icon orange-alert">
                                            <i class="fa-solid fa-car-burst"></i>
                                        </div>

                                    </td>

                                    <td>

                                        <div class="alert-title">
                                            Traffic congestion in Jounieh
                                        </div>

                                        <div class="alert-subtext">
                                            Target: Logistics Teams • 40m ago
                                        </div>

                                    </td>

                                </tr>

                                <!-- ROW -->
                                <tr>

                                    <td class="alert-icon-td">

                                        <div class="alert-icon safe-alert">
                                            <i class="fa-solid fa-circle-check"></i>
                                        </div>

                                    </td>

                                    <td>

                                        <div class="alert-title">
                                            Route 1 safe update
                                        </div>

                                        <div class="alert-subtext">
                                            Target: General Public • 1h ago
                                        </div>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>
            </div>

        </div>



    </div>

    <?php include('includes/script.php'); ?>

</body>

</html>