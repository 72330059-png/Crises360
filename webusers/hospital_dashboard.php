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
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include('includes/header.php'); ?>
    <style>
        .btn-action {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-edit-soft {
            color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.08);
        }

        .btn-edit-soft:hover {
            background-color: #0d6efd;
            color: white;
        }

        .btn-delete-soft {
            color: #dc3545;
            background-color: rgba(220, 53, 69, 0.08);
        }

        .btn-delete-soft:hover {
            background-color: #dc3545;
            color: white;
        }
    </style>

</head>

<body>

    <?php include('includes/sidebar.php'); ?>
    <?php include('includes/nav.php'); ?>

    <div class="modal fade" id="membersModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Team Members</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Dr. Hassan Khaled <span class="badge bg-primary rounded-pill">Leader</span>
                        </li>
                        <li class="list-group-item px-0">Nurse Sara Ali</li>
                        <li class="list-group-item px-0">Paramedic Ali Hamdan</li>
                        <li class="list-group-item px-0">Ahmad Mansour</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card p-3 mt-4">

            <h5 class="mb-3">Today's Summary</h5>

            <div class="row text-center g-3">

                <div class="col-md-3">
                    <div class="summary-box">
                        <h6>Incoming Patients</h6>
                        <h4>124</h4>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="summary-box">
                        <h6>Critical Cases</h6>
                        <h4>18</h4>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="summary-box">
                        <h6>Discharged</h6>
                        <h4>15</h4>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="summary-box">
                        <h6>Transferred Out</h6>
                        <h4>6</h4>
                    </div>
                </div>

            </div>

            <div class="d-flex justify-content-between align-items-center mt-4 report-box p-3">
                <div>
                    <h6 class="mb-1">Generate Full Report (Excel / Word)</h6>
                    <small class="text-muted">Includes all data, teams, capacity, and missions</small>
                </div>

                <button class="btn btn-success">Generate Report</button>
            </div>

        </div>
    </div>

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
                                <small class="text-muted" style="font-size: 0.7rem;">(Today)</small>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <h3 class="fw-bold mb-0" style="color: #1a3317; font-size: 1.5rem;">124</h3>
                            <i class="fa fa-pencil text-muted mb-1" style="font-size: 0.75rem; cursor: pointer;"></i>
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
                            <h3 class="fw-bold mb-0" style="color: #5c1818; font-size: 1.5rem;">18</h3>
                            <i class="fa fa-pencil text-muted mb-1" style="font-size: 0.75rem; cursor: pointer;"></i>
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
                            <h3 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.3rem;">56 <small class="text-muted" style="font-size: 0.8rem;">/ 120</small></h3>
                            <i class="fa fa-pencil text-muted mb-1" style="font-size: 0.75rem; cursor: pointer;"></i>
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
                            <h3 class="fw-bold mb-0" style="color: #322659; font-size: 1.3rem;">12 <small class="text-muted" style="font-size: 0.8rem;">/ 40</small></h3>
                            <i class="fa fa-pencil text-muted mb-1" style="font-size: 0.75rem; cursor: pointer;"></i>
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
                            <h3 class="fw-bold mb-0" style="color: #7b341e; font-size: 1.5rem;">78</h3>
                            <i class="fa fa-pencil text-muted mb-1" style="font-size: 0.75rem; cursor: pointer;"></i>
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
                            <h3 class="fw-bold mb-0" style="color: #1c4532; font-size: 1.5rem;">7</h3>
                            <i class="fa fa-pencil text-muted mb-1" style="font-size: 0.75rem; cursor: pointer;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            <div>
                                <h5 class="fw-bold mb-0">Response Teams (Ambulances)</h5>
                                <small class="text-muted">7 Active Teams</small>
                            </div>
                        </div>
                        <button class="btn btn-outline-success btn-sm px-3">+ Add Team</button>
                    </div>

                    <div class="table-responsive">
                        <table id="teamsTable" class="table align-middle custom-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Team ID</th>
                                    <th>Status</th>
                                    <th>Mission / Location</th>
                                    <th>Personnel</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-bold">AMB-01</td>
                                    <td><span class="badge badge-soft-success">On Mission</span></td>
                                    <td>
                                        <div class="fw-bold small">Transport to RHUH</div>
                                        <small class="text-muted">Beirut, Downtown</small>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#membersModal">
                                            <i class="fa-solid fa-users me-1"></i> View Team
                                        </button>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <button class="btn-action btn-edit-soft" title="Edit">
                                                <i class="fa-solid fa-pen-to-square" style="font-size: 0.85rem;"></i>
                                            </button>
                                            <button class="btn-action btn-delete-soft" title="Delete">
                                                <i class="fa-solid fa-trash-can" style="font-size: 0.85rem;"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">AMB-01</td>
                                    <td><span class="badge badge-soft-success">On Mission</span></td>
                                    <td>
                                        <div class="fw-bold small">Transport to RHUH</div>
                                        <small class="text-muted">Beirut, Downtown</small>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#membersModal">
                                            <i class="fa-solid fa-users me-1"></i> View Team
                                        </button>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <button class="btn btn-link text-primary p-1">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button class="btn btn-link text-danger p-1">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">AMB-01</td>
                                    <td><span class="badge badge-soft-success">On Mission</span></td>
                                    <td>
                                        <div class="fw-bold small">Transport to RHUH</div>
                                        <small class="text-muted">Beirut, Downtown</small>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#membersModal">
                                            <i class="fa-solid fa-users me-1"></i> View Team
                                        </button>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <button class="btn btn-link text-primary p-1">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button class="btn btn-link text-danger p-1">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">AMB-01</td>
                                    <td><span class="badge badge-soft-success">On Mission</span></td>
                                    <td>
                                        <div class="fw-bold small">Transport to RHUH</div>
                                        <small class="text-muted">Beirut, Downtown</small>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#membersModal">
                                            <i class="fa-solid fa-users me-1"></i> View Team
                                        </button>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <button class="btn btn-link text-primary p-1">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button class="btn btn-link text-danger p-1">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">AMB-01</td>
                                    <td><span class="badge badge-soft-success">On Mission</span></td>
                                    <td>
                                        <div class="fw-bold small">Transport to RHUH</div>
                                        <small class="text-muted">Beirut, Downtown</small>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#membersModal">
                                            <i class="fa-solid fa-users me-1"></i> View Team
                                        </button>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <button class="btn btn-link text-primary p-1">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button class="btn btn-link text-danger p-1">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">AMB-01</td>
                                    <td><span class="badge badge-soft-success">On Mission</span></td>
                                    <td>
                                        <div class="fw-bold small">Transport to RHUH</div>
                                        <small class="text-muted">Beirut, Downtown</small>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#membersModal">
                                            <i class="fa-solid fa-users me-1"></i> View Team
                                        </button>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <button class="btn btn-link text-primary p-1">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button class="btn btn-link text-danger p-1">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- TRANSFERS -->
                <div class="card border-0 shadow-sm p-4 mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fa-solid fa-clock-rotate-left text-success me-2 fs-5"></i>
                            <h5 class="fw-bold mb-0">Recent Transfers</h5>
                        </div>
                        <button class="btn btn-outline-success btn-sm px-3 rounded-3">+ New Request</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr class="text-muted small">
                                    <th class="border-0 fw-bold">DESTINATION HOSPITAL</th>
                                    <th class="border-0 fw-bold">REQUEST TIME</th>
                                    <th class="border-0 fw-bold">PATIENTS</th>
                                    <th class="border-0 fw-bold">STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="fw-bold small">Tripoli Governmental Hospital</span>
                                        </div>
                                    </td>
                                    <td><span class="text-muted small">10:20 AM</span></td>
                                    <td><span class="fw-bold small">3 Patients</span></td>
                                    <td><span class="badge badge-soft-orange px-3 py-2">Pending</span></td>

                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="fw-bold small">Saida Governmental Hospital</span>
                                        </div>
                                    </td>
                                    <td><span class="text-muted small">09:40 AM</span></td>
                                    <td><span class="fw-bold small">2 Patients</span></td>
                                    <td><span class="badge badge-soft-success px-3 py-2">Completed</span></td>

                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div class="col-lg-4">

                <div class="card border-0 shadow-sm p-4 mb-4 rounded-4">
                    <div class="d-flex align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Hospital Status Update</h5>
                    </div>

                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                        <span class="text-muted small fw-bold">Current Status</span>
                        <div class="dropdown">
                            <span class="small fw-bold dropdown-toggle cursor-pointer d-flex align-items-center text-danger" data-bs-toggle="dropdown">
                                <div style="width: 3px; height: 14px; background-color: #dc3545; border-radius: 2px; margin-right: 8px;"></div>
                                ENDANGERED
                            </span>
                            <ul class="dropdown-menu shadow border-0 rounded-3">
                                <li><a class="dropdown-item text-success small fw-bold" href="#">STABLE</a></li>
                                <li><a class="dropdown-item text-warning small fw-bold" href="#">CRITICAL</a></li>
                                <li><a class="dropdown-item text-danger small fw-bold" href="#">ENDANGERED</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                        <span class="text-muted small fw-bold">Infrastructure</span>
                        <div class="dropdown">
                            <span class="small fw-bold dropdown-toggle cursor-pointer d-flex align-items-center text-warning" data-bs-toggle="dropdown">
                                <div style="width: 3px; height: 14px; background-color: #ffc107; border-radius: 2px; margin-right: 8px;"></div>
                                Partially Damaged
                            </span>
                            <ul class="dropdown-menu shadow border-0 rounded-3">
                                <li><a class="dropdown-item small" href="#">Intact</a></li>
                                <li><a class="dropdown-item small" href="#">Minor Damage</a></li>
                                <li><a class="dropdown-item small" href="#">Partially Damaged</a></li>
                                <li><a class="dropdown-item small" href="#">Destroyed</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                        <span class="text-muted small fw-bold">Power Supply</span>
                        <div class="dropdown">
                            <span class="small fw-bold dropdown-toggle cursor-pointer d-flex align-items-center text-warning" data-bs-toggle="dropdown">
                                <div style="width: 3px; height: 14px; background-color: #ffc107; border-radius: 2px; margin-right: 8px;"></div>
                                Unstable
                            </span>
                            <ul class="dropdown-menu shadow border-0 rounded-3">
                                <li><a class="dropdown-item small" href="#">Stable</a></li>
                                <li><a class="dropdown-item small" href="#">Unstable</a></li>
                                <li><a class="dropdown-item small" href="#">Offline</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                        <span class="text-muted small fw-bold">Water Supply</span>
                        <div class="dropdown">
                            <span class="small fw-bold dropdown-toggle cursor-pointer d-flex align-items-center text-success" data-bs-toggle="dropdown">
                                <div style="width: 3px; height: 14px; background-color: #198754; border-radius: 2px; margin-right: 8px;"></div>
                                Available
                            </span>
                            <ul class="dropdown-menu shadow border-0 rounded-3">
                                <li><a class="dropdown-item small" href="#">Available</a></li>
                                <li><a class="dropdown-item small" href="#">Limited</a></li>
                                <li><a class="dropdown-item small" href="#">Unavailable</a></li>
                            </ul>
                        </div>
                    </div>



                    <button class="btn btn-outline-success w-100 py-2 fw-bold border-2 rounded-3 mt-2">
                        <i class="fa-solid fa-check-double me-2"></i> Save Status Updates
                    </button>
                </div>

                <!-- QUICK ACTIONS
                <div class="card border-0 shadow-sm p-4">
                    <div class="d-flex align-items-center mb-4">
                        <i class="fa-solid fa-bolt text-success me-2"></i>
                        <h5 class="fw-bold mb-0">Quick Actions</h5>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <button class="btn action-btn bg-soft-danger text-danger w-100 py-3 d-flex flex-column align-items-center">
                                <i class="fa-solid fa-bell fs-4 mb-2"></i>
                                <span class="small fw-bold">Send Alert</span>
                            </button>
                        </div>
                    
                        <div class="col-6">
                            <button class="btn action-btn bg-soft-purple text-purple w-100 py-3 d-flex flex-column align-items-center">
                                <i class="fa-solid fa-file-lines fs-4 mb-2"></i>
                                <span class="small fw-bold">Generate Report</span>
                            </button>
                        </div>
                    </div>
                </div> -->




            </div>
        </div>
    </div>
    <?php include('includes/script.php'); ?>
</body>

</html>