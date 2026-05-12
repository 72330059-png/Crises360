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
                <div class="modern-card ">
                    <h6 class="fw-bold mb-3">Live Map Overview</h6>
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d106063.05364177573!2d35.41695423871216!3d33.88921107567781!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x151f17215882853f%3A0x7fa32e49c8959d2a!2sBeirut!5e0!3m2!1sen!2slb!4v1714400000000!5m2!1sen!2slb"
                        class="db-map-frame"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy">
                    </iframe>
                </div>
            </div>

            <!-- RIGHT SIDE -->
            <div class="col-md-4">
                <!-- <div class="modern-card db-safe-isolation mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold mb-0">Road Conditions</h6>
                        <a href="#" class="small text-decoration-none">View All</a>
                    </div>

                    <div class="condition-row d-flex align-items-center mb-4">
                        <div class="status-icon-box bg-safe">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="condition-text flex-grow-1 ms-3">
                            <span class="d-block fw-bold mb-0">Beirut — Downtown</span>
                            <span class="subtext">Updated 10 min ago</span>
                        </div>
                        <span class="status-label status-safe text-end">Open</span>
                    </div>

                    <div class="condition-row d-flex align-items-center mb-4">
                        <div class="status-icon-box bg-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="condition-text flex-grow-1 ms-3">
                            <span class="d-block fw-bold mb-0">Airport Road</span>
                            <span class="subtext">Updated 15 min ago</span>
                        </div>
                        <span class="status-label status-warning text-end">Heavy Traffic</span>
                    </div>

                    <div class="condition-row d-flex align-items-center">
                        <div class="status-icon-box bg-danger">
                            <i class="bi bi-x-circle"></i>
                        </div>
                        <div class="condition-text flex-grow-1 ms-3">
                            <span class="d-block fw-bold mb-0">Bekaa Highway</span>
                            <span class="subtext">Updated 5 min ago</span>
                        </div>
                        <span class="status-label status-danger text-end">Blocked</span>
                    </div>
                </div>

                <div class="modern-card db-safe-isolation">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold mb-0">Evacuation Routes</h6>
                        <a href="#" class="small text-decoration-none">View All</a>
                    </div>

                    <div class="condition-row d-flex align-items-center mb-4">
                        <div class="status-icon-box bg-safe">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="condition-text flex-grow-1 ms-3">
                            <span class="d-block fw-bold mb-0">Route 1</span>
                            <span class="subtext">Beirut -> Downtown</span>
                        </div>
                        <span class="status-label status-safe text-end">Safe</span>
                    </div>

                    <div class="condition-row d-flex align-items-center mb-4">
                        <div class="status-icon-box bg-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="condition-text flex-grow-1 ms-3">
                            <span class="d-block fw-bold mb-0">Route 2</span>
                            <span class="subtext">Beirut -> Zahle</span>
                        </div>
                        <span class="status-label status-warning text-end">Dangerous</span>
                    </div>

                </div> -->
                <div class="modern-card db-safe-isolation">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold mb-0">Recent Alerts Sent</h6>
                        <a href="#" class="small text-decoration-none">View all</a>
                    </div>

                    <div class="condition-row d-flex align-items-center mb-4">
                        <div class="status-icon-box bg-danger">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div class="condition-text flex-grow-1 ms-3">
                            <span class="d-block fw-bold mb-0">Road blocked on Airport Road</span>
                            <span class="subtext">Target: All Civil Units • 10m ago</span>
                        </div>
                    </div>

                    <div class="condition-row d-flex align-items-center mb-4">
                        <div class="status-icon-box bg-warning">
                            <i class="fa-solid fa-bullhorn"></i>
                        </div>
                        <div class="condition-text flex-grow-1 ms-3">
                            <span class="d-block fw-bold mb-0">Evacuation advised in Dahieh</span>
                            <span class="subtext">Target: Sector 7 Residents • 25m ago</span>
                        </div>
                    </div>

                    <div class="condition-row d-flex align-items-center mb-4">
                        <div class="status-icon-box bg-warning">
                            <i class="fa-solid fa-car-burst"></i>
                        </div>
                        <div class="condition-text flex-grow-1 ms-3">
                            <span class="d-block fw-bold mb-0">Traffic congestion in Jounieh</span>
                            <span class="subtext">Target: Logistics Teams • 40m ago</span>
                        </div>
                    </div>

                    <div class="condition-row d-flex align-items-center">
                        <div class="status-icon-box bg-safe">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div class="condition-text flex-grow-1 ms-3">
                            <span class="d-block fw-bold mb-0">Route 1 safe update</span>
                            <span class="subtext">Target: General Public • 1h ago</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- BOTTOM SECTION -->
        <div class="row g-3 mt-4">

            <!-- TABLE -->
            <div class="col-md">
                <div class="modern-card">
                    <h6 class="fw-bold mb-3">Active Police Operations</h6>

                    <table class="table align-middle">
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
                        </tbody>
                    </table>
                </div>
            </div>

         

        </div>

    </div>

    <?php include('includes/script.php'); ?>

</body>

</html>