<?php
session_start();
require_once("class/alerts.class.php");

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

$alerts = new alert();

$allallerts = $alerts->getAllAlerts();
$total = $alerts->totalAlerts();
$sentToday = $alerts->sentTodayAlerts();
$pending = $alerts->pendingAlerts();
// // $resolved = $incident->resolvedIncidents();
$critical = $alerts->criticalAlerts();
$regions = $alerts->getRegions();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Alerts Management</title>
    <?php include('includes/header.php'); ?>
    <style>
        @media (max-width: 992px) {
            .filter-row-container {
                flex-wrap: wrap;
            }

            .search-container {
                flex: 0 0 100% !important;
                min-width: 100% !important;
            }

            .filter-group-item {
                flex: 0 0 calc(50% - 5px) !important;
                min-width: calc(50% - 5px) !important;
            }

            .btn-add-navy {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .top-nav {
                left: 70px !important;
                padding: 0 16px;
            }

            .main-content {
                margin-left: 70px !important;
                padding: 14px !important;
            }

            .stat-col {
                flex: 0 0 calc(50% - 8px);
            }

            .card-subtext {
                display: none;
            }

            .dashboard-card {
                padding: 12px;
                min-height: auto;
            }
        }

        /* Small phones */
        @media (max-width: 480px) {
            .stat-col {
                flex: 0 0 100%;
            }

            .filter-group-item {
                flex: 0 0 100% !important;
                min-width: 100% !important;
            }

            .main-content {
                padding: 10px !important;
            }
        }

        .alert-actions {
            display: flex;
            gap: 8px;
        }
    </style>

</head>

<div class="modal fade" id="addAlertModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content rounded-4">

            <!-- HEADER -->
            <div class="modal-header">
                <h5 class="modal-title">Send Emergency Alert</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body">

                <form id="addAlertForm">

                    <!-- ALERT MESSAGE -->
                    <div class="mb-3">
                        <label class="form-label">Alert Message</label>
                        <textarea
                            id="alert_message"
                            class="form-control"
                            placeholder="Write alert message..."></textarea>
                    </div>

                    <!-- SEVERITY -->
                    <div class="mb-3">
                        <label class="form-label">Severity</label>
                        <select id="severity" class="form-select">
                            <option value="">Select Severity</option>
                            <option value="Info">Info</option>
                            <option value="Warning">Warning</option>
                            <option value="Critical">Critical</option>
                        </select>
                    </div>

                    <!-- REGION -->
                    <div class="mb-3">
                        <label class="form-label">Region</label>
                        <input
                            type="text"
                            id="region"
                            class="form-control"
                            placeholder="e.g. Beirut, Mount Lebanon">
                    </div>

                    <!-- STATUS -->
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select id="status" class="form-select">
                            <option value="Pending">Pending</option>
                            <option value="Sent">Sent</option>
                        </select>
                    </div>

                </form>

            </div>

            <!-- FOOTER -->
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>

                <button id="saveAlertBtn" class="btn btn-primary">
                    Send Alert
                </button>
            </div>

        </div>
    </div>
</div>

<body>

    <?php include('includes/sidebar.php'); ?>
    <?php include('includes/nav.php'); ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 style="color: #1b2559; font-weight: 700;">Alerts Management</h2>
                <p class="text-muted small">Broadcast emergency notifications and track recipient reach</p>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #f4f7fe; color: #4318ff;">
                        <i class="fa-solid fa-tower-broadcast"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Total Alerts</span>
                        <span class="card-value"><?php echo $total; ?></span>
                        <span class="card-subtext">All time</span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #f2faf8; color: #05cd99;">
                        <i class="fa-solid fa-paper-plane"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Sent Today</span>
                        <span class="card-value"><?php echo $sentToday; ?></span>
                        <span class="card-subtext">Successful</span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #fff9f2; color: #ffb547;">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Pending</span>
                        <span class="card-value"><?php echo $pending; ?></span>
                        <span class="card-subtext">Scheduled</span>
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
                        <span class="card-value"><?php echo $critical; ?></span>
                        <span class="card-subtext">Emergency</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="filter-row-container">
            <div class="search-container">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="alertSearch" class="form-control filter-control" placeholder="Search alerts...">
            </div>

            <div class="filter-group-item">
                <select id="regionFilter" class="form-select filter-control">
                    <option value="">All Regions</option>
                    <?php foreach ($regions as $row): ?>
                        <option value="<?= $row['region'] ?>">
                            <?= $row['region'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group-item">
                <select id="statusFilter" class="form-select filter-control">
                    <option value="">All Statuses</option>
                    <option>Sent</option>
                    <option>Pending</option>
                </select>
            </div>

            <div class="filter-group-item position-relative">
                <input type="date"
                    id="dateFilter"
                    class="form-control filter-control">
                <i class="fa-regular fa-calendar position-absolute"
                    style="right:12px; top:12px; color:#a3adc2; pointer-events:none;">
                </i>
            </div>

            <button
                class="btn btn-add-navy"
                data-bs-toggle="modal"
                data-bs-target="#addAlertModal">

                <i class="fa-solid fa-bullhorn"></i>
                Send Alert
            </button>
        </div>

        <div class="table-container shadow-sm p-4 bg-white rounded-4">
            <div class="table-responsive">
                <table class="table align-middle" id="alertTable">
                    <thead>
                        <tr>
                            <!-- <th>ID</th> -->
                            <th>Alert Message</th>
                            <th>Severity</th>
                            <th>Region</th>
                            <!-- <th>Recipients</th> -->
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php

                        foreach ($allallerts  as $row) {

                            if ($row['severity'] == 'Critical') {
                                $severityClass = "text-danger";
                            } elseif ($row['severity'] == 'Warning') {

                                $severityClass = "text-warning";
                            } else {
                                $severityClass = "text-success";
                            }

                            if ($row['status'] == 'Sent') {

                                $statusClass = "text-success";
                            } else {

                                $statusClass = "text-primary";
                            }
                        ?>

                            <tr>
                                <td class="alert-message">
                                    <span style="font-weight:700;">
                                        <?= $row['alert_message'] ?>
                                    </span>
                                </td>

                                <td class=" alert-severity status-text <?= $severityClass ?>">
                                    <?= $row['severity'] ?>
                                </td>

                                <td class="alert-region">
                                    <?= $row['region'] ?>
                                </td>

                                <td class="alert-status">
                                    <span class="status-text <?= $statusClass ?>">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>

                                <td>
                                    <?= $row['created_at'] ?>
                                </td>

                                <td class="text-center">

                                    <i class="fa fa-edit text-muted me-2 editAlertsBtn"
                                        style="cursor:pointer;"
                                        data-id="<?php echo $row['id']; ?>">
                                    </i>

                                    <i class="fa fa-trash text-danger deleteAlertsBtn"
                                        style="cursor:pointer;"
                                        data-id="<?php echo $row['id']; ?>">
                                    </i>

                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php include('includes/script.php'); ?>
    <script>
        $(document).ready(function() {

            var table = $('#alertTable').DataTable({
                pageLength: 7,
                order: [
                    [4, 'desc']
                ],
                dom: 'rt<"d-flex justify-content-between"ip>',
                language: {
                    info: "Showing _START_ to _END_ of _TOTAL_ results",
                    paginate: {
                        previous: "<",
                        next: ">"
                    }
                }
            });

            // SEARCH
            $('#alertSearch').on('keyup', function() {

                table.search(this.value).draw();

            });

            // REGION FILTER
            $('#regionFilter').on('change', function() {

                table.column(2).search(this.value).draw();

            });

            // STATUS FILTER
            $('#statusFilter').on('change', function() {

                table.column(3).search(this.value).draw();

            });

            // DATE FILTER
            $('#dateFilter').on('change', function() {

                table.column(4).search(this.value).draw();

            });

        });
        $('#saveAlertBtn').click(function() {

            let alert_message = $('#alert_message').val();
            let severity = $('#severity').val();
            let region = $('#region').val();
            let status = $('#status').val();

            $.ajax({
                url: 'actions/add_alerts.php',
                type: 'POST',
                data: {
                    alert_message: alert_message,
                    severity: severity,
                    region: region,
                    status: status
                },
                dataType: 'json',

                success: function(response) {

                    if (response.status === 'success') {
                        $('#addAlertModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        setTimeout(() => location.reload(), 1500);

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
        $(document).on('click', '.deleteAlertsBtn', function() {

            let id = $(this).data('id');

            Swal.fire({
                title: 'Delete Alerts?',
                text: "This action cannot be undone",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Delete'
            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({
                        url: 'actions/delete_alerts.php',
                        type: 'POST',
                        data: {
                            id: id
                        },
                        dataType: 'json',

                        success: function(response) {

                            if (response.status === 'success') {

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {

                                    location.reload();

                                });


                            } else {

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message
                                });
                            }
                        }
                    });

                }

            });

        });
        // update 

        $(document).on('click', '.editAlertsBtn', function() {

            let row = $(this).closest('tr');

            let message = row.find('.alert-message').text().trim();
            let severity = row.find('.alert-severity').text().trim();
            let region = row.find('.alert-region').text().trim();
            let status = row.find('.alert-status').text().trim();


            row.find('.alert-message').html(` <input type="text" class="form-control edit-alert-message" value="${message}"> `);

            row.find('.alert-severity').html(` <select class="form-select edit-alert-severity">
            <option ${severity == 'Info' ? 'selected' : ''}>Info</option>
            <option ${severity == 'Warning' ? 'selected' : ''}>Warning</option>
            <option ${severity == 'Critical' ? 'selected' : ''}>Critical</option>
            </select> `);

            row.find('.alert-region').html(`<input type="text" class="form-control edit-alert-region" value="${region}">`);

            row.find('.alert-status').html(` <select class="form-select edit-alert-status">
            <option ${status == 'Sent' ? 'selected' : ''}>Sent</option>
            <option ${status == 'Pending' ? 'selected' : ''}>Pending</option>
            </select>`);

            row.find('td:last').html(`
    <div class="alert-actions">
        <button class="btn btn-success btn-sm saveAlertBtn" data-id="${$(this).data('id')}">Save</button>
        <button class="btn btn-secondary btn-sm cancelAlertBtn" data-id="${$(this).data('id')}">Cancel</button>
    </div>
`);
        });

        $(document).on('click', '.saveAlertBtn', function() {

            let row = $(this).closest('tr');

            let id = row.find('.cancelAlertBtn').data('id');

            let message = row.find('.edit-alert-message').val();
            let severity = row.find('.edit-alert-severity').val();
            let region = row.find('.edit-alert-region').val();
            let status = row.find('.edit-alert-status').val();

            $.ajax({

                url: 'actions/update_alerts.php',

                type: 'POST',

                data: {
                    id: id,
                    alert_message: message,
                    severity: severity,
                    region: region,
                    status: status
                },

                dataType: 'json',

                success: function(response) {

                    if (response.status === 'success') {

                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        window.location.reload();

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
        $(document).on('click', '.cancelAlertBtn', function() {
            location.reload();
        });
    </script>

</body>

</html>