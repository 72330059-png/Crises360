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

        <!-- ===== PAGE HEADER ===== -->


        <!-- ===== STATS CARDS ===== -->
        <div class="row g-3 mb-4">

            <div class="col-md-3">
                <div class="card dashboard-card">
                    <p>Total Alerts</p>
                    <h3>210</h3>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card dashboard-card">
                    <p>Active Alerts</p>
                    <h3 class="text-danger">45</h3>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card dashboard-card">
                    <p>Sent Today</p>
                    <h3 class="text-primary">18</h3>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card dashboard-card">
                    <p>Critical Alerts</p>
                    <h3 class="text-warning">9</h3>
                </div>
            </div>

        </div>

        <!-- ===== FILTERS ===== -->
        <div class="d-flex flex-wrap gap-3 align-items-center mb-3">

            <input type="text" class="form-control filter-control" placeholder="Search alerts">

            <select class="form-select filter-control">
                <option>Type</option>
                <option>Fire</option>
                <option>Flood</option>
                <option>Conflict</option>
            </select>

            <select class="form-select filter-control">
                <option>Target</option>
                <option>Citizens</option>
                <option>Police</option>
                <option>Hospitals</option>
            </select>

            <select class="form-select filter-control">
                <option>Status</option>
                <option>Sent</option>
                <option>Pending</option>
            </select>

            <input type="date" class="form-control filter-control">
            <button class="btn btn-primary px-4">
                <i class="fa fa-bullhorn me-1"></i> Send Alert
            </button>
        </div>

        <!-- ===== TABLE ===== -->
        <div class="card p-3 table-card">

            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Message</th>
                        <th>Type</th>
                        <th>Target</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <tr>
                        <td>#501</td>
                        <td>Evacuate area immediately</td>
                        <td><span class="badge bg-danger">Fire</span></td>
                        <td>Citizens</td>
                        <td><span class="badge bg-success">Sent</span></td>
                        <td>2026-04-28</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-light"><i class="fa fa-eye"></i></button>
                            <button class="btn btn-sm btn-light"><i class="fa fa-edit"></i></button>
                            <button class="btn btn-sm btn-light text-danger"><i class="fa fa-trash"></i></button>
                            <button class="btn btn-sm btn-light text-primary"><i class="fa fa-paper-plane"></i></button>
                        </td>
                    </tr>

                    <tr>
                        <td>#502</td>
                        <td>Road blocked - avoid route</td>
                        <td><span class="badge bg-warning text-dark">Flood</span></td>
                        <td>Police</td>
                        <td><span class="badge bg-secondary">Pending</span></td>
                        <td>2026-04-27</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-light"><i class="fa fa-eye"></i></button>
                            <button class="btn btn-sm btn-light"><i class="fa fa-edit"></i></button>
                            <button class="btn btn-sm btn-light text-danger"><i class="fa fa-trash"></i></button>
                            <button class="btn btn-sm btn-light text-primary"><i class="fa fa-paper-plane"></i></button>
                        </td>
                    </tr>

                </tbody>
            </table>

            <!-- ===== PAGINATION ===== -->
            <div class="d-flex justify-content-between align-items-center mt-3">

                <small class="text-muted">Showing 1–10 of 210</small>

                <nav>
                    <ul class="pagination mb-0">
                        <li class="page-item disabled"><a class="page-link">«</a></li>
                        <li class="page-item active"><a class="page-link">1</a></li>
                        <li class="page-item"><a class="page-link">2</a></li>
                        <li class="page-item"><a class="page-link">3</a></li>
                        <li class="page-item"><a class="page-link">»</a></li>
                    </ul>
                </nav>

            </div>

        </div>

    </div>
    <?php include('includes/script.php'); ?>
</body>

</html>