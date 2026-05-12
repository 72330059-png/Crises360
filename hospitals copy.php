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
    <title>Hospitals Management | Admin</title>
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
            /* Adjust based on your sidebar width */
        }

        /* Stats Cards */
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            border: none;
            box-shadow: none;
            height: 100%;
        }

        .stat-label {
            color: #A3AED0;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .stat-value {
            color: #1B2559;
            font-size: 24px;
            font-weight: 700;
        }

        .filter-select {
            border-radius: 12px;
            border: 1px solid #E9EDF7;
            padding: 8px 12px;
            /* Smaller padding for better height */
            font-size: 14px;
            color: #1B2559;
            font-weight: 500;
            background-color: white;
            height: 40px;
            /* Fixed height to match button */
            min-width: 140px;
        }

        .btn-add-hospital {
            background: #05CD99;
            /* Precise Model Green */
            color: white;
            border-radius: 12px;
            padding: 0 20px;
            font-weight: 700;
            border: none;
            height: 40px;
            width: 400px;
            /* Matching height */
            display: flex;
            align-items: center;
            transition: 0.3s;
        }

        .btn-add-hospital:hover {
            background: #04b88a;
            color: white;
        }

        .status-high,
        .status-capacity {
            color: #EE5D50 !important;
            font-weight: 700;
        }

        .status-medium {
            color: #FFB547 !important;
            font-weight: 700;
        }

        .status-low {
            color: #05CD99 !important;
            font-weight: 700;
        }

        /* Table Design */
        .modern-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            border: none;
        }

        .table thead th {
            color: #A3AED0;
            font-size: 12px;
            font-weight: 500;
            border-bottom: 1px solid #E9EDF7;
            padding-bottom: 15px;
            text-transform: none;
        }

        .table tbody td {
            padding: 15px 0;
            color: #1B2559;
            font-size: 14px;
            border-bottom: 1px solid #F4F7FE;
        }

        /* Status Badges based on Model */
        .status-high {
            color: #EE5D50;
            font-weight: 700;
        }

        /* Red */
        .status-capacity {
            color: #EE5D50;
            font-weight: 700;
        }

        /* Red */
        .status-medium {
            color: #FFB547;
            font-weight: 700;
        }

        /* Orange */
        .status-low {
            color: #05CD99;
            font-weight: 700;
        }

        /* Green */

        /* Action Buttons */
        .action-btn {
            background: #F4F7FE;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            margin-left: 5px;
            color: #4318FF;
            /* Primary Blue for eye/pen */
        }

        .action-btn:hover {
            background: #E9EDF7;
        }

        .action-btn.delete:hover {
            color: #82150bff;
        }

        .action-btn.delete-btn {
            color: #EE5D50;
            /* Red for Trash */
        }

        .teams-badge {
            background: #F4F7FE;
            color: #1B2559;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 12px;
            text-decoration: none;
        }

        .teams-badge i {
            color: #A3AED0;
            margin-right: 4px;
        }

        /* DataTable Pagination UI */
        .dataTables_info {
            color: #A3AED0 !important;
            font-size: 13px;
            margin-top: 20px;
        }

        .dataTables_paginate {
            margin-top: 20px;
        }

        .paginate_button {
            border-radius: 8px !important;
            padding: 5px 12px !important;
            font-size: 13px !important;
        }
    </style>
</head>

<body>

    <?php include('includes/sidebar.php'); ?>
    <?php include('includes/nav.php'); ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold" style="color: #1B2559;">Hospitals Management</h2>
                <p style="color: #A3AED0; font-size: 14px;">Monitor hospitals, capacity, and medical resources</p>

            </div>
            <div class="d-flex gap-2 align-items-center">
                <select class="form-select filter-select">
                    <option>All Regions</option>
                </select>
                <select class="form-select filter-select">
                    <option>All Statuses</option>
                </select>
                <button class="btn btn-add-hospital">
                    <i class="fa-solid fa-plus me-2"></i> Add Hospital
                </button>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col">
                <div class="stat-card">
                    <div class="stat-label">Total Hospitals</div>
                    <div class="stat-value">42</div>
                </div>
            </div>
            <div class="col">
                <div class="stat-card">
                    <div class="stat-label">Available Beds</div>
                    <div class="stat-value">1,245</div>
                </div>
            </div>
            <div class="col">
                <div class="stat-card">
                    <div class="stat-label">Occupied Beds</div>
                    <div class="stat-value">2,885</div>
                </div>
            </div>
            <div class="col">
                <div class="stat-card">
                    <div class="stat-label">Occupancy Rate</div>
                    <div class="stat-value">70%</div>
                </div>
            </div>
            <div class="col">
                <div class="stat-card">
                    <div class="stat-label">ICU Beds Available</div>
                    <div class="stat-value">156</div>
                </div>
            </div>
            <div class="col">
                <div class="stat-card">
                    <div class="stat-label">Hospitals at Capacity</div>
                    <div class="stat-value">4</div>
                </div>
            </div>
        </div>

        <div class="modern-card">
            <h5 class="fw-bold mb-4" style="color: #1B2559;">Hospitals List</h5>
            <div class="table-responsive">
                <table id="hospitalsTable" class="table align-middle">
                    <thead>
                        <tr>
                            <th>Hospital Name</th>
                            <th>Location</th>
                            <th>Region</th>
                            <th>Total Beds</th>
                            <th>Occupied</th>
                            <th>Available</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Teams</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold">Rafic Hariri University Hospital</td>
                            <td>Beirut</td>
                            <td>Beirut</td>
                            <td>350</td>
                            <td>280</td>
                            <td>70</td>
                            <td class="status-high">High Occupancy</td>
                            <td>May 18, 2025 11:10 AM</td>
                            <td><a href="#" class="teams-badge"><i class="fa-solid fa-users me-1"></i> 4</a></td>
                            <td class="text-end">
                                <button class="action-btn"><i class="fa-solid fa-eye"></i></button>
                                <button class="action-btn"><i class="fa-solid fa-pen"></i></button>
                                <button class="action-btn delete-btn"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Tripoli Governmental Hospital</td>
                            <td>Al Mina</td>
                            <td>North</td>
                            <td>220</td>
                            <td>210</td>
                            <td>10</td>
                            <td class="status-at-capacity">At Capacity</td>
                            <td>May 18, 2025 11:10 AM</td>
                            <td><a href="#" class="teams-badge"><i class="fa-solid fa-users me-1"></i> 3</a></td>
                            <td class="text-end">
                                <button class="action-btn"><i class="fa-solid fa-eye"></i></button>
                                <button class="action-btn"><i class="fa-solid fa-pen"></i></button>
                                <button class="action-btn delete-btn"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Hotel Dieu de France</td>
                            <td>Achrafieh</td>
                            <td>Beirut</td>
                            <td>400</td>
                            <td>100</td>
                            <td>300</td>
                            <td class="status-low">Available</td>
                            <td>May 18, 2025 11:10 AM</td>
                            <td><a href="#" class="teams-badge"><i class="fa-solid fa-users me-1"></i> 8</a></td>
                            <td class="text-end">
                                <button class="action-btn"><i class="fa-solid fa-eye"></i></button>
                                <button class="action-btn"><i class="fa-solid fa-pen"></i></button>
                                <button class="action-btn delete-btn"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#hospitalsTable').DataTable({
                pageLength: 7,
                dom: 'rt<"d-flex justify-content-between align-items-center"ip>',
                language: {
                    info: "Showing _START_ to _END_ of _TOTAL_ results",
                    paginate: {
                        previous: "<",
                        next: ">"
                    }
                }
            });
        });
    </script>
</body>

</html>