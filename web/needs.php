<?php
session_start();
require_once("class/municipality.class.php");
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
$mun = new muni();
$needs = $mun->getAllNeeds();
$totalNeeds = $mun->totalNeeds();
$fulfilledNeeds = $mun->fulfilledNeeds();
$activeNeeds = $mun->activeNeeds();
$highPriorityNeeds = $mun->highPriorityNeeds();
$totalMunicipalities = $mun->totalMunicipalitiesWithNeeds();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Needs & Requests</title>
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

        @media (max-width: 768px) {

            /* Main content */
            .main-content {
                margin-left: 70px !important;
                width: calc(100% - 70px) !important;
                padding: 15px !important;
            }

            /* Filters wrap */
            .d-flex.gap-2.mb-4 {
                flex-wrap: wrap;
            }

            .filter-control {
                width: 100%;
                min-width: 100%;
            }

            /* Stats cards scroll */
            .row.g-3.mb-4 {
                flex-wrap: nowrap !important;
                overflow-x: auto;
                overflow-y: hidden;
                padding-bottom: 8px;
                scrollbar-width: thin;
            }

            .row.g-3.mb-4>.col {
                flex: 0 0 220px !important;
                min-width: 220px !important;
            }

            .row.g-3.mb-4::-webkit-scrollbar {
                height: 5px;
            }

            .row.g-3.mb-4::-webkit-scrollbar-thumb {
                background: #d1d5db;
                border-radius: 20px;
            }

            .stat-card {
                height: 100%;
            }

            .stat-value {
                font-size: 18px;
            }

            .modern-card {
                padding: 15px;
            }
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

            <!-- SEARCH -->
            <input type="text"
                id="needSearch"
                class="form-control filter-control"
                placeholder="Search needs...">

            <!-- CATEGORY FILTER -->
            <select id="categoryFilter" class="form-select filter-control">

                <option value="">All Categories</option>

                <?php

                $categories = [];

                foreach ($needs as $row) {

                    if (!in_array($row['category'], $categories)) {

                        $categories[] = $row['category'];
                    }
                }

                foreach ($categories as $category):
                ?>

                    <option value="<?= ucfirst($category) ?>">
                        <?= ucfirst($category) ?>
                    </option>

                <?php endforeach; ?>

            </select>

            <!-- PRIORITY FILTER -->
            <select id="priorityFilter" class="form-select filter-control">

                <option value="">All Priorities</option>

                <?php

                $priorities = [];

                foreach ($needs as $row) {

                    if (!in_array($row['priority'], $priorities)) {

                        $priorities[] = $row['priority'];
                    }
                }

                foreach ($priorities as $priority):
                ?>

                    <option value="<?= ucfirst($priority) ?>">
                        <?= ucfirst($priority) ?>
                    </option>

                <?php endforeach; ?>

            </select>

            <!-- DATE FILTER -->
            <input type="date"
                id="dateFilter"
                class="form-control filter-control">

        </div>

        <!-- STATS -->
        <div class="row g-3 mb-4">
            <div class="col">
                <div class="stat-card">
                    <div class="stat-label">Total Requests</div>
                    <div class="stat-value"><?= $totalNeeds ?></div>
                </div>
            </div>

            <div class="col">
                <div class="stat-card">
                    <div class="stat-label">Municipalities</div>
                    <div class="stat-value"><?= $totalMunicipalities ?></div>
                </div>
            </div>

            <div class="col">
                <div class="stat-card">
                    <div class="stat-label">In Progress</div>
                    <div class="stat-value"><?= $activeNeeds ?></div>
                </div>
            </div>

            <div class="col">
                <div class="stat-card d-flex align-items-center justify-content-between p-3">
                    <div>
                        <div class="stat-label mb-1" style="color: #a3aed0; font-size: 0.9rem;">Fulfilled</div>
                        <div class="stat-value fw-bold" style="font-size: 1.5rem; color: #1b2559;"><?= $fulfilledNeeds ?></div>
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

            <div class="table-responsive">
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

                        <?php foreach ($needs as $row): ?>

                            <?php

                            if ($row['priority'] == 'high') {
                                $priorityClass = "priority-high";
                            } elseif ($row['priority'] == 'medium') {
                                $priorityClass = "priority-medium";
                            } else {
                                $priorityClass = "priority-low";
                            }

                            if ($row['status'] == 'fulfilled') {
                                $statusClass = "status-open";
                            } elseif ($row['status'] == 'in_progress') {
                                $statusClass = "status-progress";
                            } else {
                                $statusClass = "status-closed";
                            }
                            ?>
                            <tr>
                                <td class="fw-bold">
                                    <?= $row['need_name'] ?>
                                </td>

                                <td>
                                    <?= ucfirst($row['category']) ?>
                                </td>

                                <td>
                                    <span class="<?= $priorityClass ?>">
                                        <?= ucfirst($row['priority']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= $row['municipality_name'] ?>
                                </td>

                                <td>
                                    <?= $row['quantity'] ?>
                                </td>

                                <td>
                                    <span class="<?= $statusClass ?>">
                                        <?= ucfirst(str_replace('_', ' ', $row['status'])) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= date('Y-m-d', strtotime($row['created_at'])) ?>
                                </td>
                                <td class="text-end">
                                    <button class="action-icon action-approve fulfillBtn"
                                        data-id="<?= $row['id'] ?>"
                                        data-tooltip="Fulfill Request">

                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                    <button class="action-icon action-reject rejectBtn"
                                        data-id="<?= $row['id'] ?>"
                                        data-tooltip="Reject Request">

                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <?php include('includes/script.php'); ?>
    <script>
        $(document).ready(function() {

            var table = $('#needsTable').DataTable({

                pageLength: 7,

                dom: 'rt<"d-flex justify-content-between"ip>',

                order: [
                    [6, 'desc']
                ],


                language: {

                    info: "Showing _START_ to _END_ of _TOTAL_ results",

                    paginate: {
                        previous: "<",
                        next: ">"
                    }
                }
            });

            // SEARCH
            $('#needSearch').on('keyup', function() {

                table.search(this.value).draw();

            });

            // CATEGORY FILTER
            $('#categoryFilter').on('change', function() {
                table.column(1).search(this.value, false, false).draw();
            });

            // PRIORITY FILTER
            $('#priorityFilter').on('change', function() {
                table.column(2).search(this.value, false, false).draw();
            });

            // DATE FILTER
            $('#dateFilter').on('change', function() {
                table.column(6).search(this.value, false, false).draw();
            });

        });

        $(document).on('click', '.fulfillBtn', function() {

            let id = $(this).data('id');

            $.ajax({

                url: 'actions/fulfill_need.php',

                type: 'POST',

                data: {
                    id: id
                },

                dataType: 'json',

                success: function(response) {

                    if (response.status == 'success') {

                        Swal.fire({

                            icon: 'success',

                            title: 'Success',

                            text: response.message,

                            timer: 1500,

                            showConfirmButton: false

                        });

                        setTimeout(function() {

                            location.reload();

                        }, 1500);

                    } else {

                        Swal.fire({

                            icon: 'error',

                            title: 'Error',

                            text: response.message

                        });
                    }
                }
            });
        });

        $(document).on('click', '.rejectBtn', function() {

            let id = $(this).data('id');

            $.ajax({

                url: 'actions/reject_need.php',

                type: 'POST',

                data: {
                    id: id
                },

                dataType: 'json',

                success: function(response) {

                    if (response.status == 'success') {

                        Swal.fire({

                            icon: 'warning',

                            title: 'Need rejected',

                            text: response.message,

                            timer: 3000,

                            showConfirmButton: false

                        });

                        setTimeout(function() {

                            location.reload();

                        }, 1500);

                    } else {

                        Swal.fire({

                            icon: 'error',

                            title: 'Error',

                            text: response.message

                        });
                    }
                }
            });
        });
    </script>

</body>

</html>