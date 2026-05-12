<?php
session_start();
require_once("class/DAL.class.php");

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$dal = new DAL();
$hospital_name = "Rafic Hariri University Hospital";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Hospital Teams | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
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

        /* HEADER SECTION */
        .back-link {
            color: #A3AED0;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: 0.2s;
        }

        .back-link:hover {
            color: #4318FF;
        }

        .btn-add-team {
            background: #4318FF;
            /* Horizon Primary Blue */
            color: white;
            border-radius: 12px;
            padding: 0 20px;
            font-weight: 700;
            font-size: 14px;
            border: none;
            height: 40px;
            transition: 0.3s;
        }

        .btn-add-team:hover {
            background: #3311CC;
            color: white;
        }

        /* STATS CARDS (HORIZON UI STYLE) */
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            height: 90px;
            /* Smaller height */
            border: none;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #F4F7FE;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #4318FF;
            margin-right: 15px;
        }

        .stat-label {
            color: #A3AED0;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 2px;
        }

        .stat-value {
            color: #1B2559;
            font-size: 20px;
            font-weight: 700;
        }

        /* TABLE DESIGN */
        .modern-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            border: none;
        }

        .table thead th {
            color: #A3AED0;
            font-size: 12px;
            font-weight: 600;
            border-bottom: 1px solid #F4F7FE;
            padding-bottom: 15px;
            text-transform: uppercase;
        }

        .table tbody td {
            padding: 18px 0;
            color: #1B2559;
            font-size: 14px;
            border-bottom: 1px solid #F4F7FE;
        }

        /* STATUS & MISSION COLORS */
        .status-available {
            color: #05CD99 !important;
            font-weight: 700;
        }

        .status-busy {
            color: #EE5D50 !important;
            font-weight: 700;
        }

        .status-mission {
            color: #FFB547 !important;
            font-weight: 700;
        }

        .mission-yes {
            color: #EE5D50;
            font-weight: 700;
            background: #FFF5F5;
            padding: 4px 10px;
            border-radius: 8px;
        }

        .mission-no {
            color: #05CD99;
            font-weight: 700;
            background: #F0FFF9;
            padding: 4px 10px;
            border-radius: 8px;
        }

        /* ACTION BUTTONS */
        .action-btn {
            background: #F4F7FE;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            color: #4318FF;
            margin-left: 5px;
        }
    </style>
</head>

<body>

    <?php include('includes/sidebar.php'); ?>
    <?php include('includes/nav.php'); ?>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <a href="hospitals.php" class="back-link"><i class="fa fa-chevron-left me-1"></i> Back to Hospitals</a>
                <h2 class="fw-bold mt-2" style="color: #1B2559;"><?= $hospital_name ?></h2>
                <p style="color: #A3AED0; font-size: 14px; margin: 0;">Manage response teams and mission status</p>
            </div>
            <button class="btn btn-add-team">
                <i class="fa-solid fa-plus me-2"></i> Add Team
            </button>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                    <div>
                        <div class="stat-label">Total Teams</div>
                        <div class="stat-value">4</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="color: #05CD99;"><i class="fa-solid fa-check-circle"></i></div>
                    <div>
                        <div class="stat-label">Available</div>
                        <div class="stat-value">1</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="color: #FFB547;"><i class="fa-solid fa-truck-fast"></i></div>
                    <div>
                        <div class="stat-label">On Mission</div>
                        <div class="stat-value">1</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                    <div>
                        <div class="stat-label">Avg Response</div>
                        <div class="stat-value">18 min</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="color: #eb251bff;"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div>
                        <div class="stat-label">Teams Needing Support</div>
                        <div class="stat-value">1</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modern-card">
            <h5 class="fw-bold mb-4" style="color: #1B2559;">Teams Overview</h5>
            <div class="table-responsive">
                <table id="teamsTable" class="table align-middle">
                    <thead>
                        <tr>
                            <th>Team Name</th>
                            <th>Leader</th>
                            <th>Specialization</th>
                            <th>On Mission?</th>
                            <th>Status</th>
                            <th>Members</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold">Medical Response Team A</td>
                            <td>Dr. Hassan Khaled</td>
                            <td>Trauma Care</td>
                            <td><span class="mission-no">No</span></td>
                            <td class="status-available">Available</td>
                            <td class="fw-bold">8</td>
                            <td class="text-end">
                                <button class="action-btn"><i class="fa-solid fa-eye"></i></button>
                                <button class="action-btn"><i class="fa-solid fa-pen"></i></button>
                                <button class="action-btn" style="color: #EE5D50;"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Emergency Transport B</td>
                            <td>Dr. Maya Fares</td>
                            <td>Critical Care</td>
                            <td><span class="mission-yes">Yes</span></td>
                            <td class="status-mission">On Mission</td>
                            <td class="fw-bold">6</td>
                            <td class="text-end">
                                <button class="action-btn"><i class="fa-solid fa-eye"></i></button>
                                <button class="action-btn"><i class="fa-solid fa-pen"></i></button>
                                <button class="action-btn" style="color: #EE5D50;"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#teamsTable').DataTable({
                pageLength: 5,
                dom: 'rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                language: {
                    paginate: {
                        previous: "<",
                        next: ">"
                    }
                }
            });
        });
    </script>
    <?php include('includes/script.php'); ?>

</body>

</html>