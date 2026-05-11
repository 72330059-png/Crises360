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
    <title>Needs & Requests</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

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
            padding: 25px;
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

        .filter-control {
            border-radius: 12px;
            border: 1px solid #E9EDF7;
            height: 40px;
            font-size: 14px;
        }

        .status-open {
            color: #05CD99 !important;
            font-weight: 700;
        }

        .status-progress {
            color: #FFB547 !important;
            font-weight: 700;
        }

        .status-closed {
            color: #EE5D50 !important;
            font-weight: 700;
        }

        .priority-high {
            color: #EE5D50 !important;
            font-weight: 700;
        }

        .priority-medium {
            color: #FFB547 !important;
            font-weight: 700;
        }

        .priority-low {
            color: #05CD99 !important;
            font-weight: 700;
        }

        .action-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            border: none;
            background: #F4F7FE;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
            position: relative;
        }

        .action-approve {
            color: #05CD99;
        }

        .action-reject {
            color: #EE5D50;
        }

        .action-icon:hover {
            transform: translateY(-2px);
            background: #E9EDF7;
        }

        .action-icon::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 120%;
            left: 50%;
            transform: translateX(-50%);
            background: #1B2559;
            color: white;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: 0.2s;
        }

        .action-icon:hover::after {
            opacity: 1;
        }

        .table thead th {
            color: #A3AED0;
            font-size: 12px;
        }

        .stat-icon-circle {
            width: 45px;
            height: 45px;
            background-color: #f0fdf4;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #05cd99;
            font-size: 1.2rem;
            border: 1px solid #e1f9e6;
        }


        .priority-high {
            background: #FFF1F0;
            color: #EE5D50;
        }

        .priority-medium {
            background: #FFF7E6;
            color: #FFB547;
        }

        .priority-low {
            background: #E6F9F2;
            color: #05CD99;
        }



        .status-open {
            background: #E6F9F2;
            color: #05CD99;
        }

        .status-progress {
            background: #FFF7E6;
            color: #FFB547;
        }

        .status-closed {
            background: #FFF1F0;
            color: #EE5D50;
        }
    </style>
</head>

<body>

    <?php include('includes/sidebar.php'); ?>
    <?php include('includes/nav.php'); ?>

    <div class="main-content">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold">Needs & Requests</h2>
                <p class="text-muted small">Track municipality resource requests</p>
            </div>
        </div>

        <!-- FILTERS -->
        <div class="d-flex gap-2 mb-4">
            <input type="text" class="form-control filter-control" placeholder="Search needs...">

            <select class="form-select filter-control">
                <option>All Categories</option>
                <option>Fuel</option>
                <option>Food</option>
                <option>Medical</option>
            </select>

            <select class="form-select filter-control">
                <option>All Priorities</option>
                <option>High</option>
                <option>Medium</option>
                <option>Low</option>
            </select>

            <select class="form-select filter-control">
                <option>This Week</option>
            </select>
        </div>

        <!-- STATS -->
        <div class="row g-3 mb-4">
            <div class="col">
                <div class="stat-card">
                    <div class="stat-label">Total Requests</div>
                    <div class="stat-value">67</div>
                </div>
            </div>

            <div class="col">
                <div class="stat-card">
                    <div class="stat-label">Municipalities</div>
                    <div class="stat-value">28</div>
                </div>
            </div>

            <div class="col">
                <div class="stat-card">
                    <div class="stat-label">Active Requests</div>
                    <div class="stat-value">52</div>
                </div>
            </div>

            <div class="col">
                <div class="stat-card d-flex align-items-center justify-content-between p-3">
                    <div>
                        <div class="stat-label mb-1" style="color: #a3aed0; font-size: 0.9rem;">Fulfilled</div>
                        <div class="stat-value fw-bold" style="font-size: 1.5rem; color: #1b2559;">15</div>
                    </div>

                    <div class="stat-icon-circle">
                        <i class="fa-solid fa-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLE -->
        <div class="modern-card">
            <h5 class="fw-bold mb-4">All Needs</h5>

            <table id="needsTable" class="table align-middle">
                <thead>
                    <tr>
                        <th>Need</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Municipality</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td class="fw-bold">Fuel</td>
                        <td>Fuel</td>
                        <td class="priority-high">High</td>
                        <td>Tyre</td>
                        <td>5000 L</td>
                        <td class="status-open">Open</td>
                        <td>May 18</td>
                        <td class="text-end">
                            <!-- Fulfill -->
                            <button class="action-icon action-approve" data-tooltip="Fulfill Request">
                                <i class="fa-solid fa-check"></i>
                            </button>

                            <!-- Cannot Fulfill -->
                            <button class="action-icon action-reject" data-tooltip="Cannot Fulfill">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </td>

                    </tr>

                    <tr>
                        <td class="fw-bold">Medical Supplies</td>
                        <td>Medical</td>
                        <td class="priority-medium">Medium</td>
                        <td>Saida</td>
                        <td>200 Kits</td>
                        <td class="status-progress">In Progress</td>
                        <td>May 17</td>
                        <td class="text-end">
                            <!-- Fulfill -->
                            <button class="action-icon action-approve" data-tooltip="Fulfill Request">
                                <i class="fa-solid fa-check"></i>
                            </button>

                            <!-- Cannot Fulfill -->
                            <button class="action-icon action-reject" data-tooltip="Cannot Fulfill">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $('#needsTable').DataTable({
            pageLength: 7,
            dom: 'rt<"d-flex justify-content-between"ip>',
            language: {
                info: "Showing _START_ to _END_ of _TOTAL_ results",
                paginate: {
                    previous: "<",
                    next: ">"
                }
            }
        });
    </script>

    <?php include('includes/script.php'); ?>

</body>

</html>