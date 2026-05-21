<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// error_reporting(E_ALL);
session_start();
// echo "PAGE STARTED";
// exit;
// echo "1<br>";
require_once("class/hospital.class.php");
// echo "2<br>";
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
// echo "3<br>";

$hospitalObj = new hospital_dashboard();
// echo "4<br>";
$organization_id = $_SESSION['org_id'];
// echo "ORG ID = " . $organization_id . "<br>";
$hospitalData = $hospitalObj->getHospitalByOrganization($organization_id);
// echo "<pre>";
// print_r($hospitalData);
// echo "5<br>";
// exit;
$hospital_id = $hospitalData['id'];
// echo "6<br>";
$hospital = $hospitalObj->getHospitalData($hospital_id);
// echo "7<br>";
$stats = $hospitalObj->getTodayStats($hospital_id);
// echo "8<br>";
$teams = $hospitalObj->getTeams($hospital_id);
// echo "9<br>";
$transfers = $hospitalObj->getTransfers($hospital_id);
$demographics = $hospitalObj->getDemographics($hospital_id);
// echo "10<br>";
// exit
$allHospitals = $hospitalObj->getAllHospitals();
// $memcount=$hospitalObj->memcount();

// $members = $hospitalObj->getTeamMembers($team['id']);

?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <?php include('includes/header.php'); ?>
    <style>
        .action-btn {
            border: none;
            background: transparent;
            color: #3a3939;
            padding: 2px;
            border-radius: 5px;
            transition: all 0.2s ease;
            font-size: 0.9rem;
            margin: 0 2px;
        }

        .action-btn:hover {
            background-color: #f4f7fe;
            color: #4318ff;
        }

        .action-btn.btn-delete:hover {
            background-color: #fff5f5;
            color: #ee5d50;
        }

        .action-btn.btn-view:hover {
            background-color: #f0fdf4;
            color: #05cd99;
        }

        .dataTables_paginate .paginate_button {
            border-radius: 10px !important;
            margin: 0 3px;
        }

        .dataTables_paginate .paginate_button.current {
            background: #198754 !important;
            color: white !important;
            border: none !important;
        }

        .view-team-btn {
            border: none;
            background: transparent;
            color: #5c6f91;
            font-size: 0.92rem;
            font-weight: 500;
            padding: 0;
        }

        .view-team-btn:hover {
            color: #198754;
        }

        #teamsTable td,
        #teamsTable th {
            padding: 14px 12px;
            vertical-align: middle;
        }

        #teamsTable th:last-child,
        #teamsTable td:last-child {
            width: 90px;
            white-space: nowrap;
        }

        .card {
            border-radius: 20px;
        }

        .thc {
            font-weight: 600;
            color: #374151;
        }

        .thc2 {
            font-weight: 700;
            color: #111827;
        }
    </style>

</head>
<div class="modal fade" id="addTeamModal" tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 shadow rounded-4">

            <div class="modal-header border-0">

                <h5 class="fw-bold mb-0">
                    Add Response Team
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <div class="row g-3">

                    <div class="col-md-6">

                        <label class="small fw-bold mb-2">
                            Team Name
                        </label>

                        <input type="text" class="form-control" id="team_name">

                    </div>

                    <div class="col-md-6">

                        <label class="small fw-bold mb-2">
                            Status
                        </label>

                        <select class="form-select" id="team_status">

                            <option value="Available">
                                Available
                            </option>

                            <option value="On Mission">
                                On Mission
                            </option>

                            <option value="Unavailable">
                                Unavailable
                            </option>

                        </select>

                    </div>

                    <div class="col-12">

                        <label class="small fw-bold mb-2">
                            Current Location
                        </label>

                        <input type="text" class="form-control" id="current_location">

                    </div>

                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <h6 class="fw-bold mb-0">
                        Team Members
                    </h6>

                    <button type="button" class="btn btn-sm btn-outline-success" id="addMemberFieldBtn">
                        <i class="fa-solid fa-plus me-1"></i>
                        Add Member
                    </button>

                </div>

                <div id="membersContainer">

                </div>

            </div>

            <div class="modal-footer border-0">

                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="button" class="btn btn-success px-4" id="saveTeamBtn" data-hospital-id="<?= $hospital['id'] ?>">
                    Save Team
                </button>
            </div>
        </div>
    </div>

</div>
<div class="modal fade" id="addMemberModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow rounded-4">

            <div class="modal-header border-0">

                <h6 class="fw-bold mb-0">
                    Add Member
                </h6>

                <button type="button" class="btn-close" data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <input type="hidden" id="add_member_team_id">

                <div class="mb-3">

                    <label class="small fw-bold mb-2">
                        Member Name
                    </label>

                    <input type="text" class="form-control" id="add_member_name">

                </div>

                <div>

                    <label class="small fw-bold mb-2">
                        Role
                    </label>

                    <input type="text" class="form-control" id="add_member_role">

                </div>

            </div>

            <div class="modal-footer border-0">

                <button class="btn btn-success" id="saveMemberBtn">
                    Save Member
                </button>

            </div>

        </div>

    </div>

</div>
<div class="modal fade" id="addTransferModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow rounded-4">

            <div class="modal-header border-0">

                <h5 class="fw-bold mb-0">
                    New Transfer Request
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <form id="addTransferForm">

                    <input type="hidden" name="hospital_id" value="<?= $hospital['id'] ?>">

                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Destination Hospital
                        </label>

                        <select class="form-select" name="destination_organization_id" required>

                            <option value="">
                                Select Hospital
                            </option>

                            <?php foreach ($allHospitals as $hos): ?>

                                <?php if ($hos['id'] != $hospital['organization_id']): ?>

                                    <option value="<?= $hos['id'] ?>">
                                        <?= $hos['name'] ?>
                                    </option>

                                <?php endif; ?>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Patients Count
                        </label>
                        <input type="number" class="form-control" name="patients_count" min="1" required>
                    </div>

                    <button type="submit" class="btn btn-success w-100 rounded-3">
                        Submit Transfer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<body>

    <?php include('includes/sidebar.php'); ?>
    <?php include('includes/nav.php'); ?>

    <div class="main-content">
        <div class="container-fluid">

            <div class="row g-3 mb-4 mt-0">
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm p-3 rounded-4" style="background-color: #ffff;">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px; background-color: #2d5a27; color: white;">
                                <i class="fa fa-users fs-6"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold" style="font-size: 0.8rem; color: #2d5a27;">Total Patients</h6>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <h3 class="fw-bold mb-0" style="color: #1a3317; font-size: 1.5rem;"><?= $stats['total_patients'] ?? 0 ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card border-0 shadow-sm p-3 rounded-4" style="background-color: #ffff;">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px; background-color: #a52a2a; color: white;">
                                <i class="fa fa-heartbeat fs-6"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold" style="font-size: 0.8rem; color: #a52a2a;">Critical Cases</h6>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <h3 class="fw-bold mb-0" style="color: #5c1818; font-size: 1.5rem;"><?= $stats['critical_cases'] ?? 0 ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card border-0 shadow-sm p-3 rounded-4" style="background-color: #ffff;">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px; background-color: #4a6fa5; color: white;">
                                <i class="fa fa-bed fs-6"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold" style="font-size: 0.8rem; color: #4a6fa5;">Available Beds</h6>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <h3 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.3rem;"><?= $hospital['available_beds'] ?? 0 ?><small class="text-muted" style="font-size: 0.8rem;">/ <?= $hospital['total_beds'] ?? 0 ?></small></h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card border-0 shadow-sm p-3 rounded-4" style="background-color: #ffff;">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px; background-color: #6b46c1; color: white;">
                                <i class="fa fa-user-md fs-6"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold" style="font-size: 0.8rem; color: #6b46c1;">ICU Beds</h6>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <h3 class="fw-bold mb-0" style="color: #322659; font-size: 1.3rem;"><?= $hospital['available_icu_beds'] ?? 0 ?> <small class="text-muted" style="font-size: 0.8rem;">/ <?= $hospital['icu_beds'] ?? 0 ?></small></h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card border-0 shadow-sm p-3 rounded-4" style="background-color: #ffff;">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px; background-color: #c05621; color: white;">
                                <i class="fa fa-user-nurse fs-6"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold" style="font-size: 0.8rem; color: #c05621;">Staff On Duty</h6>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <h3 class="fw-bold mb-0" style="color: #7b341e; font-size: 1.5rem;"><?= $hospital['staff_on_duty'] ?? 0 ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card border-0 shadow-sm p-3 rounded-4" style="background-color: #ffff;">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px; background-color: #38a169; color: white;">
                                <i class="fa fa-ambulance fs-6"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold" style="font-size: 0.8rem; color: #276749;">Ambulances</h6>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <h3 class="fw-bold mb-0" style="color: #1c4532; font-size: 1.5rem;"><?= $hospital['ambulances'] ?? 0 ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4 ">

                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm p-3 rounded-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <div>
                                    <h5 class="fw-bold mb-0">Ambulance Teams</h5>
                                    <small class="text-muted">
                                        <?= count($teams) ?> Active Teams
                                    </small>
                                </div>
                            </div>
                            <!-- <button class="btn btn-outline-success btn-sm px-3">+ Add Team</button> -->
                            <button class="btn btn-outline-success btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addTeamModal">
                                + Add Team
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table id="teamsTable" class="table align-middle" style="width:100%">
                                <thead>
                                    <tr>
                                        <th class="fw-semibold">Team Name</th>
                                        <th class="fw-semibold">Status</th>
                                        <th class="fw-semibold">Location</th>
                                        <th class="fw-semibold">Members</th>
                                        <th class="text-center fw-semibold ">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($teams as $team): ?>
                                        <?php
                                        $members = $hospitalObj->getTeamMembers($team['id']);
                                        ?>
                                        <tr class="team-row" data-id="<?= $team['id'] ?>">
                                            <td>
                                                <div class="team-view team-name-text">
                                                    <?= $team['team_name'] ?>
                                                </div>

                                                <input type="text" class="form-control form-control-sm team-edit-input team_name_input d-none" value="<?= $team['team_name'] ?>">
                                            </td>

                                            <td>
                                                <div class="team-view team-status-text">
                                                    <?= $team['status'] ?>
                                                </div>

                                                <select class="form-select form-select-sm team-edit-input team_status_input d-none">

                                                    <option value="Available"
                                                        <?= $team['status'] == "Available" ? "selected" : "" ?>>
                                                        Available
                                                    </option>

                                                    <option value="On Mission"
                                                        <?= $team['status'] == "On Mission" ? "selected" : "" ?>>
                                                        On Mission
                                                    </option>

                                                    <option value="Unavailable"
                                                        <?= $team['status'] == "Unavailable" ? "selected" : "" ?>>
                                                        Unavailable
                                                    </option>

                                                </select>

                                            </td>

                                            <td>

                                                <div class="team-view team-location-text"> <?= $team['current_location'] ?>
                                                </div>

                                                <input type="text" class="form-control form-control-sm team-edit-input team_location_input d-none" value="<?= $team['current_location'] ?>">
                                            </td>

                                            <td>

                                                <div class="d-flex align-items-center gap-2">

                                                    <button class="btn btn-sm btn-light rounded-pill px-3 viewMembersBtn"
                                                        data-id="<?= $team['id'] ?>"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#membersModal<?= $team['id'] ?>">

                                                        <i class="fa-solid fa-users me-1 text-secondary"></i>
                                                        <span class="text-secondary">View Team</span>

                                                    </button>

                                                </div>

                                            </td>

                                            <td class="text-center">
                                                <div class="team-actions-view">
                                                    <button class="action-btn btn-view editTeamBtn"
                                                        data-id="<?= $team['id'] ?>">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>

                                                    <button class="action-btn btn-delete deleteTeamBtn"
                                                        data-id="<?= $team['id'] ?>">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </div>
                                                <div class="team-actions-edit d-none">

                                                    <button class="btn btn-success btn-sm saveTeamInlineBtn">
                                                        <i class="fa-solid fa-check"></i>
                                                    </button>

                                                    <button class="btn btn-secondary btn-sm cancelTeamInlineBtn">
                                                        <i class="fa-solid fa-xmark"></i>

                                                    </button>

                                                </div>

                                            </td>

                                        </tr>

                                        <div class="modal fade" id="membersModal<?= $team['id'] ?>" tabindex="-1" aria-hidden="true">

                                            <div class="modal-dialog modal-dialog-centered">

                                                <div class="modal-content border-0 shadow rounded-4">

                                                    <div class="modal-header border-0 pb-0">

                                                        <div>
                                                            <h6 class="fw-bold mb-0">
                                                                Team Members
                                                            </h6>
                                                            <?php
                                                            $memcount = $hospitalObj->memcount($team['id']);
                                                            ?>
                                                            <small class="text-muted">
                                                                <?= $memcount['total'] ?>
                                                                Members
                                                            </small>
                                                        </div>
                                                        <div class="d-flex align-items-center gap-2">

                                                            <button class="btn btn-sm btn-outline-success rounded-pill px-3 addInlineMemberBtn" data-team-id="<?= $team['id'] ?>">
                                                                <i class="fa-solid fa-plus me-1"></i>
                                                                Add
                                                            </button>

                                                            <button type="button" class="btn-close" data-bs-dismiss="modal">
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="modal-body pt-3">

                                                        <div class="membersList">

                                                            <?php if (!empty($members)): ?>

                                                                <?php foreach ($members as $member): ?>

                                                                    <div class="member-row border-bottom py-3" data-id="<?= $member['id'] ?>">

                                                                        <div class="member-view d-flex justify-content-between align-items-center">

                                                                            <div>
                                                                                <div class="fw-semibold member-name-text">
                                                                                    <?= $member['member_name'] ?>
                                                                                </div>

                                                                                <small class="text-muted member-role-text">
                                                                                    <?= $member['role'] ?>
                                                                                </small>
                                                                            </div>

                                                                            <div class="d-flex gap-2">

                                                                                <button class="btn btn-sm btn-light border rounded-circle editMemberBtn">
                                                                                    <i class="fa-solid fa-pen text-primary"></i>
                                                                                </button>

                                                                                <button class="btn btn-sm btn-light border rounded-circle deleteMemberBtn"
                                                                                    data-id="<?= $member['id'] ?>">
                                                                                    <i class="fa-solid fa-xmark text-danger"></i>

                                                                                </button>

                                                                            </div>

                                                                        </div>

                                                                        <div class="member-edit d-none">

                                                                            <div class="row g-2 align-items-center">

                                                                                <div class="col-md-5">

                                                                                    <input type="text" class="form-control edit_member_name" value="<?= $member['member_name'] ?>">

                                                                                </div>

                                                                                <div class="col-md-5">

                                                                                    <input type="text" class="form-control edit_member_role" value="<?= $member['role'] ?>">

                                                                                </div>

                                                                                <div class="col-md-2 d-flex gap-1">

                                                                                    <button class="btn btn-success saveInlineMemberBtn w-100">

                                                                                        <i class="fa-solid fa-check"></i>

                                                                                    </button>

                                                                                    <button class="btn btn-light cancelEditBtn w-100">

                                                                                        <i class="fa-solid fa-xmark"></i>

                                                                                    </button>

                                                                                </div>

                                                                            </div>

                                                                        </div>

                                                                    </div>

                                                                <?php endforeach; ?>

                                                            <?php endif; ?>

                                                        </div>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    <?php endforeach; ?>

                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <div class="col-lg-4">

                    <div class="card border-0 shadow-sm p-4 rounded-4 h-100">

                        <div class="d-flex align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Hospital Status Update</h5>
                        </div>

                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">

                            <span class="thc ">Current Status</span>

                            <div class="dropdown">
                                <span id="hospital_status_display" class="small fw-bold dropdown-toggle cursor-pointer d-flex align-items-center text-danger" data-bs-toggle="dropdown">
                                    <div style="width: 3px; height: 14px; background-color: #dc3545; border-radius: 2px; margin-right: 8px;"></div>
                                    <span id="hospital_status_text">
                                        <?= $hospital['hospital_status'] ?>
                                    </span>
                                </span>

                                <ul class="dropdown-menu shadow border-0 rounded-3">

                                    <li>
                                        <a class="dropdown-item small hospital-status-option" data-value="Safe" href="#">
                                            Safe
                                        </a>
                                    </li>

                                    <li>
                                        <a class="dropdown-item small hospital-status-option" data-value="Warning" href="#">
                                            Warning
                                        </a>
                                    </li>

                                    <li>
                                        <a class="dropdown-item small hospital-status-option" data-value="Dangerous" href="#">
                                            Dangerous
                                        </a>
                                    </li>

                                </ul>

                            </div>

                        </div>

                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">

                            <span class="thc  ">Infrastructure</span>

                            <div class="dropdown">
                                <span class="small fw-bold dropdown-toggle cursor-pointer d-flex align-items-center text-warning"
                                    data-bs-toggle="dropdown">
                                    <div style="width: 3px; height: 14px; background-color: #ffc107; border-radius: 2px; margin-right: 8px;"></div>
                                    <span id="infrastructure_status_text">
                                        <?= $hospital['infrastructure_status'] ?>
                                    </span>
                                </span>

                                <ul class="dropdown-menu shadow border-0 rounded-3">
                                    <li><a class="dropdown-item small infrastructure-option" data-value="Intact" href="#">Intact</a></li>
                                    <li><a class="dropdown-item small infrastructure-option" data-value="Minor Damage" href="#">Minor Damage</a></li>
                                    <li><a class="dropdown-item small infrastructure-option" data-value="Partially Damaged" href="#">Partially Damaged</a></li>
                                    <li><a class="dropdown-item small infrastructure-option" data-value="Destroyed" href="#">Destroyed</a></li>
                                </ul>

                            </div>

                        </div>

                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">

                            <span class="thc  ">Power Supply</span>

                            <div class="dropdown">

                                <span class="small fw-bold dropdown-toggle cursor-pointer d-flex align-items-center text-warning"
                                    data-bs-toggle="dropdown">

                                    <div style="width: 3px; height: 14px; background-color: #ffc107; border-radius: 2px; margin-right: 8px;"></div>

                                    <span id="power_status_text">
                                        <?= $hospital['power_status'] ?>
                                    </span>

                                </span>

                                <ul class="dropdown-menu shadow border-0 rounded-3">
                                    <li><a class="dropdown-item small power-option" data-value="Stable" href="#">Stable</a></li>
                                    <li><a class="dropdown-item small power-option" data-value="Unstable" href="#">Unstable</a></li>
                                    <li><a class="dropdown-item small power-option" data-value="Offline" href="#">Offline</a></li>

                                </ul>

                            </div>

                        </div>

                        <!-- Water -->
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">

                            <span class="thc ">Water Supply</span>

                            <div class="dropdown">
                                <span class="small fw-bold dropdown-toggle cursor-pointer d-flex align-items-center text-success"
                                    data-bs-toggle="dropdown">

                                    <div style="width: 3px; height: 14px; background-color: #198754; border-radius: 2px; margin-right: 8px;"></div>

                                    <span id="water_status_text">
                                        <?= $hospital['water_status'] ?>
                                    </span>

                                </span>

                                <ul class="dropdown-menu shadow border-0 rounded-3">
                                    <li><a class="dropdown-item small water-option" data-value="Available" href="#">Available</a></li>
                                    <li><a class="dropdown-item small water-option" data-value="Limited" href="#">Limited</a></li>
                                    <li><a class="dropdown-item small water-option" data-value="Unavailable" href="#">Unavailable</a></li>
                                </ul>

                            </div>

                        </div>

                        <button class="btn btn-outline-success w-100 py-2 fw-bold border-2 rounded-3 mt-2" id="saveHospitalStatusBtn" data-hospital-id="<?= $hospital['id'] ?>">
                            <i class="fa-solid fa-check-double me-2"></i>
                            Save Status Updates
                        </button>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm p-4 rounded-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center">
                                <h5 class="fw-bold mb-0">Recent Transfers</h5>
                            </div>

                            <button class="btn btn-outline-success btn-sm px-3 rounded-3" data-bs-toggle="modal" data-bs-target="#addTransferModal">
                                + New Request
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table id="transfersTable" class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr class="text-muted small">
                                        <th class="border-0 fw-bold">Destination Hospital</th>
                                        <th class="border-0 fw-bold">Request Time</th>
                                        <th class="border-0 fw-bold">Patients</th>
                                        <th class="border-0 fw-bold">Status</th>
                                        <th class="text-center">Actions</t>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transfers as $transfer): ?>
                                        <tr>
                                            <td>
                                                <span class="view-mode">
                                                    <?= $transfer['destination_name'] ?>
                                                </span>
                                                <select class="form-select form-select-sm edit-mode d-none destinationInput">
                                                    <?php foreach ($allHospitals as $hospital): ?>

                                                        <option
                                                            value="<?= $hospital['id'] ?>"
                                                            <?= $hospital['id'] == $transfer['destination_organization_id'] ? 'selected' : '' ?>>

                                                            <?= $hospital['name'] ?>

                                                        </option>

                                                    <?php endforeach; ?>

                                                </select>

                                            </td>

                                            <td>
                                                <?= $transfer['request_time'] ?>
                                            </td>

                                            <td>

                                                <span class="view-mode">
                                                    <?= $transfer['patients_count'] ?> Patients
                                                </span>

                                                <input type="number" class="form-control form-control-sm edit-mode d-none patientsInput" value="<?= $transfer['patients_count'] ?>">
                                            </td>

                                            <td>
                                                <span class="view-mode">
                                                    <?= $transfer['status'] ?>
                                                </span>

                                                <select class="form-select form-select-sm edit-mode d-none statusInput">

                                                    <option value="Pending"
                                                        <?= $transfer['status'] == 'Pending' ? 'selected' : '' ?>>
                                                        Pending
                                                    </option>

                                                    <option value="Completed"
                                                        <?= $transfer['status'] == 'Completed' ? 'selected' : '' ?>>
                                                        Completed
                                                    </option>

                                                    <option value="Rejected"
                                                        <?= $transfer['status'] == 'Rejected' ? 'selected' : '' ?>>
                                                        Rejected
                                                    </option>

                                                </select>

                                            </td>

                                            <td class="text-center">

                                                <div class="team-actions-view">

                                                    <button class="action-btn btn-view edittransBtn">

                                                        <i class="fa-solid fa-pen-to-square"></i>

                                                    </button>

                                                    <button class="action-btn btn-delete deletetransBtn" data-id="<?= $transfer['id'] ?>">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>

                                                </div>

                                                <div class="team-actions-edit d-none">

                                                    <button class="btn btn-success btn-sm savetransfBtn" data-id="<?= $transfer['id'] ?>">
                                                        <i class="fa-solid fa-check"></i>

                                                    </button>

                                                    <button class="btn btn-secondary btn-sm canceltransferBtn">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>

                                                </div>

                                            </td>

                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 h-100">

                        <div class="d-flex justify-content-between align-items-start mb-4">

                            <div>
                                <h5 class="fw-bold mb-1">
                                    Crisis Insights
                                </h5>

                                <small class="text-muted">
                                    Emergency statistics overview
                                </small>
                            </div>

                            <div class="d-flex align-items-center gap-2">

                                <button class="btn btn-light rounded-circle border-0 shadow-sm" id="editInsightsBtn" style="width:42px;height:42px;">
                                    <i class="fa-solid fa-pen text-success"></i>
                                </button>

                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 52px;height: 52px;background: #fdeaea; color: #dc3545; ">
                                    <i class="fa-solid fa-chart-line"></i>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <button class="btn btn-sm rounded-pill px-3 filterInsightBtn active" data-filter="all" style="background: #198754; color: white; border: none;">
                                All
                            </button>
                            <button class="btn btn-sm btn-light rounded-pill px-3 filterInsightBtn" data-filter="children">
                                Children
                            </button>
                            <button class="btn btn-sm btn-light rounded-pill px-3 filterInsightBtn" data-filter="male">
                                Male
                            </button>
                            <button class="btn btn-sm btn-light rounded-pill px-3 filterInsightBtn" data-filter="female">
                                Female
                            </button>
                        </div>

                        <div class="insight-item d-flex justify-content-between align-items-center py-3 border-bottom">

                            <div class="d-flex align-items-center">

                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; background: #fdeaea;color: #dc3545;">
                                    <i class="fa-solid fa-heart-crack"></i>
                                </div>

                                <div>
                                    <div class="small text-muted">
                                        Martyrs
                                    </div>

                                    <div class="fw-bold fs-2 insight-view" id="martyrsCount">
                                        <?= ($demographics['male_martyrs'] ?? 0) + ($demographics['female_martyrs'] ?? 0) + ($demographics['children_martyrs'] ?? 0) ?>
                                    </div>

                                    <input type="number" class="form-control insight-edit d-none" id="martyrsInput"
                                        value="<?= ($demographics['male_martyrs'] ?? 0) + ($demographics['female_martyrs'] ?? 0) + ($demographics['children_martyrs'] ?? 0) ?>">
                                </div>

                            </div>

                            <span class="badge rounded-pill px-3 py-2" style="background: #fdeaea; color: #dc3545;">
                                High
                            </span>

                        </div>

                        <div class="insight-item d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 48px; height: 48px; background: #fff4da; color: #ffb300;">
                                    <i class="fa-solid fa-user-injured"></i>
                                </div>

                                <div>
                                    <div class="small text-muted">
                                        Injured
                                    </div>
                                    <div class="fw-bold fs-2 insight-view" id="injuredCount">
                                        <?= ($demographics['male_injured'] ?? 0) + ($demographics['female_injured'] ?? 0) + ($demographics['children_injured'] ?? 0) ?>
                                    </div>
                                    <input type="number" class="form-control insight-edit d-none" id="injuredInput" value="<?= ($demographics['male_injured'] ?? 0) + ($demographics['female_injured'] ?? 0) + ($demographics['children_injured'] ?? 0) ?>">
                                </div>
                            </div>
                            <span class="badge rounded-pill px-3 py-2" style="background: #fff4da; color: #ffb300;">
                                Critical
                            </span>

                        </div>
                        <div id="insightActions" class="d-none mt-4 d-flex gap-2">
                            <button class="btn btn-success flex-grow-1" id="saveInsightsBtn">
                                <i class="fa-solid fa-check me-1"></i>
                                Save
                            </button>

                            <button class="btn btn-light border flex-grow-1" id="cancelInsightsBtn">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include('includes/footer.php'); ?>

    </div>
    <?php include('includes/script.php'); ?>

    <?php
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        echo "<script>
        Swal.fire({
            icon: '{$flash['icon']}',
            title: '{$flash['title']}',
            text: '{$flash['text']}',
            timer: {$flash['timer']},
            showConfirmButton: " . ($flash['showConfirmButton'] ? 'true' : 'false') . ",
            timerProgressBar: true
        }).then(() => {
            window.location.href = '{$flash['redirect']}';
        });
    </script>";
        unset($_SESSION['flash']);
    }
    ?>

    <script>
        let hospital_status = $("#hospital_status_text").text().trim();
        let infrastructure_status = $("#infrastructure_status_text").text().trim();
        let power_status = $("#power_status_text").text().trim();
        let water_status = $("#water_status_text").text().trim();

        function updateStatusColor(type, value) {

            let textElement = $("#" + type + "_text");

            let parent = textElement.closest(".dropdown-toggle");

            let line = parent.find("div");

            parent.removeClass(
                "text-danger text-warning text-success"
            );

            if (type == "hospital_status") {

                if (value == "Safe") {

                    parent.addClass("text-success");
                    line.css("background-color", "#198754");

                } else if (value == "Warning") {

                    parent.addClass("text-warning");
                    line.css("background-color", "#ffc107");

                } else {

                    parent.addClass("text-danger");
                    line.css("background-color", "#dc3545");

                }

            }

            if (type == "infrastructure_status") {

                if (value == "Intact") {

                    parent.addClass("text-success");
                    line.css("background-color", "#198754");

                } else if (value == "Minor Damage") {

                    parent.addClass("text-warning");
                    line.css("background-color", "#ffc107");

                } else if (value == "Partially Damaged") {

                    parent.addClass("text-warning");
                    line.css("background-color", "#fd7e14");

                } else {

                    parent.addClass("text-danger");
                    line.css("background-color", "#dc3545");

                }

            }

            if (type == "power_status") {

                if (value == "Stable") {

                    parent.addClass("text-success");
                    line.css("background-color", "#198754");

                } else if (value == "Unstable") {

                    parent.addClass("text-warning");
                    line.css("background-color", "#ffc107");

                } else {

                    parent.addClass("text-danger");
                    line.css("background-color", "#dc3545");

                }

            }

            if (type == "water_status") {

                if (value == "Available") {

                    parent.addClass("text-success");
                    line.css("background-color", "#198754");

                } else if (value == "Limited") {

                    parent.addClass("text-warning");
                    line.css("background-color", "#ffc107");

                } else {

                    parent.addClass("text-danger");
                    line.css("background-color", "#dc3545");

                }

            }

        }

        $(".hospital-status-option").click(function(e) {

            e.preventDefault();

            hospital_status = $(this).data("value");

            $("#hospital_status_text").text(hospital_status);

            updateStatusColor("hospital_status", hospital_status);

        });

        $(".infrastructure-option").click(function(e) {

            e.preventDefault();

            infrastructure_status = $(this).data("value");

            $("#infrastructure_status_text").text(infrastructure_status);

            updateStatusColor("infrastructure_status", infrastructure_status);

        });

        $(".power-option").click(function(e) {

            e.preventDefault();

            power_status = $(this).data("value");

            $("#power_status_text").text(power_status);

            updateStatusColor("power_status", power_status);

        });

        $(".water-option").click(function(e) {

            e.preventDefault();

            water_status = $(this).data("value");

            $("#water_status_text").text(water_status);

            updateStatusColor("water_status", water_status);

        });
        $("#saveHospitalStatusBtn").click(function() {

            let hospital_id = $(this).data("hospital-id");

            $.ajax({

                url: "actions/update_hospital_status.php",
                type: "POST",
                dataType: "json",

                data: {
                    hospital_id: hospital_id,
                    hospital_status: hospital_status,
                    infrastructure_status: infrastructure_status,
                    power_status: power_status,
                    water_status: water_status
                },

                success: function(response) {

                    if (response.status == "success") {

                        Swal.fire({
                            icon: "success",
                            title: "Updated Successfully",
                            timer: 1500,
                            showConfirmButton: false
                        });

                    } else {

                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: response.message
                        });

                    }

                }

            });

        });
    </script>
    <script>
        let memberIndex = 0;

        $("#addMemberFieldBtn").click(function() {

            memberIndex++;

            $("#membersContainer").append(`

        <div class="member-item border rounded-3 p-3 mb-3">

            <div class="row g-2">

                <div class="col-md-5">

                    <input
                        type="text"
                        class="form-control member_name"
                        placeholder="Member Name">

                </div>

                <div class="col-md-5">

                    <input
                        type="text"
                        class="form-control member_role"
                        placeholder="Role">

                </div>

                <div class="col-md-2">

                    <button
                        type="button"
                        class="btn btn-danger w-100 removeMemberBtn">

                        <i class="fa-solid fa-trash"></i>

                    </button>

                </div>

            </div>

        </div>
    `);

        });

        $(document).on("click", ".removeMemberBtn", function() {

            $(this).closest(".member-item").remove();

        });
    </script>
    <script>
        $("#saveTeamBtn").click(function() {

            let hospital_id = $(this).data("hospital-id");

            let team_name = $("#team_name").val();

            let status = $("#team_status").val();

            let current_location = $("#current_location").val();

            let members = [];

            $(".member-item").each(function() {

                let member_name = $(this).find(".member_name").val();

                let member_role = $(this).find(".member_role").val();

                members.push({
                    member_name: member_name,
                    role: member_role
                });

            });

            $.ajax({

                url: "actions/add_team.php",
                type: "POST",
                dataType: "json",

                data: {
                    hospital_id: hospital_id,
                    team_name: team_name,
                    status: status,
                    current_location: current_location,
                    members: JSON.stringify(members)
                },

                success: function(response) {

                    if (response.status == "success") {

                        Swal.fire({
                            icon: "success",
                            title: "Team Added Successfully",
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {

                            $("#addTeamModal").modal("hide");

                            setTimeout(() => {

                                location.reload();

                            }, 500);

                        });

                    }

                }

            });

        });
    </script>
    <script>
        $(document).on("click", ".deleteTeamBtn", function() {

            let team_id = $(this).data("id");

            Swal.fire({

                title: "Delete Team?",
                text: "All team members will also be deleted.",
                icon: "warning",

                showCancelButton: true,

                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",

                confirmButtonText: "Yes, Delete"

            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({

                        url: "actions/delete_team.php",
                        type: "POST",
                        dataType: "json",

                        data: {
                            team_id: team_id
                        },

                        success: function(response) {

                            if (response.status == "success") {

                                Swal.fire({
                                    icon: "success",
                                    title: "Deleted Successfully",
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {

                                    location.reload();

                                });

                            } else {

                                Swal.fire({
                                    icon: "error",
                                    title: "Error",
                                    text: response.message
                                });

                            }

                        }

                    });

                }

            });

        });
    </script>

    <script>
        $(document).on("click", ".addInlineMemberBtn", function() {

            let team_id = $(this).data("team-id");

            let html = `

        <div class="member-row border-bottom py-3 new-member-row">

            <div class="row g-2 align-items-center">

                <div class="col-md-5">

                    <input
                        type="text"
                        class="form-control new_member_name"
                        placeholder="Member Name">

                </div>

                <div class="col-md-5">

                    <input
                        type="text"
                        class="form-control new_member_role"
                        placeholder="Role">

                </div>

                <div class="col-md-2 d-flex gap-1">

                    <button
                        class="btn btn-success saveNewMemberBtn w-100"
                        data-team-id="${team_id}">

                        <i class="fa-solid fa-check"></i>

                    </button>

                    <button
                        class="btn btn-light cancelNewMemberBtn w-100">

                        <i class="fa-solid fa-xmark"></i>

                    </button>

                </div>

            </div>

        </div>

        `;

            $(this)
                .closest(".modal-content")
                .find(".membersList")
                .prepend(html);

        });

        $(document).on("click", ".cancelNewMemberBtn", function() {

            $(this).closest(".new-member-row").fadeOut(200, function() {

                $(this).remove();

            });

        });

        $(document).on("click", ".saveNewMemberBtn", function() {

            let row = $(this).closest(".new-member-row");

            let member_name = row.find(".new_member_name").val();

            let member_role = row.find(".new_member_role").val();

            let team_id = $(this).data("team-id");

            if (member_name == "" || member_role == "") {

                Swal.fire({
                    icon: "warning",
                    title: "Missing Information",
                    text: "Please fill all fields.",
                    confirmButtonColor: "#f59e0b"
                });

                return;
            }

            $.ajax({

                url: "actions/add_member.php",
                type: "POST",
                dataType: "json",

                data: {
                    team_id: team_id,
                    member_name: member_name,
                    role: member_role
                },

                success: function(response) {

                    if (response.status == "success") {

                        Swal.fire({
                            icon: "success",
                            title: "Member Added",
                            text: "Team member added successfully.",
                            confirmButtonColor: "#198754",
                            timer: 1700,
                            showConfirmButton: false
                        }).then(() => {

                            location.reload();

                        });

                    } else {

                        Swal.fire({
                            icon: "error",
                            title: "Add Failed",
                            text: response.message,
                            confirmButtonColor: "#dc3545"
                        });

                    }

                }

            });

        });

        $(document).on("click", ".editMemberBtn", function() {

            let row = $(this).closest(".member-row");

            row.find(".member-view").addClass("d-none");

            row.find(".member-edit").removeClass("d-none");

        });

        $(document).on("click", ".cancelEditBtn", function() {

            let row = $(this).closest(".member-row");

            row.find(".member-edit").addClass("d-none");

            row.find(".member-view").removeClass("d-none");

        });

        $(document).on("click", ".saveInlineMemberBtn", function() {

            let row = $(this).closest(".member-row");

            let member_id = row.data("id");

            let member_name = row.find(".edit_member_name").val();

            let member_role = row.find(".edit_member_role").val();

            $.ajax({

                url: "actions/update_member.php",
                type: "POST",
                dataType: "json",

                data: {
                    member_id: member_id,
                    member_name: member_name,
                    role: member_role
                },

                success: function(response) {

                    if (response.status == "success") {

                        Swal.fire({
                            icon: "success",
                            title: "Member Updated",
                            text: "Changes saved successfully.",
                            confirmButtonColor: "#198754",
                            timer: 1700,
                            showConfirmButton: false
                        }).then(() => {

                            location.reload();

                        });

                    } else {

                        Swal.fire({
                            icon: "error",
                            title: "Update Failed",
                            text: response.message,
                            confirmButtonColor: "#dc3545"
                        });

                    }

                }

            });

        });

        $(document).on("click", ".deleteMemberBtn", function() {

            let member_id = $(this).data("id");

            Swal.fire({

                title: "Delete Member?",
                text: "This action cannot be undone.",
                icon: "warning",

                showCancelButton: true,

                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",

                confirmButtonText: "Yes, Delete"

            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({

                        url: "actions/delete_member.php",

                        type: "POST",

                        dataType: "json",

                        data: {
                            member_id: member_id
                        },

                        success: function(response) {

                            if (response.status == "success") {

                                Swal.fire({
                                    icon: "success",
                                    title: "Member Deleted",
                                    text: "The member was removed successfully.",
                                    confirmButtonColor: "#198754",
                                    timer: 1700,
                                    showConfirmButton: false
                                }).then(() => {

                                    location.reload();

                                });

                            } else {

                                Swal.fire({
                                    icon: "error",
                                    title: "Delete Failed",
                                    text: response.message,
                                    confirmButtonColor: "#dc3545"
                                });

                            }

                        }

                    });

                }

            });

        });
    </script>

    <script>
        $(document).on("click", ".editTeamBtn", function() {

            let row = $(this).closest(".team-row");

            row.find(".team-view").addClass("d-none");

            row.find(".team-edit-input").removeClass("d-none");

            row.find(".team-actions-view").addClass("d-none");

            row.find(".team-actions-edit").removeClass("d-none");

        });

        $(document).on("click", ".cancelTeamInlineBtn", function() {

            let row = $(this).closest(".team-row");

            row.find(".team-view").removeClass("d-none");

            row.find(".team-edit-input").addClass("d-none");

            row.find(".team-actions-view").removeClass("d-none");

            row.find(".team-actions-edit").addClass("d-none");

        });

        $(document).on("click", ".saveTeamInlineBtn", function() {

            let row = $(this).closest(".team-row");

            let team_id = row.data("id");

            let team_name = row.find(".team_name_input").val();

            let status = row.find(".team_status_input").val();

            let current_location = row.find(".team_location_input").val();

            $.ajax({

                url: "actions/update_team.php",

                type: "POST",

                dataType: "json",

                data: {
                    team_id: team_id,
                    team_name: team_name,
                    status: status,
                    current_location: current_location
                },

                success: function(response) {

                    if (response.status == "success") {

                        Swal.fire({
                            icon: "success",
                            title: "Updated Successfully",
                            timer: 1200,
                            showConfirmButton: false
                        }).then(() => {

                            location.reload();

                        });

                    } else {

                        Swal.fire({
                            icon: "error",
                            title: "Update Failed",
                            text: response.message
                        });

                    }

                }

            });

        });
        $(document).on("submit", "#addTransferForm", function(e) {
            e.preventDefault();
            let formData = $(this).serialize();
            $.ajax({
                url: "actions/add_transfer.php",
                type: "POST",
                data: formData,
                dataType: "json",

                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Transfer Added",
                            text: "Transfer request submitted successfully",
                            timer: 1800,
                            showConfirmButton: false
                        });

                        $("#addTransferModal").modal("hide");

                        setTimeout(() => {
                            location.reload();
                        }, 1800);

                    } else {

                        Swal.fire({
                            icon: "error",
                            title: "Insert Failed"
                        });

                    }

                }

            });

        });
    </script>

    <script>
        $(document).on("click", ".deletetransBtn", function() {
            let transfer_id = $(this).data("id");
            Swal.fire({

                title: "Delete transfer?",
                text: "This action cannot be undone.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, Delete"

            }).then((result) => {

                if (result.isConfirmed) {
                    $.ajax({
                        url: "actions/delete_transfer.php",
                        type: "POST",

                        dataType: "json",

                        data: {
                            transfer_id: transfer_id
                        },

                        success: function(response) {
                            if (response.status == "success") {
                                Swal.fire({
                                    icon: "success",
                                    title: "Transfer Deleted",
                                    text: "The transfer was removed successfully.",
                                    confirmButtonColor: "#198754",
                                    timer: 1700,
                                    showConfirmButton: false
                                }).then(() => {

                                    location.reload();

                                });

                            } else {

                                Swal.fire({
                                    icon: "error",
                                    title: "Delete Failed",
                                    text: response.message,
                                    confirmButtonColor: "#dc3545"
                                });

                            }

                        }

                    });

                }

            });

        });
        $(document).on("click", ".edittransBtn", function() {

            let row = $(this).closest("tr");

            row.find(".view-mode").addClass("d-none");

            row.find(".edit-mode").removeClass("d-none");

            row.find(".team-actions-view").addClass("d-none");

            row.find(".team-actions-edit").removeClass("d-none");

        });
        $(document).on("click", ".canceltransferBtn", function() {

            let row = $(this).closest("tr");

            row.find(".view-mode").removeClass("d-none");

            row.find(".edit-mode").addClass("d-none");

            row.find(".team-actions-view").removeClass("d-none");

            row.find(".team-actions-edit").addClass("d-none");

        });
        $(document).on("click", ".savetransfBtn", function() {

            let row = $(this).closest("tr");

            let transfer_id = $(this).data("id");

            let destination_organization_id =
                row.find(".destinationInput").val();

            let patients_count =
                row.find(".patientsInput").val();

            let status =
                row.find(".statusInput").val();

            $.ajax({

                url: "actions/update_transfer.php",

                type: "POST",

                dataType: "json",

                data: {

                    transfer_id: transfer_id,
                    destination_organization_id: destination_organization_id,

                    patients_count: patients_count,
                    status: status

                },

                success: function(response) {

                    if (response.status == "success") {

                        Swal.fire({

                            icon: "success",
                            title: "Transfer Updated",
                            timer: 1500,
                            showConfirmButton: false

                        });

                        setTimeout(() => {

                            location.reload();

                        }, 1500);

                    }

                }

            });

        });
    </script>

    <script>
        const crisisData = {

            all: {

                martyrs: <?=
                            ($demographics['male_martyrs'] ?? 0)
                                +
                                ($demographics['female_martyrs'] ?? 0)
                                +
                                ($demographics['children_martyrs'] ?? 0)
                            ?>,

                injured: <?=
                            ($demographics['male_injured'] ?? 0)
                                +
                                ($demographics['female_injured'] ?? 0)
                                +
                                ($demographics['children_injured'] ?? 0)
                            ?>

            },

            children: {

                martyrs: <?= $demographics['children_martyrs'] ?? 0 ?>,

                injured: <?= $demographics['children_injured'] ?? 0 ?>

            },

            male: {

                martyrs: <?= $demographics['male_martyrs'] ?? 0 ?>,

                injured: <?= $demographics['male_injured'] ?? 0 ?>

            },

            female: {

                martyrs: <?= $demographics['female_martyrs'] ?? 0 ?>,

                injured: <?= $demographics['female_injured'] ?? 0 ?>

            }

        };

        let currentFilter = "all";

        $(document).on("click", ".filterInsightBtn", function() {

            $(".filterInsightBtn")
                .removeClass("active")
                .removeAttr("style")
                .addClass("btn-light");

            $(this)
                .removeClass("btn-light")
                .addClass("active")
                .css({
                    background: "#198754",
                    color: "white",
                    border: "none"
                });

            currentFilter = $(this).data("filter");

            $("#martyrsCount").text(
                crisisData[currentFilter].martyrs
            );

            $("#injuredCount").text(
                crisisData[currentFilter].injured
            );

            $("#martyrsInput").val(
                crisisData[currentFilter].martyrs
            );

            $("#injuredInput").val(
                crisisData[currentFilter].injured
            );


            if (currentFilter == "all") {

                $("#editInsightsBtn")
                    .prop("disabled", true)
                    .css({
                        opacity: "0.5",
                        cursor: "not-allowed"
                    });

            } else {

                $("#editInsightsBtn")
                    .prop("disabled", false)
                    .css({
                        opacity: "1",
                        cursor: "pointer"
                    });
            }
        });

        $("#editInsightsBtn").click(function() {

            $("#martyrsInput").val(
                crisisData[currentFilter].martyrs
            );

            $("#injuredInput").val(
                crisisData[currentFilter].injured
            );

            $(".insight-view").addClass("d-none");

            $(".insight-edit").removeClass("d-none");

            $("#insightActions").removeClass("d-none");

        });

        $("#cancelInsightsBtn").click(function() {

            $(".insight-view").removeClass("d-none");

            $(".insight-edit").addClass("d-none");

            $("#insightActions").addClass("d-none");

        });

        $("#saveInsightsBtn").click(function() {

            let martyrs = parseInt($("#martyrsInput").val()) || 0;

            let injured = parseInt($("#injuredInput").val()) || 0;

            $.ajax({

                url: "actions/update_demographics.php",

                type: "POST",

                dataType: "json",

                data: {

                    hospital_id: <?= (int)$hospital_id ?>,

                    filter: currentFilter,

                    martyrs: martyrs,

                    injured: injured

                },

                success: function(response) {
                    if (response.status == "success") {
                        Swal.fire({

                            icon: "success",

                            title: "Updated Successfully",

                            text: response.message,

                            timer: 1800,

                            showConfirmButton: false

                        });

                        window.location.reload();

                    } else {

                        Swal.fire({

                            icon: "error",

                            title: "Error",

                            text: response.message

                        });

                    }

                },

                error: function() {
                    Swal.fire({

                        icon: "error",

                        title: "Server Error",

                        text: "Something went wrong"

                    });

                }

            });

        });
        if (currentFilter == "all") {

            $("#editInsightsBtn")
                .prop("disabled", true)
                .css({
                    opacity: "0.5",
                    cursor: "not-allowed"
                });

        }
    </script>
</body>

</html>
