<?php
session_start();
// require_once("class/DAL.class.php");
require_once("class/incidents.class.php");

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// $dal = new DAL();
$incident = new incident();

$allIncidents = $incident->getAllIncidents();

$total = $incident->totalIncidents();

$active = $incident->activeIncidents();

$progress = $incident->inProgressIncidents();

$resolved = $incident->resolvedIncidents();

$critical = $incident->criticalIncidents();

?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <?php include('includes/header.php'); ?>
</head>

<div class="modal fade" id="viewIncidentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">

            <div class="modal-header">

                <h5 class="modal-title" id="incidentTitle">
                    Incident Details
                </h5>

                <div class="d-flex align-items-center gap-2">

                    <!-- EDIT DESCRIPTION -->
                    <i class="fa fa-pen text-primary"
                        id="editDescriptionBtn"
                        style="cursor:pointer;">
                    </i>

                    <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>

                </div>

            </div>

            <div class="modal-body">

                <h6>Description</h6>

                <!-- NORMAL VIEW -->
                <p id="incidentDescription"></p>

                <!-- EDIT TEXTAREA -->
                <textarea
                    id="editDescriptionTextarea"
                    class="form-control d-none"
                    rows="5">
                </textarea>

                <!-- SAVE BUTTON -->
                <button
                    class="btn btn-success mt-3 d-none"
                    id="saveDescriptionBtn">
                    Save Description
                </button>

            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="addIncidentModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered modal-md">

        <div class="modal-content rounded-4">

            <div class="modal-header">

                <h5 class="modal-title">
                    Add New Incident
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <form id="addIncidentForm">

                    <div class="input-group mb-3">

                        <span class="input-group-text" style="width:150px;">
                            Incident
                        </span>

                        <input
                            type="text"
                            class="form-control"
                            id="addIncidentName"
                            placeholder="Incident name">
                    </div>

                    <div class="input-group mb-3">

                        <span class="input-group-text" style="width:150px;">
                            Location
                        </span>

                        <input
                            type="text"
                            class="form-control"
                            id="addLocation"
                            placeholder="Location">
                    </div>

                    <div class="input-group mb-3">

                        <span class="input-group-text" style="width:150px;">
                            Severity
                        </span>

                        <select class="form-select" id="addSeverity">

                            <option value="">Select</option>

                            <option value="Low">Low</option>

                            <option value="Medium">Medium</option>

                            <option value="High">High</option>

                        </select>

                    </div>

                    <div class="input-group mb-3">

                        <span class="input-group-text" style="width:150px;">
                            Status
                        </span>

                        <select class="form-select" id="addStatus">

                            <option value="">Select</option>

                            <option value="Investigating">
                                Investigating
                            </option>

                            <option value="In Progress">
                                In Progress
                            </option>

                            <option value="Resolved">
                                Resolved
                            </option>

                        </select>

                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            Description
                        </label>

                        <textarea
                            class="form-control"
                            rows="3"
                            id="addDescription"
                            placeholder="Write description...">
            </textarea>

                    </div>

                </form>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    Cancel
                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    id="saveIncidentBtn">
                    Add Incident
                </button>

            </div>

        </div>

    </div>

</div>

<body>

    <!-- SIDEBAR -->
    <?php include('includes/sidebar.php'); ?>
    <?php include('includes/nav.php'); ?>

    <div class="main-content">
        <div class="page-header mb-4">
            <h2>Incidents Management</h2>
            <p class="text-muted small">Monitor, track and manage all incidents in real-time</p>
        </div>

        <div class="row g-3 mb-4">

            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #f4f7fe; color: #4318ff;">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Total Incidents</span>
                        <span class="card-value"><?php echo $total; ?></span>
                        <span class="card-subtext">All time</span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #fff5f5; color: #ee5d50;">
                        <i class="fa-solid fa-circle-exclamation"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Active Incidents</span>
                        <span class="card-value"><?php echo $active; ?></span>
                        <span class="card-subtext">Currently active</span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #fff9f2; color: #ffb547;">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">In Progress</span>
                        <span class="card-value"><?php echo $progress; ?></span>
                        <span class="card-subtext">Under response</span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #f2faf8; color: #05cd99;">
                        <i class="fa-solid fa-square-check"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Resolved</span>
                        <span class="card-value"><?php echo $resolved; ?></span>
                        <span class="card-subtext">Successfully resolved</span>
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
                        <span class="card-subtext">High priority</span>
                    </div>
                </div>
            </div>

        </div>

        <div class="d-flex align-items-center mb-4">
            <div class="filter-row-container">

                <div class="search-container">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchFilter" class="form-control filter-control" placeholder="Search incidents...">
                </div>

                <div class="filter-group-item">
                    <select
                        class="form-select filter-control"
                        id="regionFilter">
                        <option value="">All Regions</option>
                        <option value="Beirut">Beirut</option>
                        <option value="Tripoli">Tripoli</option>
                        <option value="Saida">Saida</option>
                    </select>
                </div>

                <div class="filter-group-item">
                    <select class="form-select filter-control" id="typeFilter">

                        <option value="">All Types</option>

                        <option value="High">High</option>

                        <option value="Low">Low</option>

                        <option value="Medium">Medium</option>

                    </select>
                </div>

                <div class="filter-group-item">
                    <select class="form-select filter-control" id="statusFilter">

                        <option value="">All Statuses</option>

                        <option value="Investigating">
                            Investigating
                        </option>

                        <option value="In Progress">
                            In Progress
                        </option>

                        <option value="Resolved">
                            Resolved
                        </option>
                    </select>
                </div>

                <div class="filter-group-item position-relative">
                    <input id="dateFilter" type="text" class="form-control filter-control" placeholder="From - To" onfocus="(this.type='date')">
                    <i class="fa-regular fa-calendar position-absolute" style="right:12px; top:12px; color:#a3adc2; pointer-events:none;"></i>
                </div>

                <button
                    class="btn btn-add-navy"
                    data-bs-toggle="modal"
                    data-bs-target="#addIncidentModal">

                    <i class="fa-solid fa-plus"></i>
                    Add Incident
                </button>

            </div>
        </div>

        <div class="table-container shadow-sm p-4 bg-white rounded-4">
            <h5 style="color: #1b2559; font-weight: 700;" class="mb-4">Recent Incidents</h5>
            <div class="table-responsive">
                <table class="table align-middle" id="myIncidentTable">
                    <thead>
                        <tr>
                            <th>Incident Name</th>
                            <th>Location</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Reported At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php foreach ($allIncidents as $row) {

                            $severityClass = '';

                            if ($row['severity'] == 'High') {
                                $severityClass = 'text-high';
                            } elseif ($row['severity'] == 'Medium') {
                                $severityClass = 'text-medium';
                            } elseif ($row['severity'] == 'Low') {
                                $severityClass = 'text-low';
                            }

                            $statusClass = '';

                            if ($row['status'] == 'In Progress') {
                                $statusClass = 'text-in-progress';
                            } elseif ($row['status'] == 'Investigating') {
                                $statusClass = 'text-investigating';
                            } elseif ($row['status'] == 'Resolved') {
                                $statusClass = 'text-resolved';
                            }

                        ?>

                            <tr>

                                <td class="incident-name" style="font-weight:700;">
                                    <?php echo $row['incident_name']; ?>
                                </td>

                                <td class="incident-location">
                                    <?php echo $row['location']; ?>
                                </td>

                                <td class="incident-severity status-text <?php echo $severityClass; ?>">
                                    <?php echo $row['severity']; ?>
                                </td>

                                <td class="incident-status status-text <?php echo $statusClass; ?>">
                                    <?php echo $row['status']; ?>
                                </td>

                                <td class="incident-date" data-date="<?php echo date('Y-m-d', strtotime($row['reported_at'])); ?>">>
                                    <?php echo date("M d, Y h:i A", strtotime($row['reported_at'])); ?>

                                </td>

                                <td>

                                    <button
                                        class="btn btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#viewIncidentModal"
                                        data-id="<?php echo $row['id']; ?>"
                                        data-description="<?php echo htmlspecialchars($row['description']); ?>"
                                        data-title="<?php echo htmlspecialchars($row['incident_name']); ?>">
                                        <i class="fa fa-eye text-muted"></i>
                                    </button>

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