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

    <div class="main-content p-4">

        <!-- TOP CARDS -->
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="card stat-card">
                    <h6>Total Patients</h6>
                    <h3>124</h3>
                    <span class="text-success">+18 vs yesterday</span>
                </div>
            </div>

            <div class="col-md-2">
                <div class="card stat-card">
                    <h6>Critical Cases</h6>
                    <h3>18</h3>
                    <span class="text-danger">+4 vs yesterday</span>
                </div>
            </div>

            <div class="col-md-2">
                <div class="card stat-card">
                    <h6>Available Beds</h6>
                    <h3>56 / 120</h3>
                    <span class="text-primary">47% available</span>
                </div>
            </div>

            <div class="col-md-2">
                <div class="card stat-card">
                    <h6>ICU Beds</h6>
                    <h3>12 / 40</h3>
                    <span class="text-warning">30% available</span>
                </div>
            </div>

            <div class="col-md-2">
                <div class="card stat-card">
                    <h6>Staff On Duty</h6>
                    <h3>78</h3>
                </div>
            </div>

            <div class="col-md-2">
                <div class="card stat-card">
                    <h6>Ambulances</h6>
                    <h3>7</h3>
                    <span class="text-success">Active</span>
                </div>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="row">

            <!-- LEFT TABLE -->
            <div class="col-lg-8">
                <div class="card p-3">
                    <div class="d-flex justify-content-between mb-3">
                        <h5>Response Teams</h5>
                        <button class="btn btn-success btn-sm">+ Add Team</button>
                    </div>

                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Status</th>
                                <th>Leader</th>
                                <th>Mission</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>AMB-01</td>
                                <td><span class="badge bg-success">On Mission</span></td>
                                <td>Dr. Hassan</td>
                                <td>Transport to RHUH</td>
                                <td>
                                    <button class="btn btn-sm btn-primary">Edit</button>
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </td>
                            </tr>

                            <tr>
                                <td>AMB-02</td>
                                <td><span class="badge bg-secondary">Available</span></td>
                                <td>Nurse Sara</td>
                                <td>Standby</td>
                                <td>
                                    <button class="btn btn-sm btn-primary">Edit</button>
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </td>
                            </tr>

                            <tr>
                                <td>AMB-03</td>
                                <td><span class="badge bg-warning">Maintenance</span></td>
                                <td>Eng. Mark</td>
                                <td>Garage</td>
                                <td>
                                    <button class="btn btn-sm btn-primary">Edit</button>
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
                <!-- TODAY SUMMARY -->
                <div class="card p-3 mt-4">

                    <h5 class="mb-3">Today's Summary</h5>

                    <div class="row text-center g-3">

                        <div class="col-md-3">
                            <div class="summary-box">
                                <h6>Incoming Patients</h6>
                                <h4>124</h4>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="summary-box">
                                <h6>Critical Cases</h6>
                                <h4>18</h4>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="summary-box">
                                <h6>Discharged</h6>
                                <h4>15</h4>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="summary-box">
                                <h6>Transferred Out</h6>
                                <h4>6</h4>
                            </div>
                        </div>

                    </div>

                    <!-- GENERATE REPORT -->
                    <div class="d-flex justify-content-between align-items-center mt-4 report-box p-3">
                        <div>
                            <h6 class="mb-1">Generate Full Report (Excel / Word)</h6>
                            <small class="text-muted">Includes all data, teams, capacity, and missions</small>
                        </div>

                        <button class="btn btn-success">Generate Report</button>
                    </div>

                </div>
            </div>

            <!-- RIGHT SIDE -->
            <div class="col-lg-4">

                <!-- STATUS -->
                <div class="card p-3 mb-3">
                    <h5>Hospital Status</h5>

                    <p>Infrastructure: <span class="text-warning">Partially Damaged</span></p>
                    <p>Power: <span class="text-danger">Unstable</span></p>
                    <p>Water: <span class="text-success">Available</span></p>

                    <button class="btn btn-outline-success btn-sm w-100">Update Status</button>
                </div>

                <!-- ACTIONS -->
                <div class="card p-3 mb-3">
                    <h5>Quick Actions</h5>

                    <button class="btn btn-danger w-100 mb-2">Send Alert</button>
                    <button class="btn btn-primary w-100 mb-2">Request Transfer</button>
                    <button class="btn btn-success w-100 mb-2">Update Capacity</button>
                    <button class="btn btn-secondary w-100">Generate Report</button>
                </div>

                <!-- TRANSFERS -->
                <div class="card p-3">
                    <h5>Recent Transfers</h5>

                    <p>Tripoli Hospital - 3 Patients <span class="badge bg-warning">Pending</span></p>
                    <p>Saida Hospital - 2 Patients <span class="badge bg-success">Done</span></p>
                </div>

            </div>
        </div>
    </div>
    <?php include('includes/script.php'); ?>
</body>

</html>