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
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include('includes/header.php'); ?>
    <style>

    </style>

</head>

<body>

    <!-- SIDEBAR -->
    <?php include('includes/sidebar.php'); ?>
    <?php include('includes/nav.php'); ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 style="color: #1b2559; font-weight: 700;">Incidents Management</h2>
                <p class="text-muted small">Monitor, track and manage all incidents in real-time</p>
            </div>
        </div>

        <div class="row g-3 mb-4">

            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #f4f7fe; color: #4318ff;">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Total Incidents</span>
                        <span class="card-value">124</span>
                        <span class="card-subtext">All time</span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #fff5f5; color: #ee5d50;">
                        <i class="fa-solid fa-circle-exclamation"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Active Incidents</span>
                        <span class="card-value" style="color: #ee5d50;">38</span>
                        <span class="card-subtext">Currently active</span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #fff9f2; color: #ffb547;">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">In Progress</span>
                        <span class="card-value">34</span>
                        <span class="card-subtext">Under response</span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #f2faf8; color: #05cd99;">
                        <i class="fa-solid fa-square-check"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Resolved</span>
                        <span class="card-value">70</span>
                        <span class="card-subtext">Successfully resolved</span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #fff5f5; color: #ee5d50;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Critical</span>
                        <span class="card-value">16</span>
                        <span class="card-subtext">High priority</span>
                    </div>
                </div>
            </div>

        </div>

        <div class="d-flex align-items-center mb-4">
            <div class="filter-row-container">

                <div class="search-container">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" class="form-control filter-control" placeholder="Search incidents...">
                </div>

                <div class="filter-group-item">
                    <select class="form-select filter-control">
                        <option selected>All Types</option>
                    </select>
                </div>

                <div class="filter-group-item">
                    <select class="form-select filter-control">
                        <option selected>All Regions</option>
                    </select>
                </div>

                <div class="filter-group-item">
                    <select class="form-select filter-control">
                        <option selected>All Statuses</option>
                    </select>
                </div>

                <div class="filter-group-item position-relative">
                    <input type="text" class="form-control filter-control" placeholder="From - To" onfocus="(this.type='date')">
                    <i class="fa-regular fa-calendar position-absolute" style="right:12px; top:12px; color:#a3adc2; pointer-events:none;"></i>
                </div>

                <button class="btn btn-add-navy">
                    <i class="fa-solid fa-plus"></i> Add Incident
                </button>

            </div>
        </div>

        <div class="table-container shadow-sm p-4 bg-white rounded-4">
            <h5 style="color: #1b2559; font-weight: 700;" class="mb-4">Recent Incidents</h5>
            <div class="table-responsive">
                <table class="table align-middle" id="myIncidentTable">
                    <thead>
                        <tr>
                            <th>Incident Name</th>
                            <th>Location</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Reported At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-weight:700;">Fire in warehouse</td>
                            <td>Beirut</td>
                            <td class="status-text text-high">High</td>
                            <td class="status-text text-in-progress">In Progress</td>
                            <td>May 18, 2025 10:15 AM</td>
                            <td>
                                <i class="fa fa-eye text-muted me-2"></i>
                                <i class="fa fa-edit text-muted me-2"></i>
                                <i class="fa fa-trash text-danger"></i>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight:700;">Power outage</td>
                            <td>Tripoli</td>
                            <td class="status-text text-medium">Medium</td>
                            <td class="status-text text-investigating">Investigating</td>
                            <td>May 18, 2025 09:30 AM</td>
                            <td>
                                <i class="fa fa-eye text-muted me-2"></i>
                                <i class="fa fa-edit text-muted me-2"></i>
                                <i class="fa fa-trash text-danger"></i>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight:700;">Water leakage</td>
                            <td>Sidon</td>
                            <td class="status-text text-low">Low</td>
                            <td class="status-text text-resolved">Resolved</td>
                            <td>May 17, 2025 04:45 PM</td>
                            <td>
                                <i class="fa fa-eye text-muted me-2"></i>
                                <i class="fa fa-edit text-muted me-2"></i>
                                <i class="fa fa-trash text-danger"></i>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight:700;">Building collapse</td>
                            <td>Beirut</td>
                            <td class="status-text text-high">High</td>
                            <td class="status-text text-investigating">Investigating</td>
                            <td>May 17, 2025 11:20 AM</td>
                            <td>
                                <i class="fa fa-eye text-muted me-2"></i>
                                <i class="fa fa-edit text-muted me-2"></i>
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