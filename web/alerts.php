<?php
session_start();
require_once("class/alerts.class.php");

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
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
?>
<!DOCTYPE html>
<html>

<head>
    <title>Alerts Management</title>
    <?php include('includes/header.php'); ?>
</head>

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

            <!-- <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #f4f7fe; color: #4318ff;">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Recipients</span>
                        <span class="card-value">12k</span>
                        <span class="card-subtext">Total reach</span>
                    </div>
                </div>
            </div> -->

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
                <select class="form-select filter-control">
                    <option selected>All Regions</option>
                    <option>Beirut</option>
                    <option>Mount Lebanon</option>
                </select>
            </div>

            <div class="filter-group-item">
                <select class="form-select filter-control">
                    <option selected>All Statuses</option>
                    <option>Sent</option>
                    <option>Pending</option>
                </select>
            </div>

            <div class="filter-group-item position-relative">
                <input type="text" class="form-control filter-control" placeholder="Date" onfocus="(this.type='date')">
                <i class="fa-regular fa-calendar position-absolute" style="right:12px; top:12px; color:#a3adc2; pointer-events:none;"></i>
            </div>

            <button class="btn btn-add-navy">
                <i class="fa-solid fa-bullhorn"></i> Send Alert
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
                        $data = $alerts->getAllAlerts();

                        foreach ($data as $row) {

                            if ($row['severity'] == 'Critical') {

                                // $icon = "fa-circle-exclamation";
                                // $iconColor = "#ee5d50";
                                $severityClass = "text-danger";
                            } elseif ($row['severity'] == 'Warning') {

                                // $icon = "fa-triangle-exclamation";
                                // $iconColor = "#ffb547";
                                $severityClass = "text-warning";
                            } else {

                                // $icon = "fa-circle-info";
                                // $iconColor = "#4318ff";
                                $severityClass = "text-success";
                            }

                            if ($row['status'] == 'Sent') {

                                $statusClass = "text-success";
                            } else {

                                $statusClass = "text-primary";
                            }
                        ?>

                            <tr>

                                <!-- <td>#<?= $row['id'] ?></td> -->

                                <td>
                               

                                    <span style="font-weight:700;">
                                        <?= $row['alert_message'] ?>
                                    </span>
                                </td>

                                <td class="status-text <?= $severityClass ?>">
                                    <?= $row['severity'] ?>
                                </td>

                                <td>
                                    <?= $row['region'] ?>
                                </td>

                                <!-- <td>
                                    <i class="fa-solid fa-users me-1"
                                        style="font-size: 12px; color: #a3adc2;"></i>

                                    <?= number_format($row['recepients_count']) ?>
                                </td> -->

                                <td>
                                    <span class="status-text <?= $statusClass ?>">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>

                                <td>
                                    <?= $row['created_at'] ?>
                                </td>

                                <td class="text-center">

                                    <i class="fa fa-edit text-muted me-2 editBtn"
                                        style="cursor:pointer;"
                                        data-id="<?php echo $row['id']; ?>">
                                    </i>

                                    <i class="fa fa-trash text-danger deleteBtn"
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


</body>

</html>