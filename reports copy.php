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
    <?php include('includes/header.php'); ?>

    <style>

        .main-content {
            padding: 20px 40px;
        }

        /* 1. FIX SPACING: Tighten the gap between title and cards */
        .page-header {
            margin-bottom: 20px;
        }

        .page-header h2 {
            margin-bottom: 2px;
            color: #1b2559;
            font-weight: 700;
        }

        /* 2. CARD DESIGN: Matching your Incident style exactly */
        .dashboard-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            display: flex;
            align-items: center;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.05);
            height: 100%;
            /* Ensures all cards are same height */
        }

        .card-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .card-title {
            color: #A3AED0;
            font-size: 14px;
            font-weight: 500;
            display: block;
        }

        .card-value {
            color: #1B2559;
            font-size: 24px;
            font-weight: 700;
            display: block;
        }

        .card-subtext {
            color: #A3AED0;
            font-size: 12px;
        }

        /* 3. FILTER BAR: Longer inputs to fill space */
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

        /* Makes search longer */
        .filter-item {
            flex: 1;
        }

        /* Makes selects longer */

        .filter-control {
            border: 1px solid #E0E5F2;
            border-radius: 12px;
            padding: 10px 15px;
            font-size: 14px;
            width: 100%;
            color: #1B2559;
        }

        .btn-add-navy {
            background-color: #111c44;
            color: white;
            border-radius: 12px;
            padding: 10px 25px;
            font-weight: 600;
            white-space: nowrap;
            border: none;
        }

        /* 4. TABLE AREA */
        .table-container {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>
    <?php include('includes/sidebar.php'); ?>
    <?php include('includes/nav.php'); ?>

    <div class="main-content">
        <div class="page-header">
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

        <div class="table-container shadow-sm">
            <h5 style="color: #1b2559; font-weight: 700;" class="mb-4">Recent Reports List</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th style="color: #A3AED0;">REPORT NAME</th>
                            <th style="color: #A3AED0;">TYPE</th>
                            <th style="color: #A3AED0;">CATEGORY</th>
                            <th style="color: #A3AED0;">GENERATED</th>
                            <th style="color: #A3AED0;">SIZE</th>
                            <th style="color: #A3AED0;" class="text-end">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-weight:700;">Daily Situation Report</td>
                            <td>Daily</td>
                            <td>General</td>
                            <td>May 18, 2025</td>
                            <td>2.4 MB</td>
                            <td class="text-end">
                                <i class="fa fa-eye text-muted me-3"></i>
                                <i class="fa fa-download text-muted me-3"></i>
                                <i class="fa fa-trash text-danger"></i>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        
    </div>

    <?php include('includes/script.php'); ?>
</body>

</html>