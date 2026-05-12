<?php
session_start();
require_once("class/DAL.class.php");

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$dal = new DAL();

/* ================= KPIs ================= */
$wonRevenue = $dal->getdata("SELECT COALESCE(SUM(value),0) total FROM deals WHERE stage='won'")[0]['total'];
$pipelineValue = $dal->getdata("SELECT COALESCE(SUM(value),0) total FROM deals WHERE stage NOT IN ('won','lost')")[0]['total'];
$activeLeads = $dal->getdata("SELECT COUNT(*) total FROM leads WHERE status NOT IN ('won','lost')")[0]['total'];
$totalDeals = $dal->getdata("SELECT COUNT(*) total FROM deals")[0]['total'];
$wonDeals = $dal->getdata("SELECT COUNT(*) total FROM deals WHERE stage='won'")[0]['total'];
$conversion = $totalDeals ? round(($wonDeals / $totalDeals) * 100, 1) : 0;

/* ================= Charts ================= */
$pipelineStages = $dal->getdata("SELECT stage, SUM(value) total FROM deals GROUP BY stage");
$stageLabels = array_column($pipelineStages, 'stage');
$stageValues = array_column($pipelineStages, 'total');

$monthly = $dal->getdata("
    SELECT DATE_FORMAT(created_at,'%b') m, SUM(value) t 
    FROM deals WHERE stage='won'
    GROUP BY m ORDER BY created_at
");
$months = array_column($monthly, 'm');
$monthValues = array_column($monthly, 't');

$TARGET = 50000;
$progress = min(100, round(($wonRevenue / $TARGET) * 100));

/* ================= Tables ================= */
$latestDeals = $dal->getdata("
    SELECT d.title,d.value,d.stage,c.name
    FROM deals d
    LEFT JOIN contacts c ON d.contact_id=c.id
    ORDER BY d.id DESC LIMIT 5
");

$pendingTasks = $dal->getdata("
    SELECT t.title,u.name,t.due_date
    FROM tasks t
    LEFT JOIN users u ON t.assigned_to=u.id
    WHERE t.status='pending'
    ORDER BY t.id DESC LIMIT 5
");

$currentMonth = $dal->getdata("
    SELECT COALESCE(SUM(value),0) t 
    FROM deals 
    WHERE stage='won' 
    AND MONTH(created_at)=MONTH(CURDATE())
")[0]['t'];

$lastMonth = $dal->getdata("
    SELECT COALESCE(SUM(value),0) t 
    FROM deals 
    WHERE stage='won' 
    AND MONTH(created_at)=MONTH(CURDATE())-1
")[0]['t'];

$growth = $lastMonth > 0
    ? round((($currentMonth - $lastMonth) / $lastMonth) * 100, 1)
    : 0;
// Current pipeline
$currentPipeline = $dal->getdata("
    SELECT COALESCE(SUM(value),0) t
    FROM deals
    WHERE stage NOT IN ('won','lost')
    AND YEAR(created_at) = YEAR(CURDATE())
    AND MONTH(created_at) = MONTH(CURDATE())
")[0]['t'];


// Previous pipeline
$lastPipeline = $dal->getdata("
    SELECT COALESCE(SUM(value),0) t
    FROM deals
    WHERE stage NOT IN ('won','lost')
    AND created_at >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 1 MONTH)
    AND created_at <  DATE_FORMAT(CURDATE(), '%Y-%m-01')
")[0]['t'];


$pipelineChange = $lastPipeline > 0
    ? round((($currentPipeline - $lastPipeline) / $lastPipeline) * 100, 1)
    : null;

$currentLeads = $dal->getdata("
    SELECT COUNT(*) t
    FROM leads
    WHERE status NOT IN ('won','lost')
    AND MONTH(created_at) = MONTH(CURDATE())
")[0]['t'];

$lastLeads = $dal->getdata("
    SELECT COUNT(*) t
    FROM leads
    WHERE status NOT IN ('won','lost')
    AND MONTH(created_at) = MONTH(CURDATE()) - 1
")[0]['t'];

$leadsChange = $lastLeads > 0
    ? round((($currentLeads - $lastLeads) / $lastLeads) * 100, 1)
    : null;
// Current month
$currentTotal = $dal->getdata("
    SELECT COUNT(*) t FROM deals
    WHERE MONTH(created_at) = MONTH(CURDATE())
")[0]['t'];

$currentWon = $dal->getdata("
    SELECT COUNT(*) t FROM deals
    WHERE stage='won'
    AND MONTH(created_at) = MONTH(CURDATE())
")[0]['t'];

$currentConversion = $currentTotal > 0
    ? ($currentWon / $currentTotal) * 100
    : 0;

// Last month
$lastTotal = $dal->getdata("
    SELECT COUNT(*) t FROM deals
    WHERE MONTH(created_at) = MONTH(CURDATE()) - 1
")[0]['t'];

$lastWon = $dal->getdata("
    SELECT COUNT(*) t FROM deals
    WHERE stage='won'
    AND MONTH(created_at) = MONTH(CURDATE()) - 1
")[0]['t'];

$lastConversion = $lastTotal > 0
    ? ($lastWon / $lastTotal) * 100
    : 0;

$conversionChange = $lastConversion > 0
    ? round($currentConversion - $lastConversion, 1)
    : null;


$pipelineDelta = $currentPipeline - $lastPipeline;

?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php include('includes/header.php'); ?>
    <style>
        :root {
            --primary: #6366f1;
            /* Modern Indigo */
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg: #f8fafc;
            --card: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --sidebar-bg: #0f172a;
        }

        body {
            background: var(--bg);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: var(--text-main);
            letter-spacing: -0.01em;
        }



        /* MODERN CARDS */
        .card {
            background: var(--card);
            border-radius: 12px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        /* KPI STYLING */
        .kpi {
            font-size: 28px;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.02em;
        }

        .text-muted {
            color: var(--text-muted) !important;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        /* ALERT RE-DESIGN */
        .alert-warning {
            background: #fffbeb;
            border-left: 4px solid var(--warning);
            color: #92400e;
            border-radius: 8px;
            padding: 16px;
            font-size: 0.95rem;
        }

        /* TABLE POLISH */
        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #f1f5f9;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.05em;
            border: none;
            padding: 12px;
        }

        .table td {
            padding: 14px 12px;
            vertical-align: middle;
            border-color: #f1f5f9;
        }

        .table tr:hover {
            background-color: #f8fafc !important;
        }

        /* SECTION TITLES */
        .section-title {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* KPI BACKGROUND DECORATION */
        .kpi-card {
            overflow: hidden;
            position: relative;
        }

        .kpi-card::after {
            content: "";
            position: absolute;
            top: -20px;
            right: -20px;
            width: 80px;
            height: 80px;
            background: currentColor;
            opacity: 0.05;
            border-radius: 50%;
        }

        /* CUSTOM SCROLLBAR (Optional) */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .info-tooltip {
            position: relative;
            cursor: default;
            /* display: inline-block; */
            /* margin-left: 5px; */
            /* color: var(--muted); */
            /* font-size: 12px; */
        }

        .info-tooltip:before {
            content: attr(data-tip);
            position: absolute;
            bottom: 125%;
            /* left: 50%;
            transform: translateX(-50%); */
            left: -70px;
            /* ← move tooltip left */
            transform: none;
            padding: 8px 12px;
            background: #1e293b;
            color: #fff;
            border-radius: 6px;
            font-size: 11px;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: 0.2s;
            z-index: 100;
        }

        .info-tooltip:hover:before {
            opacity: 1;
            visibility: visible;
        }

        .chart-box,
        .gauge-box {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .chart-box canvas,
        .gauge-box canvas {
            flex-grow: 1;
        }

        .card.hero {
            border-top: 3px solid var(--primary);
        }

        .card:hover {
            transform: none;
            box-shadow: 0 6px 10px rgba(0, 0, 0, .06);
        }

        .chart-box:hover {
            transform: translateY(-3px);
        }

        .chart-box {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .kpi-badge {
            font-size: 12px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .kpi-badge.up {
            background: rgba(34, 197, 94, .15);
            color: #16a34a;
        }

        .kpi-badge.down {
            background: rgba(239, 68, 68, .15);
            color: #dc2626;
        }
    </style>

</head>

<body>

    <!-- SIDEBAR -->
    <?php include('includes/sidebar.php'); ?>
    <?php include('includes/nav.php'); ?>

    <div class="main-content">

        <!-- ALERTS -->

        <!-- <div class="alert alert-warning">⚠️ 3 Deals are stuck in Proposal stage</div> -->

        <!-- KPIs -->
        <div class="row g-3 mb-4">

            <div class="col-md-3">
                <div class="card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted">Won Revenue</div>
                            <div class="kpi text-success">$<?= number_format($wonRevenue) ?></div>
                        </div>

                        <span class="kpi-badge <?= $growth >= 0 ? 'up' : 'down' ?> info-tooltip"
                            data-tip="Change in won revenue compared to last month">
                            <?= $growth >= 0 ? '▲' : '▼' ?> <?= abs($growth) ?>%
                        </span>

                    </div>

                </div>
            </div>


            <div class="col-md-3">
                <div class="card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted">Pipeline</div>
                            <div class="kpi text-primary">$<?= number_format($pipelineValue) ?></div>
                        </div>

                        <span class="kpi-badge <?= $pipelineDelta >= 0 ? 'up' : 'down' ?> info-tooltip"
                            data-tip="Difference in open pipeline value compared to last month">
                            <?= $pipelineDelta >= 0 ? '+' : '−' ?>$<?= number_format(abs($pipelineDelta)) ?>
                        </span>



                    </div>

                </div>

            </div>
            <div class="col-md-3">
                <div class="card p-3 ">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted">Active Leads</div>
                            <div class="kpi"><?= $activeLeads ?></div>
                        </div>

                        <?php if ($leadsChange !== null): ?>
                            <span class="kpi-badge <?= $leadsChange >= 0 ? 'up' : 'down' ?> info-tooltip"
                                data-tip="Percentage change in active leads compared to last month">
                                <?= $leadsChange >= 0 ? '▲' : '▼' ?> <?= abs($leadsChange) ?>%
                            </span>
                        <?php endif; ?>


                    </div>

                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted">Conversion</div>
                            <div class="kpi"><?= $conversion ?>%</div>
                        </div>

                        <?php if ($conversionChange !== null): ?>
                            <span class="kpi-badge <?= $conversionChange >= 0 ? 'up' : 'down' ?> info-tooltip"
                                data-tip="Change compared to last month">
                                <?= $conversionChange >= 0 ? '▲' : '▼' ?> <?= abs($conversionChange) ?> pts
                            </span>
                        <?php endif; ?>


                    </div>

                </div>
            </div>

        </div>


        <div class="row g-4 mb-4 align-items-stretch">

            <!-- GAUGE -->
            <div class="col-lg-3 col-md-6">
                <div class="card p-4 gauge-box h-100">
                    <h6 class="section-title">Revenue Target</h6>

                    <div class="position-relative text-center" style="height:220px">
                        <canvas id="gauge"></canvas>

                        <div style="
                position:absolute;
                top:55%;
                left:50%;
                transform:translate(-50%,-50%);
                text-align:center;
            ">
                            <div style="font-size:26px;font-weight:800;">
                                $<?= number_format($wonRevenue) ?>
                            </div>
                        </div>
                    </div>

                    <!-- 👇 fills the empty space perfectly -->
                    <div class="text-center mt-3">
                        <div style="font-size:18px;font-weight:600;color:#1e293b;">
                            <?= $progress ?>% of $<?= number_format($TARGET) ?>
                        </div>
                    </div>

                </div>
            </div>


            <!-- PIPELINE -->
            <div class="col-lg-5 col-md-6">
                <div class="card p-4 chart-box h-100 hero">
                    <h6 class="section-title">Pipeline Distribution</h6>
                    <canvas id="pipeline"></canvas>
                </div>
            </div>

            <!-- MONTHLY -->
            <div class="col-lg-4 col-md-12">
                <div class="card p-4 chart-box h-100">
                    <h6 class="section-title">Monthly Revenue</h6>
                    <canvas id="monthly"></canvas>
                </div>
            </div>

        </div>


        <!-- TABLES -->
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card p-4">
                    <h6 class="section-title">Latest Deals</h6>
                    <table class="table table-sm">
                        <?php foreach ($latestDeals as $d): ?>
                            <tr>
                                <td><?= $d['title'] ?></td>
                                <td>$<?= $d['value'] ?></td>
                                <td><?= $d['stage'] ?></td>
                            </tr>
                        <?php endforeach ?>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-3">
                    <h6>Pending Tasks</h6>
                    <table class="table table-sm">
                        <?php foreach ($pendingTasks as $t): ?>
                            <tr>
                                <td><?= $t['title'] ?></td>
                                <td><?= $t['name'] ?></td>
                                <td><?= $t['due_date'] ?></td>
                            </tr>
                        <?php endforeach ?>
                    </table>
                </div>
            </div>
        </div>

    </div>
    <script>
        const COLORS = {
            primary: '#5b7cfa',
            success: '#22c55e',
            warning: '#facc15',
            danger: '#ef4444'
        };
    </script>

    <script>
        const ctx = document.getElementById('gauge').getContext('2d');

        // 🎨 Create gradient
        const gradient = ctx.createLinearGradient(0, 0, ctx.canvas.width, 0);
        gradient.addColorStop(0, '#ef4444'); // red
        gradient.addColorStop(0.5, '#facc15'); // yellow
        gradient.addColorStop(1, '#22c55e'); // green

        const progress = <?= $progress ?>;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [progress, 100 - progress],
                    backgroundColor: [
                        gradient, // colorful arc
                        '#e5e7eb' // gray remainder
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '82%',
                rotation: -90,
                circumference: 180,
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
                    }
                }
            }
        });
        // const gaugeColors = progress < 40 ?
        //     ['rgba(198, 122, 122, 0.9)', 'rgba(226, 232, 240, 0.6)'] :
        //     progress < 75 ?
        //     ['rgba(219, 181, 9, 0.9)', 'rgba(226, 232, 240, 0.6)'] :
        //     ['rgba(34, 197, 94, 0.9)', 'rgba(226, 232, 240, 0.6)'];


        // new Chart(gauge, {
        //     type: 'doughnut',
        //     data: {
        //         datasets: [{
        //             data: [progress, 100 - progress],
        //             // backgroundColor: gaugeColors,
        //             backgroundColor: gaugeColors.map(c => c.replace('0.9', '1')),

        //             borderWidth: 0
        //         }]
        //     },
        //     options: {
        //         cutout: '78%',
        //         rotation: -90,
        //         circumference: 180,
        //         responsive: true,
        //         maintainAspectRatio: false,
        //         plugins: {
        //             legend: {
        //                 display: false
        //             },
        //             tooltip: {
        //                 enabled: false
        //             }
        //         }
        //     }
        // });



        new Chart(pipeline, {
            type: 'bar',
            data: {
                labels: <?= json_encode($stageLabels) ?>,
                datasets: [{
                    data: <?= json_encode($stageValues) ?>,
                    backgroundColor: [
                        COLORS.success,
                        COLORS.warning,
                        COLORS.primary,
                        COLORS.danger
                    ],
                    borderRadius: 6,
                    barPercentage: 0.5, // 👈 slimmer bars
                    categoryPercentage: 0.6 // 👈 more space between categories
                    //   borderSkipped: false
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(0,0,0,.05)'
                        }
                    }
                },
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    formatter: v => '$' + v.toLocaleString()
                }
            }
        });


        new Chart(monthly, {
            type: 'line',
            data: {
                labels: <?= json_encode($months) ?>,
                datasets: [{
                    data: <?= json_encode($monthValues) ?>,
                    borderColor: COLORS.primary,
                    backgroundColor: 'rgba(91,124,250,.15)',
                    fill: true,
                    barPercentage: 0.6,
                    categoryPercentage: 0.6,
                    tension: .4
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
    <?php include('includes/script.php'); ?>
</body>

</html>