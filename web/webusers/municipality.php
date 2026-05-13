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
        .card-box {
            border-radius: 12px;
            padding: 15px;
            background: #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .stat-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #fff;
        }

        .icon-green {
            background: #28a745;
        }

        .icon-blue {
            background: #0d6efd;
        }

        .icon-orange {
            background: #fd7e14;
        }

        .icon-purple {
            background: #6f42c1;
        }

        .icon-teal {
            background: #20c997;
        }

        .small-text {
            font-size: 12px;
            color: gray;
        }

        .progress {
            height: 6px;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        .status-open {
            color: green;
            font-weight: 500;
        }

        .status-closed {
            color: red;
            font-weight: 500;
        }

        .map-box {
            height: 200px;
            background: #eaeaea;
            border-radius: 10px;
        }

        .badge-soft {
            background: #eef2ff;
            padding: 6px 10px;
            border-radius: 8px;
        }

        .footer-note {
            text-align: center;
            margin-top: 20px;
            color: green;
            font-weight: 500;
        }

        .icon-box {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .bg-soft-orange {
            background-color: #fff4e6;
            color: #ff922b;
        }

        .bg-soft-blue {
            background-color: #e7f5ff;
            color: #228be6;
        }

        .bg-soft-green {
            background-color: #ebfbee;
            color: #40c057;
        }

        .bg-soft-purple {
            background-color: #f3f0ff;
            color: #7950f2;
        }

      
        .status-urgent {
            background-color: #fff5f5;
            color: #e03131;
            border: 1px solid #ffc9c9;
        }

        .status-normal {
            background-color: #f8f9fa;
            color: #495057;
            border: 1px solid #dee2e6;
        }

        #needsTable td {
            padding: 12px 8px;
        }

        .status-urgent {
            background-color: #fff5f5;
            color: #e03131;
            border: 1px solid #ffc9c9;
            font-size: 0.65rem;
            padding: 4px 8px;
        }

        .status-normal {
            background-color: #f8f9fa;
            color: #495057;
            border: 1px solid #dee2e6;
            font-size: 0.65rem;
            padding: 4px 8px;
        }

        .icon-box {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            align-items: center;
            justify-content: center;
        }
    </style>

</head>

<body>

  
    <?php include('includes/sidebar.php'); ?>
    <?php include('includes/nav.php'); ?>

    <div class="main-content">
        <div class="container-fluid">

            <div class="row g-3 mb-4">
               <div class="col-md-2">
                    <div class="card border-0 shadow-sm p-3 rounded-4" style="background-color: #fff;">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px; background-color: #e8f5e9; color: #2d5a27;">
                                <i class="fa fa-home fs-6"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold" style="font-size: 0.8rem; color: #2d5a27;">Total Shelters</h6>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <h3 class="fw-bold mb-0" style="color: #1a3317; font-size: 1.5rem;">24</h3>
                            </div>
                            <i class="fa fa-pencil text-muted mb-1" style="font-size: 0.75rem; cursor: pointer;"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card border-0 shadow-sm p-3 rounded-4" style="background-color: #fff;">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px; background-color: #e3f2fd; color: #1976d2;">
                                <i class="fa fa-users fs-6"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold" style="font-size: 0.8rem; color: #1976d2;">Total Capacity</h6>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <h3 class="fw-bold mb-0" style="color: #0d47a1; font-size: 1.5rem;">3,560</h3>
                            </div>
                            <i class="fa fa-pencil text-muted mb-1" style="font-size: 0.75rem; cursor: pointer;"></i>
                        </div>
                    </div>
                </div>

                
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm p-3 rounded-4" style="background-color: #fff;">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px; background-color: #fff3e0; color: #ef6c00;">
                                <i class="fa fa-user fs-6"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold" style="font-size: 0.8rem; color: #ef6c00;">Occupied</h6>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <h3 class="fw-bold mb-0" style="color: #e65100; font-size: 1.5rem;">1,890</h3>
                            </div>
                            <i class="fa fa-pencil text-muted mb-1" style="font-size: 0.75rem; cursor: pointer;"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card border-0 shadow-sm p-3 rounded-4" style="background-color: #fff;">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px; background-color: #e8f5e9; color: #388e3c;">
                                <i class="fa fa-check fs-6"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold" style="font-size: 0.8rem; color: #388e3c;">Available</h6>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <h3 class="fw-bold mb-0" style="color: #1b5e20; font-size: 1.5rem;">1,670</h3>
                            </div>
                            <i class="fa fa-pencil text-muted mb-1" style="font-size: 0.75rem; cursor: pointer;"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card border-0 shadow-sm p-3 rounded-4" style="background-color: #fff;">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px; background-color: #f3e5f5; color: #7b1fa2;">
                                <i class="fa fa-shield fs-6"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold" style="font-size: 0.8rem; color: #7b1fa2;">Shelter Status</h6>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <div style="line-height: 1.2;">
                                    <h3 class="fw-bold mb-0" style="color: #004d40; font-size: 1.5rem;">8 open</h3>
                                </div>
                            </div>
                            <i class="fa fa-pencil text-muted mb-1" style="font-size: 0.75rem; cursor: pointer;"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card border-0 shadow-sm p-3 rounded-4" style="background-color: #fff;">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px; background-color: #e0f2f1; color: #00796b;">
                                <i class="fa fa-child fs-6"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold" style="font-size: 0.8rem; color: #00796b;">Displaced</h6>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <h3 class="fw-bold mb-0" style="color: #004d40; font-size: 1.5rem;">1,842</h3>
                            </div>
                            <i class="fa fa-pencil text-muted mb-1" style="font-size: 0.75rem; cursor: pointer;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">

                <div class="col-md-8">
                    <div class="card border-0 shadow-sm p-4 rounded-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center">
                                <div class="me-2 text-success"><i class="fa-solid fa-hospital-user fs-4"></i></div>
                                <h5 class="fw-bold mb-0">Shelter Overview</h5>
                            </div>
                            <button class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold">+ Add Shelter</button>
                        </div>

                        <div class="table-responsive">
                            <table id="shelterTable" class="table align-middle">
                                <thead class="text-muted small">
                                    <tr>
                                        <th>Shelter Name</th>
                                        <th>Location</th>
                                        <th>Capacity</th>
                                        <th>Occupied</th>
                                        <th>Availability</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-bottom">
                                        <td class="fw-bold">Community Hall</td>
                                        <td class="text-muted">Batroun</td>
                                        <td>200</td>
                                        <td class="text-danger fw-bold">200</td>
                                        <td class=" fw-bold">0</td>
                                        <td class="text-center"><span class="badge rounded-pill bg-light text-danger border">Closed</span></td>
                                        <td class="text-center">
                                            <i class="fa-solid fa-pen-to-square text-primary me-2"></i>
                                            <i class="fa-solid fa-trash text-danger"></i>
                                        </td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #f8f9fa;">
                                        <td class="fw-bold py-3 px-2" style="white-space: nowrap;">Thos Shelter</td>
                                        <td class="text-muted px-2">Tripoli</td>
                                        <td class="fw-bold px-2">500</td>
                                        <td class="fw-bold px-2">320</td>
                                        <td class="fw-bold px-2 text-success">180</td>
                                        <td class="px-2">
                                            <span class="badge rounded-pill px-3 py-2" style="background-color: #e8f5e9; color: #2e7d32; font-weight: 500; font-size: 0.7rem;">Open</span>
                                        </td>
                                        <td class="text-center px-2" style="white-space: nowrap;">
                                            <i class="fa-solid fa-pen-to-square text-primary me-2" style="cursor: pointer;"></i>
                                            <i class="fa-solid fa-trash text-danger" style="cursor: pointer;"></i>
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="fw-bold">Community Hall</td>
                                        <td class="text-muted">Batroun</td>
                                        <td>200</td>
                                        <td class="text-danger fw-bold">200</td>
                                        <td class=" fw-bold">0</td>
                                        <td class="text-center"><span class="badge rounded-pill bg-light text-danger border">Closed</span></td>
                                        <td class="text-center">
                                            <i class="fa-solid fa-pen-to-square text-primary me-2"></i>
                                            <i class="fa-solid fa-trash text-danger"></i>
                                        </td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #f8f9fa;">
                                        <td class="fw-bold py-3 px-2" style="white-space: nowrap;">Duel Sports Center</td>
                                        <td class="text-muted px-2">Zahle</td>
                                        <td class="fw-bold px-2">300</td>
                                        <td class="fw-bold px-2">250</td>
                                        <td class="fw-bold px-2 text-warning">50</td>
                                        <td class="px-2">
                                            <span class="badge rounded-pill px-3 py-2" style="background-color: #e8f5e9; color: #2e7d32; font-weight: 500; font-size: 0.7rem;">Open</span>
                                        </td>
                                        <td class="text-center px-2" style="white-space: nowrap;">
                                            <i class="fa-solid fa-pen-to-square text-primary me-2" style="cursor: pointer;"></i>
                                            <i class="fa-solid fa-trash text-danger" style="cursor: pointer;"></i>
                                        </td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #f8f9fa;">
                                        <td class="fw-bold py-3 px-2" style="white-space: nowrap;">Thos Shelter</td>
                                        <td class="text-muted px-2">Tripoli</td>
                                        <td class="fw-bold px-2">500</td>
                                        <td class="fw-bold px-2">320</td>
                                        <td class="fw-bold px-2 text-success">180</td>
                                        <td class="px-2">
                                            <span class="badge rounded-pill px-3 py-2" style="background-color: #e8f5e9; color: #2e7d32; font-weight: 500; font-size: 0.7rem;">Open</span>
                                        </td>
                                        <td class="text-center px-2" style="white-space: nowrap;">
                                            <i class="fa-solid fa-pen-to-square text-primary me-2" style="cursor: pointer;"></i>
                                            <i class="fa-solid fa-trash text-danger" style="cursor: pointer;"></i>
                                        </td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #f8f9fa;">
                                        <td class="fw-bold py-3 px-2" style="white-space: nowrap;">Duel Sports Center</td>
                                        <td class="text-muted px-2">Zahle</td>
                                        <td class="fw-bold px-2">300</td>
                                        <td class="fw-bold px-2">250</td>
                                        <td class="fw-bold px-2 text-warning">50</td>
                                        <td class="px-2">
                                            <span class="badge rounded-pill px-3 py-2" style="background-color: #e8f5e9; color: #2e7d32; font-weight: 500; font-size: 0.7rem;">Open</span>
                                        </td>
                                        <td class="text-center px-2" style="white-space: nowrap;">
                                            <i class="fa-solid fa-pen-to-square text-primary me-2" style="cursor: pointer;"></i>
                                            <i class="fa-solid fa-trash text-danger" style="cursor: pointer;"></i>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 rounded-4 h-100" style="background-color: #fff;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0" style="color: #1a3317;">Top Needs</h6>
                            <button class="btn btn-outline-success  btn-sm rounded-pill px-3 fw-bold" style="font-size: 0.7rem;">
                                + Add Need
                            </button>
                        </div>
                        <p class="text-muted small mb-3">Manage municipal requirements</p>

                        <div class="table-responsive">
                            <table id="needsTable" class="table table-borderless align-middle" style="width:100%; font-size: 0.85rem;">
                                <thead class="text-muted small border-bottom">
                                    <tr>
                                        <th class="fw-normal py-2">Requirement</th>
                                        <th class="fw-normal py-2 text-center">Qty</th>
                                        <th class="fw-normal py-2 text-center">Status</th>
                                        <th class="fw-normal py-2 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                    <tr class="border-bottom">
                                        <td>
                                            <span class="fw-bold">Food</span>
                                        </td>
                                        <td class="text-center fw-bold">1.2k</td>
                                        <td class="text-center"><span class="badge status-urgent">Urgent</span></td>
                                        <td class="text-center">
                                            <i class="fa-solid fa-paper-plane text-primary me-2" title="Send to Gov" style="cursor:pointer; font-size: 0.8rem;"></i>
                                            <i class="fa-solid fa-pen-to-square text-muted" title="Edit" style="cursor:pointer; font-size: 0.8rem;"></i>
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td>
                                            <span class="fw-bold">Water</span>
                                        </td>
                                        <td class="text-center fw-bold">3.4k</td>
                                        <td class="text-center"><span class="badge status-normal">Normal</span></td>
                                        <td class="text-center">
                                            <i class="fa-solid fa-paper-plane text-primary me-2" style="cursor:pointer; font-size: 0.8rem;"></i>
                                            <i class="fa-solid fa-pen-to-square text-muted" style="cursor:pointer; font-size: 0.8rem;"></i>
                                        </td>
                                    </tr>
                                  
                                    <tr class="border-bottom">
                                        <td>
                                            <span class="fw-bold">Medical</span>
                                        </td>
                                        <td class="text-center fw-bold">600</td>
                                        <td class="text-center"><span class="badge status-urgent">Urgent</span></td>
                                        <td class="text-center">
                                            <i class="fa-solid fa-paper-plane text-primary me-2" style="cursor:pointer; font-size: 0.8rem;"></i>
                                            <i class="fa-solid fa-pen-to-square text-muted" style="cursor:pointer; font-size: 0.8rem;"></i>
                                        </td>
                                    </tr>
                                
                                    <tr class="border-bottom">
                                        <td>
                                            <span class="fw-bold">Blankets</span>
                                        </td>
                                        <td class="text-center fw-bold">800</td>
                                        <td class="text-center"><span class="badge status-normal">Normal</span></td>
                                        <td class="text-center">
                                            <i class="fa-solid fa-paper-plane text-primary me-2" style="cursor:pointer; font-size: 0.8rem;"></i>
                                            <i class="fa-solid fa-pen-to-square text-muted" style="cursor:pointer; font-size: 0.8rem;"></i>
                                        </td>
                                    </tr>
                                   
                                    <tr class="border-bottom">
                                        <td>
                                            <span class="fw-bold">Fuel</span>
                                        </td>
                                        <td class="text-center fw-bold">2k</td>
                                        <td class="text-center"><span class="badge status-urgent">Urgent</span></td>
                                        <td class="text-center">
                                            <i class="fa-solid fa-paper-plane text-primary me-2" style="cursor:pointer; font-size: 0.8rem;"></i>
                                            <i class="fa-solid fa-pen-to-square text-muted" style="cursor:pointer; font-size: 0.8rem;"></i>
                                        </td>
                                    </tr>
                                   
                                    <tr class="border-bottom">
                                        <td>
                                            <span class="fw-bold">Water</span>
                                        </td>
                                        <td class="text-center fw-bold">3.4k</td>
                                        <td class="text-center"><span class="badge status-normal">Normal</span></td>
                                        <td class="text-center">
                                            <i class="fa-solid fa-paper-plane text-primary me-2" style="cursor:pointer; font-size: 0.8rem;"></i>
                                            <i class="fa-solid fa-pen-to-square text-muted" style="cursor:pointer; font-size: 0.8rem;"></i>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm p-4 rounded-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="fw-bold mb-0">Donations & Aid Received (Global View)</h6>
                            <button class="btn btn-outline-secondary btn-sm rounded-pill px-3">Filter by Date</button>
                        </div>

                        <div class="row">
                            <div class="col-md-3 border-end">
                                <div class="p-2">
                                    <span class="text-muted small">Total Food</span>
                                    <h4 class="fw-bold text-success mb-0">12,500</h4>
                                    <span class="small text-muted">Packages</span>
                                </div>
                            </div>
                            <div class="col-md-3 border-end">
                                <div class="p-2">
                                    <span class="text-muted small">Total Water</span>
                                    <h4 class="fw-bold text-primary mb-0">45,000</h4>
                                    <span class="small text-muted">Liters</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-note">
                💚 For lebanon. For Life.
            </div>

        </div>
    </div>
    <?php include('includes/script.php'); ?>
</body>

</html>