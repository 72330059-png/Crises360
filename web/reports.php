<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$dateFrom = isset($_GET['from']) ? $_GET['from'] : date('Y-m-d', strtotime('-30 days'));
$dateTo   = isset($_GET['to'])   ? $_GET['to']   : date('Y-m-d');

$dateFrom = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom) ? $dateFrom : date('Y-m-d', strtotime('-30 days'));
$dateTo   = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)   ? $dateTo   : date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reports &amp; Statistics</title>
    <?php include('includes/header.php'); ?>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800;1,9..40,400&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
    <style>
        .rp *, .rp *::before, .rp *::after { box-sizing: border-box; }
        .rp-modal-overlay *, .rp-modal-overlay *::before, .rp-modal-overlay *::after { box-sizing: border-box; }
        :root {
            --rp-navy:#1b2559; --rp-indigo:#4f46e5; --rp-indigo-light:#6366f1;
            --rp-gray-50:#f8f9fc; --rp-gray-100:#f3f4f6; --rp-gray-200:#e8eaf2;
            --rp-gray-300:#d1d5db; --rp-gray-500:#9ca3af; --rp-gray-600:#6b7280;
            --rp-gray-700:#374151; --rp-green:#22c55e; --rp-amber:#f59e0b;
            --rp-red:#ef4444; --rp-orange:#f97316;
            --rp-radius-card:16px; --rp-radius-input:10px;
            --rp-shadow-card:0 1px 3px rgba(0,0,0,.06),0 1px 2px rgba(0,0,0,.04);
        }
        .rp { padding:1.5rem 2rem; width:100%; font-family:'DM Sans',sans-serif; color:var(--rp-navy); font-size:14px; }
        .rp-header { display:flex; align-items:flex-start; justify-content:space-between; gap:20px; margin-bottom:1.6rem; }
        .rp-header h1 { font-size:2rem; font-weight:700; color:var(--rp-navy); letter-spacing:-.4px; line-height:1.1; margin:0; }
        .rp-header .rp-sub { font-size:.8rem; color:var(--rp-gray-500); margin-top:4px; }
        .rp-sev-badge { display:inline-flex; align-items:center; gap:10px; padding:10px 18px; border-radius:14px; font-weight:800; font-size:1rem; border:2.5px solid; white-space:nowrap; flex-shrink:0; font-family:'DM Sans',sans-serif; }
        .rp-sev-badge .rp-sev-num { font-size:1.8rem; font-weight:800; line-height:1; }
        .rp-sev-badge .rp-sev-sep { opacity:.35; font-size:1.2rem; }
        .rp-sev-badge .rp-sev-lbl { font-size:.88rem; font-weight:700; }
        .rp-sev-badge .rp-sev-sub { font-size:.62rem; opacity:.65; }
        .rp-sev-badge.rp-high     { background:#fed7aa; color:#c2410c; border-color:#fb923c; }
        .rp-sev-badge.rp-critical { background:#fee2e2; color:#dc2626; border-color:#fca5a5; }
        .rp-sev-badge.rp-medium   { background:#fef3c7; color:#b45309; border-color:#fcd34d; }
        .rp-sev-badge.rp-low      { background:#dcfce7; color:#16a34a; border-color:#86efac; }
        .rp-filter-bar { display:flex; align-items:flex-end; gap:10px; flex-wrap:wrap; margin-bottom:1.5rem; }
        .rp-filter-group { display:flex; flex-direction:column; gap:5px; }
        .rp-filter-label { font-size:.62rem; font-weight:700; color:var(--rp-indigo); text-transform:uppercase; letter-spacing:.7px; }
        .rp-filter-input { display:flex; align-items:center; gap:8px; background:#fff; border:1.5px solid var(--rp-gray-200); border-radius:var(--rp-radius-input); padding:8px 13px; font-size:.82rem; color:var(--rp-gray-700); font-family:'DM Sans',sans-serif; transition:border-color .15s; cursor:pointer; }
        .rp-filter-input:focus-within { border-color:var(--rp-indigo); }
        .rp-filter-input i { color:var(--rp-indigo); font-size:.9rem; flex-shrink:0; }
        .rp-filter-input input[type="date"], .rp-filter-input select { border:none; background:transparent; font-family:'DM Sans',sans-serif; font-size:.82rem; color:var(--rp-gray-700); outline:none; cursor:pointer; }
        .rp-filter-input input[type="date"] { width:130px; }
        .rp-filter-divider { width:1px; height:38px; background:var(--rp-gray-200); margin:0 4px; align-self:flex-end; }
        .rp-filter-sep-label { align-self:flex-end; padding-bottom:10px; font-size:.75rem; color:var(--rp-gray-500); font-weight:600; }
        .rp-btn-generate { background:linear-gradient(135deg,var(--rp-indigo),var(--rp-indigo-light)); color:#fff; border:none; border-radius:var(--rp-radius-input); padding:9px 22px; font-size:.82rem; font-weight:600; font-family:'DM Sans',sans-serif; display:flex; align-items:center; gap:7px; cursor:pointer; box-shadow:0 2px 10px rgba(99,102,241,.3); transition:opacity .2s,transform .1s; white-space:nowrap; }
        .rp-btn-generate:hover { opacity:.88; transform:translateY(-1px); }
        .rp-btn-export { background:#fff; color:#16a34a; border:1.5px solid #d1fae5; border-radius:var(--rp-radius-input); padding:9px 18px; font-size:.82rem; font-weight:600; font-family:'DM Sans',sans-serif; display:flex; align-items:center; gap:6px; cursor:pointer; transition:background .15s; white-space:nowrap; text-decoration:none; }
        .rp-btn-export:hover { background:#f0fdf4; }
        .rp-filter-actions { display:flex; gap:8px; align-items:center; margin-left:auto; }
        .rp-overview-row { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:14px; }
        .rp-ov-card { background:#fff; border:1px solid var(--rp-gray-200); border-radius:var(--rp-radius-card); padding:1rem 1.1rem; box-shadow:var(--rp-shadow-card); }
        .rp-ov-title { font-size:.78rem; font-weight:700; color:var(--rp-navy); }
        .rp-ov-sub { font-size:.68rem; color:var(--rp-gray-500); margin-top:2px; margin-bottom:10px; }
        .rp-ov-donut-wrap { display:flex; align-items:center; gap:12px; }
        .rp-ov-legend { display:flex; flex-direction:column; gap:5px; flex:1; }
        .rp-ov-leg-item { display:flex; align-items:center; gap:5px; font-size:.68rem; color:var(--rp-gray-600); }
        .rp-ov-leg-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
        .rp-ov-center-val { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); text-align:center; white-space:nowrap; pointer-events:none; }
        .rp-ov-center-num { font-size:.82rem; font-weight:800; color:var(--rp-navy); line-height:1; }
        .rp-ov-center-lbl { font-size:.58rem; color:var(--rp-gray-500); margin-top:2px; }
        .rp-ov-total-row { display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--rp-gray-100); margin-top:8px; padding-top:8px; font-size:.72rem; color:var(--rp-gray-600); }
        .rp-ov-total-val { font-weight:700; color:var(--rp-navy); }
        .rp-cas-heart { width:38px; height:38px; border-radius:50%; background:#fee2e2; display:flex; align-items:center; justify-content:center; margin:6px auto 12px; }
        .rp-cas-heart i { color:var(--rp-red); font-size:1.05rem; }
        .rp-cas-row { display:flex; justify-content:space-between; align-items:center; padding:7px 0; border-bottom:1px solid #fafafa; }
        .rp-cas-label { font-size:.8rem; color:var(--rp-gray-600); }
        .rp-cas-num { font-size:1.4rem; font-weight:800; line-height:1; }
        .rp-cas-num.rp-red { color:var(--rp-red); }
        .rp-cas-num.rp-gray { color:var(--rp-gray-700); }
        .rp-cas-num.rp-clickable { cursor:pointer; transition:opacity .15s; }
        .rp-cas-num.rp-clickable:hover { opacity:.7; }
        .rp-cas-total { display:flex; justify-content:space-between; border-top:2px solid var(--rp-gray-100); padding-top:8px; margin-top:6px; font-size:.8rem; font-weight:700; color:var(--rp-navy); }
        .rp-charts-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; }
        .rp-chart-card { background:#fff; border:1px solid var(--rp-gray-200); border-radius:var(--rp-radius-card); padding:1.2rem 1.3rem; display:flex; flex-direction:column; box-shadow:var(--rp-shadow-card); }
        .rp-cc-title { font-size:.88rem; font-weight:700; color:var(--rp-navy); }
        .rp-cc-sub { font-size:.7rem; color:var(--rp-gray-500); margin-top:2px; margin-bottom:14px; }
        .rp-chart-canvas-wrap { position:relative; width:100%; flex:1; min-height:260px; }
        .rp-chart-legend { display:flex; gap:14px; margin-top:10px; flex-wrap:wrap; }
        .rp-leg-item { display:flex; align-items:center; gap:5px; font-size:.72rem; color:var(--rp-gray-600); }
        .rp-leg-dot-sq { width:9px; height:9px; border-radius:2px; flex-shrink:0; }
        .rp-leg-dot-ci { width:9px; height:9px; border-radius:50%; flex-shrink:0; }
        .rp-inc-stat-row { display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; margin-bottom:12px; }
        .rp-inc-stat-item { background:var(--rp-gray-50); border:1px solid var(--rp-gray-200); border-radius:10px; padding:10px 12px; display:flex; align-items:center; gap:10px; }
        .rp-inc-stat-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
        .rp-inc-stat-num { font-size:1.3rem; font-weight:800; color:var(--rp-navy); line-height:1; }
        .rp-inc-stat-lbl { font-size:.65rem; color:var(--rp-gray-500); margin-top:2px; text-transform:uppercase; letter-spacing:.3px; }
        .rp-info-bar { display:flex; align-items:center; gap:8px; background:#eff6ff; border-radius:10px; padding:10px 14px; font-size:.75rem; color:#3b82f6; margin-top:14px; }
        /* modals */
        .rp-modal-overlay { display:none; position:fixed; inset:0; background:rgba(15,17,35,.45); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center; }
        .rp-modal-overlay.open { display:flex; }
        .rp-modal-box { background:#fff; border-radius:20px; width:480px; max-width:92vw; max-height:82vh; display:flex; flex-direction:column; box-shadow:0 24px 60px rgba(0,0,0,.18); animation:rpPop .22s cubic-bezier(.34,1.56,.64,1); font-family:'DM Sans',sans-serif; }
        @keyframes rpPop { from{transform:scale(.88);opacity:0} to{transform:scale(1);opacity:1} }
        .rp-modal-head { display:flex; align-items:center; justify-content:space-between; padding:18px 20px 14px; border-bottom:1px solid var(--rp-gray-100); }
        .rp-modal-head h3 { font-size:.95rem; font-weight:800; color:var(--rp-navy); margin:0; }
        .rp-modal-head p { font-size:.72rem; color:var(--rp-gray-500); margin-top:2px; margin-bottom:0; }
        .rp-modal-close { width:30px; height:30px; border-radius:8px; border:1px solid var(--rp-gray-200); background:var(--rp-gray-50); cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:.9rem; color:var(--rp-gray-600); transition:all .15s; }
        .rp-modal-close:hover { background:#fee2e2; color:#dc2626; border-color:#fca5a5; }
        .rp-modal-body { overflow-y:auto; padding:14px 20px 18px; flex:1; }
        .rp-modal-foot { padding:12px 20px; border-top:1px solid var(--rp-gray-100); display:flex; align-items:center; justify-content:space-between; font-size:.72rem; color:var(--rp-gray-500); }
        .rp-modal-foot strong { color:var(--rp-indigo); font-weight:600; }
        .rp-muni-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
        .rp-muni-item { display:flex; align-items:center; justify-content:space-between; background:var(--rp-gray-50); border:1px solid var(--rp-gray-100); border-radius:10px; padding:9px 12px; gap:8px; }
        .rp-muni-name { font-size:.78rem; font-weight:600; color:var(--rp-navy); }
        .rp-muni-ppl { font-size:.7rem; color:var(--rp-gray-600); margin-top:1px; }
        .rp-badge { padding:2px 9px; border-radius:20px; font-size:.62rem; font-weight:700; white-space:nowrap; flex-shrink:0; }
        .rp-badge.rp-badge-high   { background:#fee2e2; color:#dc2626; }
        .rp-badge.rp-badge-medium { background:#fef3c7; color:#b45309; }
        .rp-badge.rp-badge-low    { background:#dcfce7; color:#16a34a; }
        .rp-cas-bd-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:20px; }
        .rp-cas-bd-item { background:var(--rp-gray-50); border:1.5px solid var(--rp-gray-200); border-radius:12px; padding:18px 10px 14px; text-align:center; }
        .rp-cas-bd-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; margin:0 auto 10px; font-size:1.1rem; }
        .rp-cas-bd-num { font-size:1.5rem; font-weight:800; color:var(--rp-navy); line-height:1; }
        .rp-cas-bd-lbl { font-size:.68rem; font-weight:600; color:var(--rp-gray-500); margin-top:5px; text-transform:uppercase; letter-spacing:.4px; }
        .rp-cas-bd-bars { display:flex; flex-direction:column; gap:12px; }
        .rp-cas-bd-bar-row { display:flex; align-items:center; gap:12px; font-size:.78rem; }
        .rp-cas-bd-bar-label { width:65px; flex-shrink:0; font-weight:600; color:var(--rp-navy); }
        .rp-cas-bd-bar-track { flex:1; height:7px; background:#eef0f7; border-radius:99px; overflow:hidden; }
        .rp-cas-bd-bar-fill { height:100%; border-radius:99px; transition:width .6s ease; }
        .rp-cas-bd-bar-pct { width:36px; text-align:right; flex-shrink:0; font-weight:700; color:var(--rp-navy); }
        @media(max-width:1100px){.rp-charts-grid{grid-template-columns:1fr 1fr}}
        @media(max-width:900px) {.rp-overview-row{grid-template-columns:repeat(2,1fr)}.rp-charts-grid{grid-template-columns:1fr}}
        @media(max-width:560px) {.rp-overview-row{grid-template-columns:1fr}}
    </style>
</head>
<body>
<?php include('includes/sidebar.php'); ?>
<?php include('includes/nav.php'); ?>

<div class="main-content">
<div class="rp">

    <!-- HEADER -->
    <div class="rp-header">
        <div>
            <h1>Reports &amp; Analytics</h1>
            <p class="rp-sub">Analyze incident performance, resources, needs and response over time.</p>
        </div>
        <div id="rpSevBadgeWrap"></div>
    </div>

    <!-- FILTER BAR -->
    <div class="rp-filter-bar">
        <div class="rp-filter-group">
            <span class="rp-filter-label">From Date</span>
            <div class="rp-filter-input">
                <i class="bi bi-calendar-event"></i>
                <input type="date" id="rpDateFrom" value="<?= htmlspecialchars($dateFrom) ?>">
            </div>
        </div>
        <span class="rp-filter-sep-label">—</span>
        <div class="rp-filter-group">
            <span class="rp-filter-label">To Date</span>
            <div class="rp-filter-input">
                <i class="bi bi-calendar-event"></i>
                <input type="date" id="rpDateTo" value="<?= htmlspecialchars($dateTo) ?>">
            </div>
        </div>
        <div class="rp-filter-divider"></div>
        <div class="rp-filter-group">
            <span class="rp-filter-label">Quick Range</span>
            <div class="rp-filter-input">
                <i class="bi bi-clock-history"></i>
                <select id="rpQuickRange" onchange="rpApplyQuickRange(this.value)">
                    <option value="">Custom</option>
                    <option value="7">Last 7 Days</option>
                    <option value="30">Last 30 Days</option>
                    <option value="90">Last 3 Months</option>
                    <option value="180">Last 6 Months</option>
                    <option value="365">This Year</option>
                </select>
            </div>
        </div>
        <div class="rp-filter-divider"></div>
        <div class="rp-filter-actions">
            <button class="rp-btn-generate" onclick="rpGoGenerate()">
                <i class="bi bi-bar-chart-fill"></i> Generate Report
            </button>
            <a class="rp-btn-export" href="export_report.php?from=<?= urlencode($dateFrom) ?>&to=<?= urlencode($dateTo) ?>">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- OVERVIEW CARDS -->
    <div class="rp-overview-row" id="rpOverviewRow"></div>

    <!-- CHARTS -->
    <div class="rp-charts-grid">

        <div class="rp-chart-card">
            <div class="rp-cc-title">Needs Fulfillment Overview</div>
            <div class="rp-cc-sub">Tracking of needs and request fulfillment by category</div>
            <div class="rp-chart-canvas-wrap"><canvas id="rpNeedsChart"></canvas></div>
            <div class="rp-chart-legend">
                <span class="rp-leg-item"><span class="rp-leg-dot-sq" style="background:#22c55e"></span>Fulfilled</span>
                <span class="rp-leg-item"><span class="rp-leg-dot-sq" style="background:#f59e0b"></span>In Progress</span>
                <span class="rp-leg-item"><span class="rp-leg-dot-sq" style="background:#ef4444"></span>Rejected / Not Fulfilled</span>
            </div>
        </div>

        <div class="rp-chart-card">
            <div class="rp-cc-title">Alerts &amp; Teams Over Time</div>
            <div class="rp-cc-sub">Daily count of alerts and hospital teams in selected period</div>
            <div class="rp-chart-canvas-wrap"><canvas id="rpAlertsChart"></canvas></div>
            <div class="rp-chart-legend">
                <span class="rp-leg-item"><span class="rp-leg-dot-ci" style="background:#ef4444"></span>Alerts</span>
                <span class="rp-leg-item"><span class="rp-leg-dot-ci" style="background:#6366f1"></span>Teams</span>
            </div>
        </div>

        <div class="rp-chart-card">
            <div class="rp-cc-title">Incidents Overview</div>
            <div class="rp-cc-sub">Filtered by selected date range — cards &amp; chart update together</div>
            <div class="rp-inc-stat-row" id="rpIncStatRow"></div>
            <div class="rp-chart-canvas-wrap" style="min-height:180px;flex:1"><canvas id="rpIncidentsChart"></canvas></div>
            <div class="rp-chart-legend">
                <span class="rp-leg-item"><span class="rp-leg-dot-sq" style="background:#4f46e5"></span>Active</span>
                <span class="rp-leg-item"><span class="rp-leg-dot-sq" style="background:#22c55e"></span>Resolved</span>
            </div>
        </div>

    </div>

    <div class="rp-info-bar">
        <i class="bi bi-info-circle-fill"></i>
        All data reflects the selected date range. Click <strong>Generate Report</strong> to reload with new dates.
    </div>

</div>
</div>

<!-- ALERT / TEAM MODAL -->
<div class="rp-modal-overlay" id="rpMuniModal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="rp-modal-box" id="rpMuniModalBox">
        <div class="rp-modal-head">
            <div>
                <h3 id="rpModalTitle">Details</h3>
                <p id="rpModalSub"></p>
            </div>
            <button class="rp-modal-close" onclick="document.getElementById('rpMuniModal').classList.remove('open')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="rp-modal-body"><div class="rp-muni-grid" id="rpMuniGrid"></div></div>
        <div class="rp-modal-foot">
            <span id="rpModalFootNote">Data for selected date point</span>
            <strong id="rpModalFootStat"></strong>
        </div>
    </div>
</div>

<!-- CASUALTY MODAL -->
<div class="rp-modal-overlay" id="rpCasModal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="rp-modal-box" id="rpCasModalBox">
        <div class="rp-modal-head">
            <div>
                <h3 id="rpCasModalTitle">Casualty Breakdown</h3>
                <p id="rpCasModalSub"></p>
            </div>
            <button class="rp-modal-close" onclick="document.getElementById('rpCasModal').classList.remove('open')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="rp-modal-body" id="rpCasModalBody"></div>
    </div>
</div>

<?php include('includes/script.php'); ?>

<script>
 
const RP_FROM = "<?= htmlspecialchars($dateFrom) ?>";
const RP_TO   = "<?= htmlspecialchars($dateTo) ?>";

let RP_DATA = {};
let RP_TIME_LABELS = [];
let RP_RAW_DATES = [];

let rpNeedsInst = null, rpAlertsInst = null, rpIncInst = null;

async function rpFetch(action) {
    try {
        const res = await fetch(`actions/reports.php?action=${action}&from=${encodeURIComponent(RP_FROM)}&to=${encodeURIComponent(RP_TO)}`);
        const json = await res.json();
        if (json.status !== 'success') {
            console.error('Action failed:', action, json.message);
            return null;
        }
        return json.data;
    } catch (err) {
        console.error('Fetch error for action:', action, err);
        return null;
    }
}

function rpGoGenerate() {
    const from = document.getElementById('rpDateFrom').value;
    const to   = document.getElementById('rpDateTo').value;
    window.location.href = 'reports.php?from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to);
}

function rpApplyQuickRange(days) {
    if (!days) return;
    const to   = new Date();
    const from = new Date();
    from.setDate(from.getDate() - parseInt(days));
    document.getElementById('rpDateTo').value   = to.toISOString().slice(0, 10);
    document.getElementById('rpDateFrom').value = from.toISOString().slice(0, 10);
}

// SEVERITY BADGE 
const RP_SEV_META = {
    low:      { icon:'bi-check-circle-fill',       label:'Low Severity'      },
    medium:   { icon:'bi-exclamation-circle-fill',  label:'Medium Severity'   },
    high:     { icon:'bi-exclamation-triangle-fill',label:'High Severity'     },
    critical: { icon:'bi-x-octagon-fill',           label:'Critical Severity' },
};
function rpRenderSevBadge(score, cls) {
    const m = RP_SEV_META[cls] || RP_SEV_META.medium;
    document.getElementById('rpSevBadgeWrap').innerHTML = `
        <div class="rp-sev-badge rp-${cls}">
          <i class="bi ${m.icon}" style="font-size:1.3rem;flex-shrink:0"></i>
          <span class="rp-sev-num">${score}</span>
          <span class="rp-sev-sep">|</span>
          <span style="display:flex;flex-direction:column;gap:1px">
            <span class="rp-sev-lbl">${m.label}</span>
            <span class="rp-sev-sub">out of 100</span>
          </span>
        </div>`;
}

function rpPct(v, t) { return t ? Math.round(v / t * 100) : 0; }

function rpMiniDonut(id, data, colors) {
    new Chart(document.getElementById(id), {
        type: 'doughnut',
        data: { datasets: [{ data, backgroundColor: colors, borderWidth: 2, borderColor: '#fff', hoverOffset: 3 }] },
        options: { cutout: '65%', plugins: { legend: { display: false }, tooltip: { enabled: false } }, responsive: false }
    });
}

//  OVERVIEW CARDS 
function rpRenderOverview(d) {
    const sh = d.shelter, ho = d.hospital, ca = d.casualty, ro = d.roads;
    document.getElementById('rpOverviewRow').innerHTML = `
    <div class="rp-ov-card">
      <div class="rp-ov-title">Shelter Occupancy</div>
      <div class="rp-ov-sub">Capacity &amp; occupancy across all shelters</div>
      <div class="rp-ov-donut-wrap">
        <div style="position:relative;width:100px;height:100px;flex-shrink:0">
          <canvas id="rpShelterDonut" width="100" height="100"></canvas>
          <div class="rp-ov-center-val">
            <div class="rp-ov-center-num">${sh.total >= 1000 ? (sh.total/1000).toFixed(1)+'k' : sh.total}</div>
            <div class="rp-ov-center-lbl">Total</div>
          </div>
        </div>
        <div class="rp-ov-legend">
          <span class="rp-ov-leg-item"><span class="rp-ov-leg-dot" style="background:#6366f1"></span>Occupied — ${sh.occupied.toLocaleString()} (${rpPct(sh.occupied,sh.total)}%)</span>
          <span class="rp-ov-leg-item"><span class="rp-ov-leg-dot" style="background:#22c55e"></span>Available — ${sh.available.toLocaleString()} (${rpPct(sh.available,sh.total)}%)</span>
          <span class="rp-ov-leg-item"><span class="rp-ov-leg-dot" style="background:#f59e0b"></span>Other — ${sh.maintenance.toLocaleString()} (${rpPct(sh.maintenance,sh.total)}%)</span>
        </div>
      </div>
    </div>

    <div class="rp-ov-card">
      <div class="rp-ov-title">Hospital Status</div>
      <div class="rp-ov-sub">Hospitals by operational status</div>
      <div class="rp-ov-donut-wrap">
        <div style="position:relative;width:100px;height:100px;flex-shrink:0">
          <canvas id="rpHospDonut" width="100" height="100"></canvas>
        </div>
        <div class="rp-ov-legend">
          <span class="rp-ov-leg-item"><span class="rp-ov-leg-dot" style="background:#22c55e"></span>Safe — ${ho.stable} (${rpPct(ho.stable,ho.total)}%)</span>
          <span class="rp-ov-leg-item"><span class="rp-ov-leg-dot" style="background:#f59e0b"></span>Warning — ${ho.warning} (${rpPct(ho.warning,ho.total)}%)</span>
          <span class="rp-ov-leg-item"><span class="rp-ov-leg-dot" style="background:#ef4444"></span>Dangerous — ${ho.dangerous} (${rpPct(ho.dangerous,ho.total)}%)</span>
        </div>
      </div>
      <div class="rp-ov-total-row"><span>Total Hospitals</span><span class="rp-ov-total-val">${ho.total}</span></div>
    </div>

    <div class="rp-ov-card">
      <div class="rp-ov-title">Hospital Casualties</div>
      <div class="rp-ov-sub">Total casualties across all hospitals</div>
      <div style="text-align:center;margin:8px 0 12px"><div class="rp-cas-heart"><i class="bi bi-heart-pulse-fill"></i></div></div>
      <div class="rp-cas-row">
        <span class="rp-cas-label">Injured</span>
        <span class="rp-cas-num rp-red rp-clickable" onclick="rpOpenCasModal('injured')" title="Click for breakdown">${ca.injured.toLocaleString()}</span>
      </div>
      <div class="rp-cas-row">
        <span class="rp-cas-label">Martyrs</span>
        <span class="rp-cas-num rp-gray rp-clickable" onclick="rpOpenCasModal('martyrs')" title="Click for breakdown">${ca.martyrs.toLocaleString()}</span>
      </div>
      <div class="rp-cas-total"><span>Total</span><span>${(ca.injured+ca.martyrs).toLocaleString()}</span></div>
    </div>

    <div class="rp-ov-card">
      <div class="rp-ov-title">Roads Status</div>
      <div class="rp-ov-sub">Roads and routes status summary</div>
      <div class="rp-ov-donut-wrap">
        <div style="position:relative;width:100px;height:100px;flex-shrink:0">
          <canvas id="rpRoadsDonut" width="100" height="100"></canvas>
        </div>
        <div class="rp-ov-legend">
          <span class="rp-ov-leg-item"><span class="rp-ov-leg-dot" style="background:#22c55e"></span>Safe/Open — ${ro.safe} (${rpPct(ro.safe,ro.total)}%)</span>
          <span class="rp-ov-leg-item"><span class="rp-ov-leg-dot" style="background:#f59e0b"></span>Restricted — ${ro.restricted} (${rpPct(ro.restricted,ro.total)}%)</span>
          <span class="rp-ov-leg-item"><span class="rp-ov-leg-dot" style="background:#ef4444"></span>Closed/Danger — ${ro.closed} (${rpPct(ro.closed,ro.total)}%)</span>
        </div>
      </div>
      <div class="rp-ov-total-row"><span>Total Roads</span><span class="rp-ov-total-val">${ro.total}</span></div>
    </div>`;

    rpMiniDonut('rpShelterDonut', [sh.occupied, sh.available, sh.maintenance], ['#6366f1','#22c55e','#f59e0b']);
    rpMiniDonut('rpHospDonut',    [ho.stable, ho.warning, ho.dangerous],        ['#22c55e','#f59e0b','#ef4444']);
    rpMiniDonut('rpRoadsDonut',   [ro.safe, ro.restricted, ro.closed],          ['#22c55e','#f59e0b','#ef4444']);
}

// NEEDS CHART 
function rpRenderNeeds(d) {
    if (rpNeedsInst) rpNeedsInst.destroy();
    const needs = d.needs;
    const manyLabels = needs.labels.length > 6;
    rpNeedsInst = new Chart(document.getElementById('rpNeedsChart'), {
        type: 'bar',
        data: {
            labels: needs.labels,
            datasets: [
                { label:'Fulfilled',                data: needs.fulfilled,    backgroundColor: '#22c55e', borderRadius: 4, borderSkipped: false },
                { label:'In Progress',              data: needs.inProgress,   backgroundColor: '#f59e0b', borderRadius: 4, borderSkipped: false },
                { label:'Rejected / Not Fulfilled', data: needs.notFulfilled, backgroundColor: '#ef4444', borderRadius: 4, borderSkipped: false },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: (items) => items[0].label,
                        afterBody: (items) => {
                            const i = items[0].dataIndex;
                            const f = needs.fulfilled[i]    || 0;
                            const p = needs.inProgress[i]   || 0;
                            const r = needs.notFulfilled[i] || 0;
                            return ['Total: '+(f+p+r), 'Fulfilled: '+f, 'In Progress: '+p, 'Rejected/Not Fulfilled: '+r];
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: '#9ca3af',
                        font: { size: manyLabels ? 9 : 11 },
                        maxRotation: manyLabels ? 35 : 0,
                        minRotation: manyLabels ? 35 : 0
                    }
                },
                y: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { color: '#9ca3af', font: { size: 11 }, stepSize: 1 }, beginAtZero: true }
            }
        }
    });
}

// ALERTS & TEAMS LINE CHART 
function rpRenderAlerts(d) {
    if (rpAlertsInst) rpAlertsInst.destroy();
    rpAlertsInst = new Chart(document.getElementById('rpAlertsChart'), {
        type: 'line',
        data: {
            labels: RP_TIME_LABELS,
            datasets: [
                { label:'Alerts', data: d.alerts, borderColor: '#ef4444', fill: false, tension: .4, pointRadius: 4, pointHoverRadius: 7, pointBackgroundColor: '#ef4444', pointBorderColor: '#fff', pointBorderWidth: 2, borderWidth: 2 },
                { label:'Teams',  data: d.teams,  borderColor: '#6366f1', fill: false, tension: .4, pointRadius: 4, pointHoverRadius: 7, pointBackgroundColor: '#6366f1', pointBorderColor: '#fff', pointBorderWidth: 2, borderWidth: 2 },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { enabled: true } },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#9ca3af', font: { size: 10 }, maxTicksLimit: 8 } },
                y: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { color: '#9ca3af', font: { size: 10 }, stepSize: 1 }, beginAtZero: true }
            },
            onClick: (evt, elems) => {
                if (!elems.length) return;
                const di = elems[0].datasetIndex, pi = elems[0].index;
                if (di === 0) rpOpenAlertsModal(pi, d);
                else          rpOpenTeamsModal(pi, d);
            }
        }
    });
}

// INCIDENTS CHART 
function rpRenderIncidents(d) {
    const inc = d.incidents;
    document.getElementById('rpIncStatRow').innerHTML = `
    <div class="rp-inc-stat-item">
      <div class="rp-inc-stat-icon" style="background:#eef2ff"><i class="bi bi-exclamation-triangle-fill" style="color:#4f46e5"></i></div>
      <div><div class="rp-inc-stat-num">${inc.total}</div><div class="rp-inc-stat-lbl">Total</div></div>
    </div>
    <div class="rp-inc-stat-item">
      <div class="rp-inc-stat-icon" style="background:#dcfce7"><i class="bi bi-check-circle-fill" style="color:#22c55e"></i></div>
      <div><div class="rp-inc-stat-num">${inc.resolved}</div><div class="rp-inc-stat-lbl">Resolved</div></div>
    </div>
    <div class="rp-inc-stat-item">
      <div class="rp-inc-stat-icon" style="background:#fef3c7"><i class="bi bi-search" style="color:#f59e0b"></i></div>
      <div><div class="rp-inc-stat-num">${inc.investigating}</div><div class="rp-inc-stat-lbl">In Progress</div></div>
    </div>`;

    if (rpIncInst) rpIncInst.destroy();
    rpIncInst = new Chart(document.getElementById('rpIncidentsChart'), {
        type: 'bar',
        data: {
            labels: RP_TIME_LABELS,
            datasets: [
                { label:'Active',   data: inc.byDay,         backgroundColor: '#4f46e5', borderRadius: 4, borderSkipped: false },
                { label:'Resolved', data: inc.resolvedByDay, backgroundColor: '#22c55e', borderRadius: 4, borderSkipped: false },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#9ca3af', font: { size: 10 }, maxTicksLimit: 8 }, stacked: true },
                y: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { color: '#9ca3af', font: { size: 10 }, stepSize: 1 }, stacked: true, beginAtZero: true }
            }
        }
    });
}

// MODAL HELPERS 
function rpSetAccent(color) {
    document.getElementById('rpMuniModalBox').style.borderTop = `4px solid ${color}`;
}

async function rpOpenAlertsModal(idx, d) {
    rpSetAccent('#ef4444');
    const total = d.alerts[idx];
    const label = RP_TIME_LABELS[idx];
    const actualDate = RP_RAW_DATES[idx]; 

    document.getElementById('rpModalTitle').textContent    = `Alerts — ${label}`;
    document.getElementById('rpModalSub').textContent      = `${total} alerts on this date`;
    document.getElementById('rpModalFootStat').textContent = `Peak: ${Math.max(...d.alerts)} alerts`;
    document.getElementById('rpMuniGrid').innerHTML        = `<p style="color:var(--rp-gray-500);font-size:.8rem;text-align:center;padding:20px">Loading...</p>`;
    document.getElementById('rpMuniModal').classList.add('open');

    const res = await fetch(`actions/reports.php?action=alerts_by_region&from=${actualDate}&to=${actualDate}`);
    const json = await res.json();
    const regions = json.status === 'success' ? json.data : [];

    let html = '';
    if (regions.length) {
        regions.forEach(r => {
            const badge = r.severity === 'Critical' ? 'rp-badge-high' : (r.severity === 'High' ? 'rp-badge-medium' : 'rp-badge-low');
            html += `<div class="rp-muni-item">
              <div>
                <div class="rp-muni-name" style="color:#ef4444"><i class="bi bi-bell-fill" style="font-size:.7rem;margin-right:4px"></i>${r.region}</div>
                <div class="rp-muni-ppl">${r.cnt} alerts · ${r.severity}</div>
              </div>
              <span class="rp-badge ${badge}">${r.cnt}</span>
            </div>`;
        });
    } else {
        html = `<p style="color:var(--rp-gray-500);font-size:.8rem;text-align:center;padding:20px">No alerts on this date.</p>`;
    }
    document.getElementById('rpMuniGrid').innerHTML = html;
}

async function rpOpenTeamsModal(idx, d) {
    rpSetAccent('#6366f1');
    const total = d.teams[idx];
    const actualDate = RP_RAW_DATES[idx];

    document.getElementById('rpModalTitle').textContent    = `Hospital Teams — ${RP_TIME_LABELS[idx]}`;
    document.getElementById('rpModalSub').textContent      = `${total} teams added on this date`;
    document.getElementById('rpModalFootStat').textContent = `Peak: ${Math.max(...d.teams)} teams`;
    document.getElementById('rpMuniGrid').innerHTML        = `<p style="color:var(--rp-gray-500);font-size:.8rem;text-align:center;padding:20px">Loading...</p>`;
    document.getElementById('rpMuniModal').classList.add('open');

   const res = await fetch(`actions/reports.php?action=teams_by_date&date=${actualDate}`);
    const json = await res.json();
    const teams = json.status === 'success' ? json.data : [];

    const statusColor = { 'Available':'#22c55e', 'On Mission':'#ef4444', 'Busy':'#f59e0b' };
    let html = '';
    if (teams.length) {
        teams.forEach(t => {
            const color = statusColor[t.status] || '#9ca3af';
            html += `<div class="rp-muni-item">
              <div>
                <div class="rp-muni-name" style="color:#6366f1"><i class="bi bi-heart-pulse-fill" style="font-size:.7rem;margin-right:4px"></i>${t.team_name}</div>
                <div class="rp-muni-ppl">${t.current_location}</div>
              </div>
              <div style="background:#ede9fe;color:${color};padding:2px 9px;border-radius:20px;font-size:.62rem;font-weight:700">${t.status}</div>
            </div>`;
        });
    } else {
        html = `<p style="color:var(--rp-gray-500);font-size:.8rem;text-align:center;padding:20px">No teams added on this date.</p>`;
    }
    document.getElementById('rpMuniGrid').innerHTML = html;
}

function rpOpenCasModal(type) {
    const isInj   = type === 'injured';
    const label   = isInj ? 'Injured' : 'Martyrs';
    const demoKey = isInj ? 'injured' : 'martyrs';
    const dem     = RP_DATA.casualty.demo[demoKey];
    const total   = isInj ? RP_DATA.casualty.injured : RP_DATA.casualty.martyrs;
    const males = dem.males, females = dem.females, children = dem.children;
    const sum   = males + females + children || 1;
    const malePct  = Math.round(males   / sum * 100);
    const femPct   = Math.round(females / sum * 100);
    const childPct = 100 - malePct - femPct;

    document.getElementById('rpCasModalTitle').textContent = `${label} — Demographic Breakdown`;
    document.getElementById('rpCasModalSub').textContent   = `${total.toLocaleString()} total ${label.toLowerCase()}`;
    document.getElementById('rpCasModalBox').style.borderTop = `4px solid ${isInj ? '#ef4444' : '#1b2559'}`;
    document.getElementById('rpCasModalBody').innerHTML = `
    <div class="rp-cas-bd-grid">
      <div class="rp-cas-bd-item">
        <div class="rp-cas-bd-icon" style="background:#eef2ff"><i class="bi bi-person-fill" style="color:#1b2559"></i></div>
        <div class="rp-cas-bd-num">${males.toLocaleString()}</div><div class="rp-cas-bd-lbl">Males</div>
      </div>
      <div class="rp-cas-bd-item">
        <div class="rp-cas-bd-icon" style="background:#ede9fe"><i class="bi bi-person-fill" style="color:#4f46e5"></i></div>
        <div class="rp-cas-bd-num">${females.toLocaleString()}</div><div class="rp-cas-bd-lbl">Females</div>
      </div>
      <div class="rp-cas-bd-item">
        <div class="rp-cas-bd-icon" style="background:#fef3c7"><i class="bi bi-person-fill" style="color:#f59e0b;font-size:.85rem"></i></div>
        <div class="rp-cas-bd-num">${children.toLocaleString()}</div><div class="rp-cas-bd-lbl">Children</div>
      </div>
    </div>
    <div class="rp-cas-bd-bars">
      <div class="rp-cas-bd-bar-row"><span class="rp-cas-bd-bar-label">Males</span><div class="rp-cas-bd-bar-track"><div class="rp-cas-bd-bar-fill" style="width:${malePct}%;background:#1b2559"></div></div><span class="rp-cas-bd-bar-pct">${malePct}%</span></div>
      <div class="rp-cas-bd-bar-row"><span class="rp-cas-bd-bar-label">Females</span><div class="rp-cas-bd-bar-track"><div class="rp-cas-bd-bar-fill" style="width:${femPct}%;background:#4f46e5"></div></div><span class="rp-cas-bd-bar-pct">${femPct}%</span></div>
      <div class="rp-cas-bd-bar-row"><span class="rp-cas-bd-bar-label">Children</span><div class="rp-cas-bd-bar-track"><div class="rp-cas-bd-bar-fill" style="width:${childPct}%;background:#f59e0b"></div></div><span class="rp-cas-bd-bar-pct">${childPct}%</span></div>
    </div>`;
    document.getElementById('rpCasModal').classList.add('open');
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.getElementById('rpMuniModal').classList.remove('open');
        document.getElementById('rpCasModal').classList.remove('open');
    }
});

async function init() {
     Swal.fire({
        title: 'Retrieving data...',
        text: 'Please wait while the report loads',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const [shelter, hospital, casualty, roads, needs, alertsTeams, incidents, severity, alertsByRegion] = await Promise.all([
        rpFetch('shelter_overview'),
        rpFetch('hospital_status'),
        rpFetch('casualties'),
        rpFetch('roads_status'),
        rpFetch('needs_fulfillment'),
        rpFetch('alerts_teams_series'),
        rpFetch('incidents_overview'),
        rpFetch('severity_score'),
        rpFetch('alerts_by_region'),
    ]);

    RP_DATA = {
        shelter:  shelter  || { total:0, occupied:0, available:0, maintenance:0 },
        hospital: hospital || { stable:0, warning:0, dangerous:0, total:0 },
        casualty: casualty || { injured:0, martyrs:0, demo:{ injured:{males:0,females:0,children:0}, martyrs:{males:0,females:0,children:0} } },
        roads:    roads    || { safe:0, restricted:0, closed:0, total:1 },
        needs:    needs    || { labels:[], fulfilled:[], inProgress:[], notFulfilled:[] },
        alerts:   (alertsTeams && alertsTeams.alerts) || [],
        teams:    (alertsTeams && alertsTeams.teams)  || [],
        incidents: incidents || { total:0, active:0, resolved:0, investigating:0, byDay:[], resolvedByDay:[] },
        sevScore: severity ? severity.score : 0,
        sevClass: severity ? severity.class : 'low',
        alertsByRegion: alertsByRegion || [],
    };

    RP_TIME_LABELS = (alertsTeams && alertsTeams.timeLabels) || (incidents && incidents.timeLabels) || [];
 RP_RAW_DATES = (alertsTeams && alertsTeams.rawDates) || [];
    rpRenderSevBadge(RP_DATA.sevScore, RP_DATA.sevClass);
    rpRenderOverview(RP_DATA);
    rpRenderNeeds(RP_DATA);
    rpRenderAlerts(RP_DATA);
    rpRenderIncidents(RP_DATA);
if (!shelter || !hospital || !casualty || !roads || !needs || !alertsTeams || !incidents || !severity) {
    Swal.fire({
        icon: 'error',
        title: 'Failed to load some data',
        text: 'Please refresh and try again.'
    });
} else {
    Swal.close();
}
    Swal.fire({
    icon: 'success',
    title: 'Data loaded',
    timer: 800,
    showConfirmButton: false
});
}

init();
</script>
</body>
</html>