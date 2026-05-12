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
                        <span class="card-value">210</span>
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
                        <span class="card-value">18</span>
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
                        <span class="card-value">45</span>
                        <span class="card-subtext">Scheduled</span>
                    </div>
                </div>
            </div>

            <div class="col">
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
            </div>

            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #fff5f5; color: #ee5d50;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Critical</span>
                        <span class="card-value">9</span>
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
                            <th>ID</th>
                            <th>Alert Message</th>
                            <th>Severity</th>
                            <th>Region</th>
                            <th>Recipients</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#501</td>
                            <td>
                                <i class="fa-solid fa-circle-exclamation me-2" style="color: #ee5d50;"></i>
                                <span style="font-weight:700;">Evacuate area immediately</span>
                            </td>
                            <td class="status-text text-high">Critical</td>
                            <td>Beirut</td>
                            <td><i class="fa-solid fa-users me-1" style="font-size: 12px; color: #a3adc2;"></i> 1,240</td>
                            <td><span class="status-text text-resolved">Sent</span></td>
                            <td>2026-04-28</td>
                            <td class="text-end">
                                <i class="fa-regular fa-eye text-muted me-2" style="cursor:pointer"></i>
                                <i class="fa-solid fa-trash text-danger" style="cursor:pointer"></i>
                            </td>
                        </tr>
                        <tr>
                            <td>#502</td>
                            <td>
                                <i class="fa-solid fa-circle-info me-2" style="color: #ffb547;"></i>
                                <span style="font-weight:700;">Heavy rain expected</span>
                            </td>
                            <td class="status-text text-medium">Warning</td>
                            <td>Tripoli</td>
                            <td><i class="fa-solid fa-users me-1" style="font-size: 12px; color: #a3adc2;"></i> 8,500</td>
                            <td><span class="status-text text-investigating" style="color: #111c44 !important;">Pending</span></td>
                            <td>2026-04-27</td>
                            <td class="text-end">
                                <i class="fa-regular fa-eye text-muted me-2" style="cursor:pointer"></i>
                                <i class="fa-solid fa-trash text-danger" style="cursor:pointer"></i>
                            </td>
                        </tr>
                        <tr>
                            <td>#501</td>
                            <td>
                                <i class="fa-solid fa-circle-exclamation me-2" style="color: #ee5d50;"></i>
                                <span style="font-weight:700;">Evacuate area immediately</span>
                            </td>
                            <td class="status-text text-high">Critical</td>
                            <td>Beirut</td>
                            <td><i class="fa-solid fa-users me-1" style="font-size: 12px; color: #a3adc2;"></i> 1,240</td>
                            <td><span class="status-text text-resolved">Sent</span></td>
                            <td>2026-04-28</td>
                            <td class="text-end">
                                <i class="fa-regular fa-eye text-muted me-2" style="cursor:pointer"></i>
                                <i class="fa-solid fa-trash text-danger" style="cursor:pointer"></i>
                            </td>
                        </tr>
                        <tr>
                            <td>#502</td>
                            <td>
                                <i class="fa-solid fa-circle-info me-2" style="color: #ffb547;"></i>
                                <span style="font-weight:700;">Heavy rain expected</span>
                            </td>
                            <td class="status-text text-medium">Warning</td>
                            <td>Tripoli</td>
                            <td><i class="fa-solid fa-users me-1" style="font-size: 12px; color: #a3adc2;"></i> 8,500</td>
                            <td><span class="status-text text-investigating" style="color: #111c44 !important;">Pending</span></td>
                            <td>2026-04-27</td>
                            <td class="text-end">
                                <i class="fa-regular fa-eye text-muted me-2" style="cursor:pointer"></i>
                                <i class="fa-solid fa-trash text-danger" style="cursor:pointer"></i>
                            </td>
                        </tr>
                        <tr>
                            <td>#501</td>
                            <td>
                                <i class="fa-solid fa-circle-exclamation me-2" style="color: #ee5d50;"></i>
                                <span style="font-weight:700;">Evacuate area immediately</span>
                            </td>
                            <td class="status-text text-high">Critical</td>
                            <td>Beirut</td>
                            <td><i class="fa-solid fa-users me-1" style="font-size: 12px; color: #a3adc2;"></i> 1,240</td>
                            <td><span class="status-text text-resolved">Sent</span></td>
                            <td>2026-04-28</td>
                            <td class="text-end">
                                <i class="fa-regular fa-eye text-muted me-2" style="cursor:pointer"></i>
                                <i class="fa-solid fa-trash text-danger" style="cursor:pointer"></i>
                            </td>
                        </tr>
                        <tr>
                            <td>#502</td>
                            <td>
                                <i class="fa-solid fa-circle-info me-2" style="color: #ffb547;"></i>
                                <span style="font-weight:700;">Heavy rain expected</span>
                            </td>
                            <td class="status-text text-medium">Warning</td>
                            <td>Tripoli</td>
                            <td><i class="fa-solid fa-users me-1" style="font-size: 12px; color: #a3adc2;"></i> 8,500</td>
                            <td><span class="status-text text-investigating" style="color: #111c44 !important;">Pending</span></td>
                            <td>2026-04-27</td>
                            <td class="text-end">
                                <i class="fa-regular fa-eye text-muted me-2" style="cursor:pointer"></i>
                                <i class="fa-solid fa-trash text-danger" style="cursor:pointer"></i>
                            </td>
                        </tr>
                        <tr>
                            <td>#501</td>
                            <td>
                                <i class="fa-solid fa-circle-exclamation me-2" style="color: #ee5d50;"></i>
                                <span style="font-weight:700;">Evacuate area immediately</span>
                            </td>
                            <td class="status-text text-high">Critical</td>
                            <td>Beirut</td>
                            <td><i class="fa-solid fa-users me-1" style="font-size: 12px; color: #a3adc2;"></i> 1,240</td>
                            <td><span class="status-text text-resolved">Sent</span></td>
                            <td>2026-04-28</td>
                            <td class="text-end">
                                <i class="fa-regular fa-eye text-muted me-2" style="cursor:pointer"></i>
                                <i class="fa-solid fa-trash text-danger" style="cursor:pointer"></i>
                            </td>
                        </tr>
                        <tr>
                            <td>#502</td>
                            <td>
                                <i class="fa-solid fa-circle-info me-2" style="color: #ffb547;"></i>
                                <span style="font-weight:700;">Heavy rain expected</span>
                            </td>
                            <td class="status-text text-medium">Warning</td>
                            <td>Tripoli</td>
                            <td><i class="fa-solid fa-users me-1" style="font-size: 12px; color: #a3adc2;"></i> 8,500</td>
                            <td><span class="status-text text-investigating" style="color: #111c44 !important;">Pending</span></td>
                            <td>2026-04-27</td>
                            <td class="text-end">
                                <i class="fa-regular fa-eye text-muted me-2" style="cursor:pointer"></i>
                                <i class="fa-solid fa-trash text-danger" style="cursor:pointer"></i>
                            </td>
                        </tr>
                        <tr>
                            <td>#501</td>
                            <td>
                                <i class="fa-solid fa-circle-exclamation me-2" style="color: #ee5d50;"></i>
                                <span style="font-weight:700;">Evacuate area immediately</span>
                            </td>
                            <td class="status-text text-high">Critical</td>
                            <td>Beirut</td>
                            <td><i class="fa-solid fa-users me-1" style="font-size: 12px; color: #a3adc2;"></i> 1,240</td>
                            <td><span class="status-text text-resolved">Sent</span></td>
                            <td>2026-04-28</td>
                            <td class="text-end">
                                <i class="fa-regular fa-eye text-muted me-2" style="cursor:pointer"></i>
                                <i class="fa-solid fa-trash text-danger" style="cursor:pointer"></i>
                            </td>
                        </tr>
                        <tr>
                            <td>#502</td>
                            <td>
                                <i class="fa-solid fa-circle-info me-2" style="color: #ffb547;"></i>
                                <span style="font-weight:700;">Heavy rain expected</span>
                            </td>
                            <td class="status-text text-medium">Warning</td>
                            <td>Tripoli</td>
                            <td><i class="fa-solid fa-users me-1" style="font-size: 12px; color: #a3adc2;"></i> 8,500</td>
                            <td><span class="status-text text-investigating" style="color: #111c44 !important;">Pending</span></td>
                            <td>2026-04-27</td>
                            <td class="text-end">
                                <i class="fa-regular fa-eye text-muted me-2" style="cursor:pointer"></i>
                                <i class="fa-solid fa-trash text-danger" style="cursor:pointer"></i>
                            </td>
                        </tr>
                        <tr>
                            <td>#501</td>
                            <td>
                                <i class="fa-solid fa-circle-exclamation me-2" style="color: #ee5d50;"></i>
                                <span style="font-weight:700;">Evacuate area immediately</span>
                            </td>
                            <td class="status-text text-high">Critical</td>
                            <td>Beirut</td>
                            <td><i class="fa-solid fa-users me-1" style="font-size: 12px; color: #a3adc2;"></i> 1,240</td>
                            <td><span class="status-text text-resolved">Sent</span></td>
                            <td>2026-04-28</td>
                            <td class="text-end">
                                <i class="fa-regular fa-eye text-muted me-2" style="cursor:pointer"></i>
                                <i class="fa-solid fa-trash text-danger" style="cursor:pointer"></i>
                            </td>
                        </tr>
                        <tr>
                            <td>#502</td>
                            <td>
                                <i class="fa-solid fa-circle-info me-2" style="color: #ffb547;"></i>
                                <span style="font-weight:700;">Heavy rain expected</span>
                            </td>
                            <td class="status-text text-medium">Warning</td>
                            <td>Tripoli</td>
                            <td><i class="fa-solid fa-users me-1" style="font-size: 12px; color: #a3adc2;"></i> 8,500</td>
                            <td><span class="status-text text-investigating" style="color: #111c44 !important;">Pending</span></td>
                            <td>2026-04-27</td>
                            <td class="text-end">
                                <i class="fa-regular fa-eye text-muted me-2" style="cursor:pointer"></i>
                                <i class="fa-solid fa-trash text-danger" style="cursor:pointer"></i>
                            </td>
                        </tr>
                        <tr>
                            <td>#501</td>
                            <td>
                                <i class="fa-solid fa-circle-exclamation me-2" style="color: #ee5d50;"></i>
                                <span style="font-weight:700;">Evacuate area immediately</span>
                            </td>
                            <td class="status-text text-high">Critical</td>
                            <td>Beirut</td>
                            <td><i class="fa-solid fa-users me-1" style="font-size: 12px; color: #a3adc2;"></i> 1,240</td>
                            <td><span class="status-text text-resolved">Sent</span></td>
                            <td>2026-04-28</td>
                            <td class="text-end">
                                <i class="fa-regular fa-eye text-muted me-2" style="cursor:pointer"></i>
                                <i class="fa-solid fa-trash text-danger" style="cursor:pointer"></i>
                            </td>
                        </tr>
                        <tr>
                            <td>#502</td>
                            <td>
                                <i class="fa-solid fa-circle-info me-2" style="color: #ffb547;"></i>
                                <span style="font-weight:700;">Heavy rain expected</span>
                            </td>
                            <td class="status-text text-medium">Warning</td>
                            <td>Tripoli</td>
                            <td><i class="fa-solid fa-users me-1" style="font-size: 12px; color: #a3adc2;"></i> 8,500</td>
                            <td><span class="status-text text-investigating" style="color: #111c44 !important;">Pending</span></td>
                            <td>2026-04-27</td>
                            <td class="text-end">
                                <i class="fa-regular fa-eye text-muted me-2" style="cursor:pointer"></i>
                                <i class="fa-solid fa-trash text-danger" style="cursor:pointer"></i>
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