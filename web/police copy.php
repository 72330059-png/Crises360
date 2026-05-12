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

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
        }

        .stat-label {
            color: #A3AED0;
            font-size: 14px;
        }

        .stat-value {
            font-size: 22px;
            font-weight: 700;
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
            <!-- <div class="col">
                <div class="stat-card">
                    <div class="stat-label">Active Operations</div>
                    <div class="stat-value">8</div>
                </div>
            </div> -->
            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #f4f7fe; color: #4318ff;">
                        <i class="fa-solid fa-house"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Active Operations</span>
                        <span class="card-value">8</span>
                        <!-- <span class="card-subtext"> All Regions</span> -->
                    </div>
                </div>
            </div>


            <div class="col">
                <div class="stat-card">
                    <div class="stat-label">Patrol Units</div>
                    <div class="stat-value">48</div>
                </div>
            </div>
            <div class="col">
                <div class="stat-card">
                    <div class="stat-label">Road Blockages</div>
                    <div class="stat-value">12</div>
                </div>
            </div>
            <div class="col">
                <div class="stat-card">
                    <div class="stat-label">Safe Areas</div>
                    <div class="stat-value">26</div>
                </div>
            </div>
            <div class="col">
                <div class="stat-card">
                    <div class="stat-label">Evacuations</div>
                    <div class="stat-value">1,245</div>
                </div>
            </div>
            <div class="col">
                <div class="stat-card">
                    <div class="stat-label">Alerts Sent</div>
                    <div class="stat-value">56</div>
                </div>
            </div>
        </div>

        <!-- TOP SECTION -->
        <div class="row g-4">

            <!-- MAP -->
            <div class="col-md-8">
                <div class="modern-card">
                    <h6 class="fw-bold mb-3">Live Map Overview</h6>

                    <iframe src="https://maps.google.com/maps?q=Beirut&t=&z=11&ie=UTF8&iwloc=&output=embed"></iframe>

                </div>
            </div>

            <!-- RIGHT SIDE -->
            <div class="col-md-4">

                <!-- ROAD CONDITIONS -->
                <div class="modern-card mb-3">
                    <h6 class="fw-bold mb-3">Road Conditions</h6>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Beirut → Downtown</span>
                        <span class="status-safe">Safe</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Airport Road</span>
                        <span class="status-warning">Heavy Traffic</span>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>Bekaa Highway</span>
                        <span class="status-danger">Blocked</span>
                    </div>
                </div>

                <!-- ROUTES -->
                <div class="modern-card">
                    <h6 class="fw-bold mb-3">Evacuation Routes</h6>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Route 1</span>
                        <span class="status-safe">Safe</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Route 2</span>
                        <span class="status-warning">Traffic</span>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>Route 3</span>
                        <span class="status-danger">Blocked</span>
                    </div>
                </div>

            </div>

        </div>

        <!-- BOTTOM SECTION -->
        <div class="row g-4 mt-2">

            <!-- TABLE -->
            <div class="col-md-8">
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

            <!-- RECENT ALERTS -->
            <div class="col-md-4">
                <div class="modern-card">

                    <div class="d-flex justify-content-between mb-3">
                        <h6 class="fw-bold">Recent Alerts Sent</h6>
                        <a href="#" class="small text-primary">View all</a>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>
                            Road blocked on Airport Road
                            <div class="text-muted small">Beirut</div>
                        </div>
                        <small class="text-muted">10 min</small>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>
                            Evacuation advised in Dahieh
                            <div class="text-muted small">Beirut</div>
                        </div>
                        <small class="text-muted">25 min</small>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>
                            Traffic congestion in Jounieh
                            <div class="text-muted small">Keserwan</div>
                        </div>
                        <small class="text-muted">40 min</small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <div>
                            <i class="fa-solid fa-circle-check text-success me-2"></i>
                            Route 1 safe update
                            <div class="text-muted small">Mount Lebanon</div>
                        </div>
                        <small class="text-muted">1 hour</small>
                    </div>

                </div>
            </div>

        </div>

    </div>

    <?php include('includes/script.php'); ?>

</body>

</html>