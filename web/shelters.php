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
    <title>Shelters Management</title>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #f0f0f0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .filter-select {
            border-radius: 12px;
            border: 1px solid #E9EDF7;
            height: 40px;
        }

        .btn-add {
            background: #07a77d;
            color: white;
            border-radius: 12px;
            height: 40px;
            padding: 0 20px;
            font-weight: 700;
            width: 450px;
        }

        .info-footer {
            background-color: #f0f7ff;
            /* Soft blue background */
            border: 1px solid #e6f7ff;
        }

        /* Colors based on Image 2 */
        .bg-danger {
            background-color: #ff4d4f !important;
        }

        .bg-warning {
            background-color: #faad14 !important;
        }

        .bg-success {
            background-color: #52c41a !important;
        }

        .status-high {
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

        /* Subtle Action Buttons */
        .action-btn {
            border: none;
            background: transparent;
            color: #7a839d;
            /* A soft, muted grey-blue that matches the headers */
            padding: 2px;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-size: 0.9rem;
            margin: 0 2px;
        }

        /* Hover effects - subtle and less aggressive */
        .action-btn:hover {
            background-color: #f4f7fe;
            /* Very light background on hover */
            color: #4318ff;
            /* Your primary theme blue */
        }

        /* Specific soft red for trash hover only */
        .action-btn.btn-delete:hover {
            background-color: #fff5f5;
            color: #ee5d50;
        }

        /* Specific soft green for view/eye hover */
        .action-btn.btn-view:hover {
            background-color: #f0fdf4;
            color: #05cd99;
        }

        .table thead th {
            color: #A3AED0;
            font-size: 12px;
        }

        .need-bar {
            height: 4px;
            /* Thinner bars like Image 2 */
            background-color: #f0f2f5;
            border-radius: 10px;
            overflow: hidden;
        }

        .need-fill {
            height: 100%;
            border-radius: 10px;
        }

        .red {
            background: #EE5D50;
        }

        .orange {
            background: #FFB547;
        }

        .green {
            background: #05CD99;
        }

        .bi {
            font-size: 1.1rem;
            /* Adjust icon size */
        }

        .summary-box {
            padding: 15px;
            border: 1px solid #f0f2f5;
            border-radius: 12px;
            background: #fff;
        }

        .summary-box h4 {
            margin: 8px 0;
            color: #1B2559;
        }

        .legend-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .legend-item {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            /* Adjusts width: Labels get more, percentages/money get less */
            align-items: center;
            font-size: 0.9rem;
            padding-bottom: 10px;
            /* Space between rows */
            border-bottom: 1px solid #f8f9fa;
            /* Optional: light line like image 2 */
        }

        .legend-item .text-muted {
            text-align: left;
            padding-left: 10px;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .bg-info {
            background-color: #0081ff !important;
        }

        /* Match your blue */
    </style>
</head>

<body>

    <?php include('includes/sidebar.php'); ?>
    <?php include('includes/nav.php'); ?>

    <div class="main-content">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold">Shelters Management</h2>
                <p class="text-muted small">Monitor and manage shelters and capacities</p>
            </div>

            <div class="d-flex gap-2">
                <select class="form-select filter-select">
                    <option>All Regions</option>
                </select>

                <select class="form-select filter-select">
                    <option>All Statuses</option>
                </select>

                <button class="btn btn-add">
                    <i class="fa-solid fa-plus me-2"></i> Add Shelter
                </button>
            </div>
        </div>

        <!-- STATS -->
        <div class="row g-3 mb-4">


            <!-- here ex  -->
            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #f4f7fe; color: #4318ff;">
                        <i class="fa-solid fa-house"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Total Shelters</span>
                        <span class="card-value">56</span>
                        <span class="card-subtext"> All Regions</span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #f4f7fe; color: #18ff9b;">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Available Capacity</span>
                        <span class="card-value">3,245</span>
                        <span class="card-subtext"> People</span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #f4f7fe; color: #9318ff;">
                        <i class="fa-solid fa-user-group"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Current Occupancy</span>
                        <span class="card-value">2,155</span>
                        <span class="card-subtext"> People</span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #f4f7fe; color: #ff8818;">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Occupancy Rate</span>
                        <span class="card-value">66%</span>
                        <span class="card-subtext"> Average</span>
                    </div>
                </div>
            </div>




            <!-- MAIN CONTENT -->
            <div class="row g-4">

                <!-- TABLE -->
                <div class="col-md-8">
                    <div class="modern-card">
                        <h5 class="fw-bold mb-4">Shelters List</h5>

                        <table id="sheltersTable" class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Capacity</th>
                                    <th>Occupied</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td class="fw-bold">Beirut Central Shelter</td>
                                    <td>Beirut</td>
                                    <td>500</td>
                                    <td>420</td>
                                    <td><span class="status-medium">Near Full</span></td>
                                    <td class="text-end text-nowrap">
                                        <button class="action-btn btn-view" title="View Details">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>

                                        <button class="action-btn btn-edit" title="Edit">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>

                                        <button class="action-btn btn-delete" title="Delete">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="fw-bold">Sidon High School</td>
                                    <td>Sidon</td>
                                    <td>300</td>
                                    <td>295</td>
                                    <td><span class="status-high">Critical</span></td>
                                    <td class="text-end text-nowrap">
                                        <button class="action-btn btn-view" title="View Details">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>

                                        <button class="action-btn btn-edit" title="Edit">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>

                                        <button class="action-btn btn-delete" title="Delete">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="fw-bold">Tripoli Community Center</td>
                                    <td>Tripoli</td>
                                    <td>450</td>
                                    <td>150</td>
                                    <td><span class="status-low">Available</span></td>
                                    <td class="text-end text-nowrap">
                                        <button class="action-btn btn-view" title="View Details">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>

                                        <button class="action-btn btn-edit" title="Edit">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>

                                        <button class="action-btn btn-delete" title="Delete">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="fw-bold">Byblos Public Hall</td>
                                    <td>Jbeil</td>
                                    <td>200</td>
                                    <td>45</td>
                                    <td><span class="status-low">Available</span></td>
                                    <td class="text-end text-nowrap">
                                        <button class="action-btn btn-view" title="View Details">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>

                                        <button class="action-btn btn-edit" title="Edit">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>

                                        <button class="action-btn btn-delete" title="Delete">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- RIGHT SIDE -->
                <div class="col-md-4">

                    <!-- TOP NEEDS -->
                    <div class="modern-card mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="fw-bold mb-0">
                                Top Needs (This Week)
                                <i class="bi bi-info-circle text-primary ms-1" style="font-size: 0.85rem; cursor: pointer;"></i>
                            </h6>
                            <a href="needs.php" class="small text-decoration-none fw-medium">View All Needs <i class="bi bi-chevron-right small"></i></a>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-fuel-pump me-3 text-secondary"></i> <span class="fw-medium">Fuel</span>
                                </div>
                                <span class="fw-bold">12</span>
                            </div>
                            <div class="need-bar">
                                <div class="need-fill bg-danger" style="width:75%"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-ev-front me-3 text-secondary"></i> <span class="fw-medium">Food Aid</span>
                                </div>
                                <span class="fw-bold">15</span>
                            </div>
                            <div class="need-bar">
                                <div class="need-fill bg-warning" style="width:85%"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-clipboard-plus me-3 text-secondary"></i> <span class="fw-medium">Medical Supplies</span>
                                </div>
                                <span class="fw-bold">9</span>
                            </div>
                            <div class="need-bar">
                                <div class="need-fill bg-success" style="width:60%"></div>
                            </div>
                        </div>


                    </div>

                    <!-- DONATIONS -->

                    <div class="modern-card">
                        <div class="d-flex align-items-center mb-4">
                            <h6 class="fw-bold mb-0">Donations & Aid Summary</h6>
                            <i class="bi bi-info-circle text-primary ms-2" style="font-size: 0.85rem;"></i>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="summary-box">
                                    <small class="text-muted">Total Donations Received</small>
                                    <h4 class="fw-bold">$45,000</h4>
                                    <small class="text-secondary opacity-75">This Week</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="summary-box">
                                    <small class="text-muted">Aid Shipments Incoming</small>
                                    <h4 class="fw-bold">12</h4>
                                    <small class="text-secondary opacity-75">This Week</small>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center mt-4">
                            <div class="col-5">
                                <div class="chart-container" style="position: relative; height:150px; width:150px">
                                    <canvas id="donationsChart"></canvas>
                                </div>
                            </div>

                            <div class="col-7">
                                <div class="legend-list">
                                    <div class="legend-item">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="dot bg-danger"></span>
                                            <span>Fuel</span>
                                        </div>
                                        <span class="text-muted">40%</span>
                                    </div>

                                    <div class="legend-item">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="dot bg-warning"></span>
                                            <span>Medical Supplies</span>
                                        </div>
                                        <span class="text-muted">25%</span>
                                    </div>

                                    <div class="legend-item">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="dot" style="background-color: #0081ff;"></span>
                                            <span>Food Aid</span>
                                        </div>
                                        <span class="text-muted">20%</span>
                                    </div>

                                    <div class="legend-item">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="dot bg-info"></span>
                                            <span>Water</span>
                                        </div>
                                        <span class="text-muted">15%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <!-- JS -->
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

        <script>
            $('#sheltersTable').DataTable({
                pageLength: 7,
                dom: 'rt<"d-flex justify-content-between"ip>',
                language: {
                    info: "Showing _START_ to _END_ of _TOTAL_ results",
                    paginate: {
                        previous: "<",
                        next: ">"
                    }
                }
            });
            document.addEventListener("DOMContentLoaded", function() {
                const ctx = document.getElementById('donationsChart').getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [40, 25, 20, 15], // The percentages
                            backgroundColor: [
                                '#EE5D50', // Red
                                '#FFB547', // Orange
                                '#0081ff', // Blue
                                '#05CD99' // Green
                            ],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        cutout: '70%', // This makes the "ring" thinner/donut bigger
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }, // We use our own custom legend
                            tooltip: {
                                enabled: true
                            }
                        }
                    }
                });
            });
        </script>

        <?php include('includes/script.php'); ?>

</body>

</html>