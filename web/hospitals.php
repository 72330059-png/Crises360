<?php
session_start();
require_once("class/hospitals.class.php");

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$hospital = new hospital();
$totalHospitals = $hospital->totalHospitals();
$totalAvailableBeds = $hospital->totalAvailableBeds();
$totalOccupiedBeds = $hospital->totalOccupiedBeds();
$occupancyRate = $hospital->occupancyRate();
$totalAvailableICU = $hospital->totalAvailableICU();
$hospitalsAvailable = $hospital->availableHospitals();
$allHospitals = $hospital->getAllHospitals();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Hospitals Management | Admin</title>
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
            font-size: 14px;
            color: #1B2559;
            font-weight: 500;
            background-color: white;
            height: 40px;
            min-width: 140px;
        }

        .btn-add-hospital {
            background: #3771c3;
            color: white;
            border-radius: 12px;
            padding: 0 20px;
            font-weight: 700;
            border: none;
            height: 40px;
            width: 400px;
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

        .status-high {
            color: #EE5D50;
            font-weight: 700;
        }

        .status-capacity {
            color: #EE5D50;
            font-weight: 700;
        }

        .status-medium {
            color: #FFB547;
            font-weight: 700;
        }

        .status-low {
            color: #05CD99;
            font-weight: 700;
        }


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

                <select class="form-select filter-select" id="statushospital">
                    <option value="">All Statuses</option>
                    <option value="Safe">Safe</option>
                    <option value="Warning">Warning</option>
                    <option value="Dangerous">Dangerous</option>
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
                    <div class="stat-value"><?= $totalHospitals ?></div>
                </div>
            </div>
            <div class="col">
                <div class="stat-card">
                    <div class="stat-label">Available Beds</div>
                    <div class="stat-value"><?= $totalAvailableBeds ?></div>
                </div>
            </div>
            <div class="col">
                <div class="stat-card">
                    <div class="stat-label">Occupied Beds</div>
                    <div class="stat-value"><?= $totalOccupiedBeds ?></div>
                </div>
            </div>
            <div class="col">
                <div class="stat-card">
                    <div class="stat-label">Occupancy Rate</div>
                    <div class="stat-value"><?= $occupancyRate ?>%</div>
                </div>
            </div>
            <div class="col">
                <div class="stat-card">
                    <div class="stat-label">ICU Beds Available</div>
                    <div class="stat-value"><?= $totalAvailableICU ?></div>
                </div>
            </div>
            <div class="col">
                <div class="stat-card">
                    <div class="stat-label">Hospitals at Capacity</div>
                    <div class="stat-value"><?= $hospitalsAvailable ?></div>
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
                            <!-- <th>Region</th> -->
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

                        <?php foreach ($allHospitals as $h): ?>

                            <?php

                            if ($h['hospital_status'] == 'Warning') {
                                $statusClass = "status-medium";
                            } elseif ($h['hospital_status'] == 'Dangerous') {
                                $statusClass = "status-high";
                            } else {
                                $statusClass = "status-low";
                            }

                            ?>

                            <tr>

                                <td class="fw-bold">
                                    <?= $h['name'] ?? 'Hospital' ?>
                                </td>

                                <td><?= $h['location'] ?></td>

                                <td><?= $h['total_beds'] ?></td>

                                <td><?= $h['occupied_beds'] ?></td>

                                <td><?= $h['available_beds'] ?></td>

                                <td class="<?= $statusClass ?>">
                                    <?= $h['hospital_status'] ?>
                                </td>

                                <td>
                                    <?= date('M d, Y h:i A', strtotime($h['updated_at'])) ?>
                                </td>
                                <td data-id="<?= $h['id'] ?>">
                                    <a href="hospital_teams.php?hospital_id=<?= $h['id'] ?>" class="teams-badge">
                                        <i class="fa-solid fa-users me-1"></i>
                                        <?= $h['total_teams'] ?>
                                    </a>
                                </td>

                                <td class="text-end">

                                    <button class="action-btn">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    <button class="action-btn">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>

                                    <button class="action-btn delete-btn">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            var table = $('#hospitalsTable').DataTable({
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
            $('#statushospital').on('change', function() {

                var status = $(this).val();

                table.column(5).search(status).draw();

            });
        });
    </script>
    <?php include('includes/script.php'); ?>

</body>

</html>