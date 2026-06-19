<?php
session_start();
require_once("class/municipality.class.php");

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
$mun = new muni();
$shelters = $mun->getAllShelters();
$totalShelters = $mun->totalShelters();
$totalCapacity = $mun->totalCapacity();
$totalOccupied = $mun->totalOccupied();
$availableCapacity = $mun->availableCapacity();
$occupancyRate = $mun->getOccupancyRate();
$topNeeds = $mun->topNeeds();
$totalDonations = $mun->totalDonations();
$municipalities = $mun->getAllmuni();
$totalAidEntries = $mun->totalAidEntries();

$chartData = $mun->donationChartData();

$labels = [];
$totals = [];
foreach ($chartData as $row) {
    $labels[] = ucfirst($row['donation_type']);
    $totals[] = $row['total'];
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Shelters Management</title>
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
            background: #1B2559;
            ;

            color: white;
            border-radius: 12px;
            height: 40px;
            padding: 0 20px;
            font-weight: 700;
            width: 170px;
        }

        .info-footer {
            background-color: #f0f7ff;
            border: 1px solid #e6f7ff;
        }

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



        .table thead th {
            color: #A3AED0;
            font-size: 12px;
        }

        .need-bar {
            height: 4px;
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
            align-items: center;
            font-size: 0.9rem;
            padding-bottom: 10px;
            border-bottom: 1px solid #f8f9fa;
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

        .filter-select {
            border-radius: 12px;
            border: 1px solid #E9EDF7;
            height: 45px;
        }

        .btn-delete {
            background: #fff5f5;
            color: #ee5d50;
        }

        .btn-delete:hover {
            background: #ee5d50;
            color: #fff;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.2s;
        }

        @media (max-width:768px) {

            .main-content>.d-flex.justify-content-between.align-items-center.mb-4 {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 15px;
            }

            .main-content {
                margin-left: 70px !important;
                width: calc(100% - 70px) !important;
                padding: 15px !important;
                overflow-x: hidden;
            }

            .d-flex.align-items-center.gap-3 {
                display: flex;
                flex-direction: column;
                width: 100%;
                gap: 10px !important;
            }

            #customSearch,
            #regionFilter,
            #statusFilter,
            .btn-add {
                width: 100% !important;
            }

            .row.g-3.mb-4 {
                flex-wrap: nowrap;
                overflow-x: auto;
                padding-bottom: 10px;
            }

            .row.g-3.mb-4>.col {
                flex: 0 0 280px;
                max-width: 280px;
            }

            .modal-dialog {
                margin: 10px;
            }

            .row.g-4 {
                display: block;
            }

            .col-md-8,
            .col-md-4 {
                width: 100%;
                max-width: 100%;
                margin-bottom: 20px;
            }

            .chart-container {
                width: 120px !important;
                height: 120px !important;
                margin: auto;
            }

            .legend-item {
                font-size: 12px;
            }

        }
    </style>
</head>
<!-- ADD SHELTER MODAL -->



<body>

    <?php include('includes/sidebar.php'); ?>
    <?php include('includes/nav.php'); ?>
<div class="modal fade" id="addShelterModal" tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 rounded-4">

            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    Add Shelter
                </h5>

                <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body">

                <form id="addShelterForm">

                    <!-- ORGANIZATION -->

                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Select Municipality
                        </label>

                        <select
                            name="organization_id"
                            id="organizationSelect"
                            class="form-select">

                            <option value="">
                                Select Municipality
                            </option>

                            <option value="new">
                                Add New Municipality
                            </option>

                            <?php
                            foreach ($municipalities as $org):  ?>
                                <option value="<?= $org['id'] ?>">
                                    <?= $org['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- NEW MUNICIPALITY FIELDS -->

                    <div id="municipalityFields">

                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Municipality Name
                                </label>

                                <input type="text"
                                    name="organization_name"
                                    class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Municipality Location
                                </label>

                                <input type="text"
                                    name="organization_location"
                                    class="form-control">
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Municipality Email
                                </label>

                                <input type="email"
                                    name="organization_email"
                                    class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Municipality Password
                                </label>

                                <input type="password"
                                    name="organization_password"
                                    class="form-control">
                            </div>

                        </div>

                    </div>

                    <!-- SHELTER -->

                    <hr>

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Shelter Name
                            </label>

                            <input type="text"
                                name="shelter_name"
                                class="form-control"
                                required>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Shelter Location
                            </label>

                            <input type="text"
                                name="location"
                                class="form-control"
                                required>

                        </div>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Capacity
                        </label>

                        <input type="number"
                            name="capacity"
                            class="form-control"
                            required>

                    </div>

                    <div class="text-end">

                        <button type="submit"
                            class="btn btn-success px-4">

                            Add Shelter

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>
    <div class="main-content">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold">Shelters Management</h2>
                <p class="text-muted small">Monitor and manage shelters and capacities</p>
            </div>

            <div class="d-flex align-items-center gap-3">

                <input
                    type="text"
                    id="customSearch"
                    class="form-control filter-select"
                    placeholder="Search shelters..."
                    style="width:220px;">

                <select id="regionFilter" class="form-select filter-select" style="width:180px;">
                    <option value="">All Regions</option>

                    <?php
                    $regions = [];
                    foreach ($shelters as $row) {
                        if (!in_array($row['location'], $regions)) {
                            $regions[] = $row['location'];
                        }
                    }
                    foreach ($regions as $region):
                    ?>
                        <option value="<?= $region ?>">
                            <?= $region ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select id="statusFilter" class="form-select filter-select" style="width:180px;">
                    <option value="">All Statuses</option>

                    <?php
                    $statuses = [];
                    foreach ($shelters as $row) {
                        if (!in_array($row['status'], $statuses)) {
                            $statuses[] = $row['status'];
                        }
                    }
                    foreach ($statuses as $status):
                    ?>
                        <option value="<?= $status ?>">
                            <?= ucfirst($status) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button class="btn btn-add"
                    data-bs-toggle="modal"
                    data-bs-target="#addShelterModal">

                    <i class="fa-solid fa-plus me-2"></i>
                    Add Shelter

                </button>

            </div>
        </div>


        <div class="row g-3 mb-4">
            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #f4f7fe; color: #4318ff;">
                        <i class="fa-solid fa-house"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Total Shelters</span>
                        <span class="card-value"><?= $totalShelters ?></span>
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
                        <span class="card-value"><?= $availableCapacity ?></span>
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
                        <span class="card-value"><?= $totalOccupied ?></span>
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
                        <span class="card-value"><?= $occupancyRate ?>%</span>
                    </div>
                </div>
            </div>

        </div>

        <div class="row g-4">

            <!-- TABLE -->
            <div class="col-md-8">
                <div class="modern-card">
                    <h5 class="fw-bold mb-4">Shelters List</h5>
                    <div class="table-responsive">
                        <table id="sheltersTable" class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Municipality</th>
                                    <th>Capacity</th>
                                    <th>Occupied</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php foreach ($shelters as $row): ?>

                                    <tr>

                                        <td class="fw-bold">
                                            <?= $row['shelter_name'] ?>
                                        </td>

                                        <td>
                                            <?= $row['location'] ?>
                                        </td>

                                        <td>
                                            <?= $row['organization_name'] ?>
                                        </td>
                                        <td>
                                            <?= $row['capacity'] ?>
                                        </td>

                                        <td>
                                            <?= $row['occupied'] ?>
                                        </td>

                                        <td>

                                            <?php
                                            if ($row['status'] == 'full') {
                                                $class = "status-high";
                                            } elseif ($row['status'] == 'near_full') {
                                                $class = "status-medium";
                                            } else {
                                                $class = "status-low";
                                            }
                                            ?>

                                            <span class="<?= $class ?>">
                                                <?= ucfirst($row['status']) ?>
                                            </span>

                                        </td>

                                        <td class="text-center text-nowrap">
                                            <button class="action-btn btn-delete dltshelter" data-id="<?php echo $row['id']; ?>">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                            </i>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            <!-- RIGHT SIDE -->
            <div class="col-md-4">
                <!-- TOP NEEDS -->
                <div class="modern-card mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold mb-0">
                            Top Needs
                            <i class="bi bi-info-circle text-primary ms-1" style="font-size: 0.85rem; cursor: pointer;"></i>
                        </h6>
                        <a href="needs.php" class="small text-decoration-none fw-medium">View All Needs <i class="bi bi-chevron-right small"></i></a>
                    </div>
                    <?php $maxNeed = $topNeeds[0]['total_quantity']; ?>
                    <?php foreach ($topNeeds as $need): ?>
                        <?php
                        $width = ($need['total_quantity'] / $maxNeed) * 100;
                        if ($width >= 80) {
                            $color = "bg-danger";
                        } elseif ($width >= 50) {
                            $color = "bg-warning";
                        } else {
                            $color = "bg-success";
                        }
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-box-seam me-3 text-secondary"></i>
                                    <span class="fw-medium">
                                        <?= ucfirst($need['need_name']) ?>
                                    </span>
                                </div>
                                <span class="fw-bold">
                                    <?= $need['total_quantity'] ?>
                                </span>
                            </div>
                            <div class="need-bar">
                                <div class="need-fill <?= $color ?>"
                                    style="width:<?= $width ?>%">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
                                <small class="text-muted">Money Received</small>
                                <h4 class="fw-bold">$<?= number_format($totalDonations) ?></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="summary-box">
                                <small class="text-muted">Aid Incoming</small>
                                <h4 class="fw-bold"><?= $totalAidEntries ?></h4>
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
                            <?php
                            $totalAll = array_sum($totals);
                            $chartColors = [];
                            foreach ($chartData as $row) {
                                $percentage = ($row['total'] / $totalAll) * 100;
                                if ($percentage >= 35) {
                                    $chartColors[] = '#EE5D50';
                                } elseif ($percentage >= 20) {
                                    $chartColors[] = '#FFB547';
                                } elseif ($percentage >= 10) {
                                    $chartColors[] = '#0081ff';
                                } else {
                                    $chartColors[] = '#05CD99';
                                }
                            }
                            ?>
                            <div class="legend-list">
                                <?php
                                foreach ($chartData as $row):
                                    $type = $row['donation_type'];
                                    $amount = $row['total'];
                                    $percentage = ($amount / $totalAll) * 100;
                                    if ($percentage >= 35) {
                                        $color = "bg-danger";
                                    } elseif ($percentage >= 20) {
                                        $color = "bg-warning";
                                    } elseif ($percentage >= 10) {
                                        $color = "bg-info";
                                    } else {
                                        $color = "bg-success";
                                    }
                                ?>
                                    <div class="legend-item">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="dot <?= $color ?>"></span>
                                            <span>
                                                <?= ucfirst($type) ?>
                                            </span>
                                        </div>
                                        <span class="text-muted">
                                            <?= round($percentage) ?>%
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>


    <?php include('includes/script.php'); ?>

    <script>
        $(document).ready(function() {

            var table = $('#sheltersTable').DataTable({

                pageLength: 11,

                dom: 'rt<"d-flex justify-content-between"ip>',

                ordering: true,
                order: [],

                language: {

                    info: "Showing _START_ to _END_ of _TOTAL_ results",

                    paginate: {
                        previous: "<",
                        next: ">"
                    }
                }
            });

            $('#customSearch').on('keyup', function() {

                table.search(this.value).draw();

            });
            // REGION FILTER
            $('#regionFilter').on('change', function() {

                table.column(1).search(this.value).draw();

            });

            // STATUS FILTER
            $('#statusFilter').on('change', function() {
                table.column(5).search(this.value, false, false).draw();
            });

        });



        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('donationsChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($labels) ?>,
                    datasets: [{
                        data: <?= json_encode($totals) ?>,
                        backgroundColor: <?= json_encode($chartColors) ?>,
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    cutout: '70%', 
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }, 
                        tooltip: {
                            enabled: true
                        }
                    }
                }
            });
        });
        $('#municipalityFields').hide();

        $('#organizationSelect').on('change', function() {

            if ($(this).val() == 'new') {

                $('#municipalityFields').slideDown();

            } else {

                $('#municipalityFields').slideUp();
            }
        });

        $('#addShelterForm').on('submit', function(e) {

            e.preventDefault();

            $.ajax({

                url: 'actions/add_shelter.php',

                type: 'POST',

                data: $(this).serialize(),

                dataType: 'json',

             success: function(response) {
                    if (response.status == 'success') {
                        $('#addShelterModal').modal('hide');
                        $('#addShelterForm')[0].reset();
                        Swal.fire({
                            icon: 'success',
                            title: 'Shelter Added Successfully',
                            confirmButtonColor: '#2d5a27',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else if (response.message === 'email_duplicate') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Email Already Exists',
                            text: 'This email is already registered. Please use a different one.',
                            confirmButtonColor: '#2d5a27'
                        });
                    } else if (response.message === 'location_not_found') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Shelter Location Not Found',
                            text: 'Could not find the shelter location. Try a different spelling.',
                            confirmButtonColor: '#2d5a27'
                        });
                    } else if (response.message === 'org_location_not_found') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Municipality Location Not Found',
                            text: 'Could not find the municipality location. Try a different spelling.',
                            confirmButtonColor: '#2d5a27'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message,
                            confirmButtonColor: '#2d5a27'
                        });
                    }
                },

                error: function() {

                    Swal.fire({

                        icon: 'error',

                        title: 'Error',

                        text: 'Something went wrong'

                    });
                }
            });
        });

        $(document).on('click', '.dltshelter', function() {

            let id = $(this).data('id');

            Swal.fire({
                title: 'Delete shelter?',
                text: "This action cannot be undone",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Delete'
            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({
                        url: 'actions/delete_shelter.php',
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
    </script>


</body>

</html>