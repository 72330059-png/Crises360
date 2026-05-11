<?php
session_start();
require_once("class/DAL.class.php");

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$dal = new DAL();

// Example hospital (later from DB)
$hospital_id = $_GET['id'] ?? 1;
$hospital_name = "Rafic Hariri University Hospital";
?>

<!DOCTYPE html>
<html>

<head>
    <title>Hospital Teams</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <?php include('includes/header.php'); ?>

    <style>
        body {
            background: #f4f7fe;
            font-family: 'DM Sans', sans-serif;
        }

        .main-content {
            padding: 20px 40px;
        }

        .modern-card {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid #E9EDF7;
        }

        .title-main {
            font-weight: 700;
            color: #1b2559;
        }

        /* Stats cards */
        .dashboard-card {
            background: white;
            border-radius: 16px;
            padding: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        /* Table */
        .table thead th {
            color: #A3AED0;
            font-size: 12px;
            text-transform: uppercase;
        }

        .table tbody td {
            font-size: 14px;
            color: #1B2559;
        }

        /* Status */
        .status-available {
            color: #05CD99;
            font-weight: 600;
        }

        .status-busy {
            color: #EE5D50;
            font-weight: 600;
        }

        .status-mission {
            color: #FFB547;
            font-weight: 600;
        }

        /* Buttons */
        .action-btn {
            background: #F4F7FE;
            border: none;
            padding: 6px 10px;
            border-radius: 8px;
            margin-left: 5px;
        }

        .btn-add {
            background: #4318FF;
            color: white;
        }

        .back-link {
            text-decoration: none;
            font-size: 14px;
            color: #4318FF;
        }
    </style>
</head>

<body>

    <!-- SIDEBAR + NAV -->
    <?php include('includes/sidebar.php'); ?>
    <?php include('includes/nav.php'); ?>

    <div class="main-content">

        <!-- BACK -->
        <a href="hospitals.php" class="back-link mb-2 d-inline-block">
            ← Back to Hospitals
        </a>

        <!-- HEADER -->
        <h3 class="title-main mb-1"><?= $hospital_name ?> - Teams</h3>
        <p class="text-muted small mb-4">Manage response teams for this hospital</p>

        <!-- STATS -->
        <div class="row mb-4">
            <div class="col">
                <div class="dashboard-card">Total Teams <h5>4</h5>
                </div>
            </div>
            <div class="col">
                <div class="dashboard-card">Available <h5 class="status-available">1</h5>
                </div>
            </div>
            <div class="col">
                <div class="dashboard-card">On Mission <h5 class="status-mission">1</h5>
                </div>
            </div>
            <div class="col">
                <div class="dashboard-card">Busy <h5 class="status-busy">2</h5>
                </div>
            </div>
            <div class="col">
                <div class="dashboard-card">Avg Response <h5>18 min</h5>
                </div>
            </div>
        </div>

        <!-- TABLE -->
        <div class="modern-card">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="title-main">Teams List</h5>

                <button class="btn btn-add btn-sm">
                    <i class="fa fa-plus"></i> Add Team
                </button>
            </div>

            <table id="teamsTable" class="table align-middle">

                <thead>
                    <tr>
                        <th>Team Name</th>
                        <th>Leader</th>
                        <th>Specialization</th>
                        <th>Status</th>
                        <th>Availability</th>
                        <th>Members</th>
                        <th>Last Update</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td class="fw-bold">Medical Response Team</td>
                        <td>Dr. Hassan Khaled</td>
                        <td>Medical Support</td>
                        <td class="status-available">Available</td>
                        <td class="status-available">Ready</td>
                        <td>8</td>
                        <td>May 18, 2025 11:10 AM</td>
                        <td class="text-end">
                            <button class="action-btn"><i class="fa fa-eye"></i></button>
                            <button class="action-btn"><i class="fa fa-pen"></i></button>
                            <button class="action-btn"><i class="fa fa-trash text-danger"></i></button>
                        </td>
                    </tr>

                    <tr>
                        <td class="fw-bold">Emergency Transport Team</td>
                        <td>Dr. Maya Fares</td>
                        <td>Transport</td>
                        <td class="status-mission">On Mission</td>
                        <td class="status-mission">On Mission</td>
                        <td>6</td>
                        <td>May 18, 2025 11:05 AM</td>
                        <td class="text-end">
                            <button class="action-btn"><i class="fa fa-eye"></i></button>
                            <button class="action-btn"><i class="fa fa-pen"></i></button>
                            <button class="action-btn"><i class="fa fa-trash text-danger"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>

    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {

            $('#teamsTable').DataTable({
                pageLength: 5,
                dom: 'rt<"bottom"ip>', // ❌ removes "Show entries"
                language: {
                    paginate: {
                        previous: "<",
                        next: ">"
                    }
                }
            });

        });
    </script>

    <?php include('includes/script.php'); ?>

</body>

</html>