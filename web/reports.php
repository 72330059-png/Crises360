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
<html>

<head>
    <title>Reports & Statistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php include('includes/header.php'); ?>

    <style>
        body {
           
            font-family: 'DM Sans', sans-serif;
        }

        .main-content {
            padding: 20px 40px;
        }

        .page-header h2 {
            color: #1b2559;
            font-weight: 700;
            margin-bottom: 2px;
        }

        
        .dashboard-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            display: flex;
            align-items: center;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.05);
            height: 100%;
            border: none;
        }

        .card-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .filter-control {
            background: white !important;
            border: 1px solid #E0E5F2;
            border-radius: 12px;
            padding: 12px 15px;
            font-size: 14px;
            color: #1B2559;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.05);
        }

        .modern-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.05);
            border: none;
            height: 100%;
        }

        .table thead th {
            color: #A3AED0;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            border-bottom: 1px solid #F4F7FE;
            padding-bottom: 15px;
            background: white;
        }

        .table tbody td {
            padding: 18px 0;
            color: #1B2559;
            border-bottom: 1px solid #F4F7FE;
            font-size: 14px;
            vertical-align: middle;
        }

        .action-btn {
            background: #f4f7fe;
            color: #4318ff;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            margin-left: 5px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 10px;
            display: inline-block;
        }

        .quick-action-btn {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 14px 20px;
            margin-bottom: 12px;
            border-radius: 14px;
            border: 1px solid #E0E5F2;
            background: white;
            text-decoration: none;
            transition: 0.2s;
            color: #1B2559;
            font-weight: 600;
            font-size: 14px;
        }

        .quick-action-btn:hover {
            background: #F4F7FE;
            transform: translateY(-2px);
        }

        .qa-icon {
            margin-right: 15px;
            font-size: 18px;
            color: #4318ff;
        }

        .btn-export {
            background: #FFF9F2;
            border-color: #FFB547;
            color: #FFB547;
        }

        .filter-row-container {
            background: transparent;
            padding: 15px 20px;
            border-radius: 15px;
            display: flex;
            gap: 15px;
            align-items: center;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.03);
            margin-bottom: 25px;
        }

        .search-wrapper {
            flex: 2;
            position: relative;
        }

        .filter-item {
            flex: 1;
        }

        .filter-control {
            border: 1px solid #E0E5F2;
            border-radius: 12px;
            padding: 10px 15px;
            font-size: 14px;
            width: 100%;
            color: #1B2559;
        }

        .row.g-4 {
            align-items: flex-start;
        }

        .modern-card {
            background: #fff;
            border-radius: 14px;
            padding: 20px;
            border: 1px solid #E9EDF7;
        }

        .title-main {
            color: #1b2559;
            font-weight: 700;
            margin: 0;
        }

        #reportsTable {
            margin-bottom: 0;
        }

        .dataTables_wrapper {
            padding-bottom: 0 !important;
        }

        .dataTables_wrapper .bottom {
            margin-top: 10px;
        }

        .action-btn {
            background: #F4F7FE;
            border: none;
            padding: 6px 10px;
            border-radius: 8px;
            margin-left: 5px;
            cursor: pointer;
        }

        .delete-btn {
            color: #EE5D50;
        }

        .chart-container {
            position: relative;
            height: 180px;
        }

        .chart-center {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -40%);
            text-align: center;
        }

        .chart-number {
            font-size: 22px;
            font-weight: 700;
            color: #1B2559;
        }

        .chart-label {
            font-size: 11px;
            color: #A3AED0;
        }

        .legend-container {
            margin-top: 10px;
        }

        .legend-item {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .legend-dot {
            width: 8px;
            height: 8px;
            display: inline-block;
            border-radius: 50%;
            margin-right: 5px;
        }

        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 10px;
            text-decoration: none;
            font-size: 14px;
        }

        .quick-action-btn.light {
            background: #F4F7FE;
            border: 1px solid #E9EDF7;
            color: #1b2559;
        }

        .quick-action-btn.warning {
            background: #FFF9F2;
            border: 1px solid #FFF2E0;
            color: #FFB547;
        }

        .qa-icon {
            color: #4318FF;
        }
    </style>
</head>

<body>
    <?php include('includes/sidebar.php'); ?>
    <?php include('includes/nav.php'); ?>

    <div class="main-content">
        <div class="page-header mb-4">
            <h2>Reports & Statistics</h2>
            <p class="text-muted small">Generate and view detailed reports and analytics</p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #f4f7fe; color: #4318ff;">
                        <i class="fa-solid fa-chart-simple"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Total Reports</span>
                        <span class="card-value">128</span>
                        <span class="card-subtext">All time</span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #f2faf8; color: #05cd99;">
                        <i class="fa-solid fa-file-circle-check"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Generated Today</span>
                        <span class="card-value">7</span>
                        <span class="card-subtext">New reports</span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #f4f7fe; color: #4318ff;">
                        <i class="fa-solid fa-cloud-arrow-down"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Downloads</span>
                        <span class="card-value">356</span>
                        <span class="card-subtext">This week</span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #fff9f2; color: #ffb547;">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Scheduled</span>
                        <span class="card-value">12</span>
                        <span class="card-subtext">Automated</span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #fff5f5; color: #ee5d50;">
                        <i class="fa-solid fa-server"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Data Sources</span>
                        <span class="card-value">8</span>
                        <span class="card-subtext">Connected</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="filter-row-container">
            <div class="search-wrapper">
                <input type="text" class="form-control filter-control" placeholder="Search reports by name...">
            </div>

            <div class="filter-item">
                <select class="form-select filter-control">
                    <option selected>All Types</option>
                </select>
            </div>

            <div class="filter-item">
                <select class="form-select filter-control">
                    <option selected>All Categories</option>
                </select>
            </div>

            <div class="filter-item" style="max-width: 200px;">
                <input type="text" class="form-control filter-control" placeholder="Date Range" onfocus="(this.type='date')">
            </div>

            <button class="btn btn-add-navy">
                <i class="fa-solid fa-plus me-2"></i> Generate Report
            </button>
        </div>


        <div class="container-fluid p-4">

            <div class="row g-4">

                <div class="col-md-8">
                    <div class="modern-card">

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="title-main">Reports List</h5>
                        </div>

                        <div class="table-responsive">
                            <table id="reportsTable" class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Report Name</th>
                                        <th>Type</th>
                                        <th>Category</th>
                                        <th>Generated At</th>
                                        <th>Size</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>
                                        <td class="fw-bold">Daily Situation Report</td>
                                        <td>Daily</td>
                                        <td>General</td>
                                        <td>May 18, 2025 10:00 AM</td>
                                        <td>2.4 MB</td>
                                        <td class="text-end">
                                            <button class="action-btn"><i class="fa-solid fa-eye fa-xs"></i></button>
                                            <button class="action-btn"><i class="fa-solid fa-pen fa-xs"></i></button>
                                            <button class="action-btn delete-btn"><i class="fa-solid fa-trash fa-xs"></i></button>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="fw-bold">Incident Analysis Report</td>
                                        <td>Analysis</td>
                                        <td>Incidents</td>
                                        <td>May 18, 2025 09:00 AM</td>
                                        <td>3.1 MB</td>
                                        <td class="text-end">
                                            <button class="action-btn"><i class="fa-solid fa-eye fa-xs"></i></button>
                                            <button class="action-btn"><i class="fa-solid fa-pen fa-xs"></i></button>
                                            <button class="action-btn delete-btn"><i class="fa-solid fa-trash fa-xs"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <div class="col-md-4">

                    <div class="modern-card mb-4 card-padding">
                        <h5 class="title-main mb-3">Reports Overview</h5>

                        <div class="chart-container">
                            <canvas id="reportDonut"></canvas>

                            <div class="chart-center">
                                <span class="chart-number">128</span>
                                <span class="chart-label">Total Reports</span>
                            </div>
                        </div>

                        <div class="legend-container">
                            <div class="legend-item">
                                <span><span class="legend-dot" style="background:#4318FF;"></span>Daily</span>
                                <strong>35 (27%)</strong>
                            </div>
                            <div class="legend-item">
                                <span><span class="legend-dot" style="background:#05CD99;"></span>Weekly</span>
                                <strong>28 (22%)</strong>
                            </div>
                            <div class="legend-item">
                                <span><span class="legend-dot" style="background:#FFB547;"></span>Monthly</span>
                                <strong>22 (17%)</strong>
                            </div>
                        </div>
                    </div>

                    <!-- ACTIONS -->
                    <div class="modern-card card-padding">
                        <h5 class="title-main mb-3">Quick Actions</h5>


                        <a href="#" class="quick-action-btn warning">
                            <i class="fa-solid fa-file-export qa-icon"></i>
                            Export Data
                        </a>

                        <a href="#" class="quick-action-btn light">
                            <i class="fa-solid fa-list-check qa-icon"></i>
                            View All Reports
                        </a>
                    </div>

                </div>
            </div>

        </div>


    </div>


    <?php include('includes/script.php'); ?>
</body>

</html>