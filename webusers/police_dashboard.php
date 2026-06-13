<?php
session_start();
require_once("class/police.class.php");

if (!isset($_SESSION['logged_in']) || $_SESSION['type'] !== 'police') {
  header("Location: login.php");
  exit;
}

$police = new police();

$org_id       = (int)$_SESSION['org_id'];
$org_name     = $_SESSION['org_name'];
$org_location = $_SESSION['org_location'];

$policeUnit  = $org_name;
$regionLabel = ucfirst($org_location);

$counts  = $police->getDashboardCounts($org_id, $org_location);
$mapData = $police->getAllPoliceMapData($org_id, $org_location);
$incident_id     = $counts['incident_id']     ?? null;
$incident_name   = $counts['incident_name']   ?? null;
$incident_status = $counts['incident_status'] ?? null;
$is_resolved     = $counts['is_resolved']     ?? false;
$has_mission     = !empty($incident_id)  || !empty($counts['mission_title']);

$sentMissions = $_SESSION['type'] === 'police'
  ? $police->getSentMissions($org_id)
  : [];
$bounds = [[33.05, 35.10], [34.70, 36.65]];
$canceledNotifs = $police->getCanceledMissionNotifs($org_id);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Police Dashboard — <?php echo htmlspecialchars($regionLabel); ?></title>
  <?php include('includes/header.php'); ?>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <style>
    :root {
      --red: #e53935;
      --red-bg: #fdecea;
      --yellow: #f59e0b;
      --yellow-bg: #fffbeb;
      --green: #2e7d32;
      --green-bg: #e8f5e9;
      --blue: #1d6ef5;
      --blue-bg: #e8f0fe;
      --navy: #0a1628;
      --gray: #64748b;
      --gray-bg: #f1f5f9;
      --text: #0f172a;
      --text2: #475569;
      --text3: #94a3b8;
      --border: #e2e8f0;
      --surface: #fff;
    }

    .stat-grid {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 10px;
      margin-bottom: 16px;
    }

    @media(max-width:1200px) {
      .stat-grid {
        grid-template-columns: repeat(3, 1fr);
      }
    }

    .stat-card {
      background: var(--surface);
      border-radius: 14px;
      border: 1px solid var(--border);
      padding: 13px 14px;
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    .sc-icon {
      width: 36px;
      height: 36px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 15px;
    }

    .sc-label {
      font-size: 11px;
      font-weight: 600;
      color: var(--text2);
    }

    .sc-num {
      font-size: 22px;
      font-weight: 800;
      color: var(--text);
      line-height: 1;
    }

    .police-layout {
      display: grid;
      grid-template-columns: 1fr 260px;
      gap: 12px;
      align-items: start;
    }

    @media(max-width:1100px) {
      .police-layout {
        grid-template-columns: 1fr;
      }
    }

    .map-card {
      background: var(--surface);
      border-radius: 18px;
      border: 1px solid var(--border);
      overflow: hidden;
    }

    .map-toolbar {
      display: flex;
      align-items: center;
      gap: 6px;
      flex-wrap: wrap;
      padding: 10px 13px;
      border-bottom: 1px solid var(--border);
    }

    .map-toolbar-lbl {
      font-size: 11px;
      font-weight: 600;
      color: var(--text3);
      text-transform: uppercase;
      letter-spacing: .5px;
    }

    .tool-btn {
      display: flex;
      align-items: center;
      gap: 5px;
      padding: 6px 12px;
      border-radius: 8px;
      font-size: 12px;
      font-weight: 600;
      font-family: inherit;
      cursor: pointer;
      border: 1.5px solid transparent;
      transition: all .15s;
    }

    .tb-road {
      background: #0f1f38;
      color: #fff;
      border-color: #1e3a5f;
    }

    .tb-road:hover {
      background: #1e3a5f;
      border-color: #2d4f7c;
    }

    .tool-sep {
      width: 1px;
      height: 20px;
      background: var(--border);
    }

    .region-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 4px 10px;
      border-radius: 8px;
      background: var(--navy);
      color: #fff;
      font-size: 11px;
      font-weight: 700;
    }

    #map {
      height: 560px;
      width: 100%;
    }

    .map-statusbar {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 13px;
      border-top: 1px solid var(--border);
      font-size: 12px;
      color: var(--text3);
      flex-wrap: wrap;
    }

    .live-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: #22c55e;
      box-shadow: 0 0 0 3px rgba(34, 197, 94, .2);
      animation: lpulse 2s infinite;
    }

    @keyframes lpulse {

      0%,
      100% {
        box-shadow: 0 0 0 3px rgba(34, 197, 94, .2)
      }

      50% {
        box-shadow: 0 0 0 6px rgba(34, 197, 94, .04)
      }
    }

    .mode-banner {
      display: none;
      align-items: center;
      gap: 10px;
      background: var(--navy);
      color: #fff;
      padding: 9px 14px;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 500;
      margin-bottom: 10px;
    }

    .mode-banner.show {
      display: flex;
    }

    .mode-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #fbbf24;
      animation: mblink 1s infinite;
    }

    @keyframes mblink {

      0%,
      100% {
        opacity: 1
      }

      50% {
        opacity: .3
      }
    }

    .mode-cancel {
      margin-left: auto;
      padding: 5px 11px;
      border-radius: 7px;
      background: rgba(255, 255, 255, .15);
      border: none;
      color: #fff;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
    }

    .mode-cancel:hover {
      background: rgba(255, 255, 255, .25);
    }

    .side-panel {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .side-card {
      background: var(--surface);
      border-radius: 14px;
      border: 1px solid var(--border);
      padding: 14px;
    }

    .side-title {
      font-size: 13px;
      font-weight: 700;
      color: var(--text);
      margin-bottom: 11px;
    }

    .layer-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 6px 0;
      border-bottom: 1px solid var(--gray-bg);
    }

    .layer-row:last-child {
      border-bottom: none;
    }

    .layer-left {
      display: flex;
      align-items: center;
      gap: 7px;
    }

    .ltoggle {
      width: 34px;
      height: 18px;
      border-radius: 9px;
      background: #e2e8f0;
      position: relative;
      cursor: pointer;
      border: none;
      transition: background .2s;
    }

    .ltoggle.on {
      background: #2e7d32;
    }

    .ltoggle::after {
      content: '';
      position: absolute;
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: #fff;
      top: 3px;
      left: 3px;
      transition: left .2s;
      box-shadow: 0 1px 3px rgba(0, 0, 0, .2);
    }

    .ltoggle.on::after {
      left: 19px;
    }

    .lname {
      font-size: 12px;
      font-weight: 500;
      color: #334155;
    }

    .lcnt {
      font-size: 10px;
      font-weight: 700;
      padding: 2px 6px;
      border-radius: 5px;
    }

    .lc-g {
      background: var(--green-bg);
      color: var(--green);
    }

    .lc-sky {
      background: #fef3c7;
      color: #92400e;
    }

    .lc-b {
      background: #fef3c7;
      color: #92400e;
    }

    .lc-r {
      background: #fef3c7;
      color: #92400e;
    }

    .lc-y {
      background: #fef3c7;
      color: #92400e;
    }

    .leg-row {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 4px 0;
    }

    .leg-line {
      width: 26px;
      height: 4px;
      border-radius: 2px;
    }

    .leg-lbl {
      font-size: 11px;
      color: var(--text2);
    }

    .tb-route {
      background: #f1f5f9;
      color: #475569;
      border-color: #e2e8f0;
      border: 1.5px solid #e2e8f0;
    }

    .tb-route:hover {
      background: #f1f5f9;
      border-color: #092b57;
    }

    .mp-overlay {
      position: fixed;
      inset: 0;
      background: rgba(10, 22, 40, .45);
      z-index: 2000;
      display: none;
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(4px);
    }

    .mp-overlay.show {
      display: flex;
    }

    .mp-box {
      background: #fff;
      border-radius: 18px;
      padding: 22px;
      width: 420px;
      max-width: 92vw;
      box-shadow: 0 20px 60px rgba(10, 22, 40, .25);
      max-height: 90vh;
      overflow-y: auto;
    }

    .mp-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 14px;
    }

    .mp-title {
      font-size: 15px;
      font-weight: 700;
      color: var(--text);
    }

    .mp-close {
      width: 28px;
      height: 28px;
      border-radius: 7px;
      border: none;
      background: var(--gray-bg);
      cursor: pointer;
      font-size: 13px;
      color: var(--text2);
    }

    .mp-close:hover {
      background: var(--border);
    }

    .mp-field {
      margin-bottom: 11px;
    }

    .mp-label {
      display: block;
      font-size: 11px;
      font-weight: 600;
      color: var(--text2);
      text-transform: uppercase;
      letter-spacing: .4px;
      margin-bottom: 4px;
    }

    .mp-input {
      width: 100%;
      padding: 8px 11px;
      border: 1.5px solid var(--border);
      border-radius: 9px;
      font-size: 13px;
      font-family: inherit;
      color: var(--text);
      outline: none;
      transition: border .15s;
    }

    .mp-input:focus {
      border-color: var(--blue);
    }

    .mp-input[readonly] {
      background: var(--gray-bg);
      font-size: 12px;
      font-family: monospace;
    }

    .mp-tip {
      border-radius: 9px;
      padding: 9px 12px;
      font-size: 12px;
      line-height: 1.55;
      margin-bottom: 10px;
    }

    .tip-blue {
      background: var(--blue-bg);
      color: #1e40af;
    }

    .tip-sky {
      background: #e0f2fe;
      color: #0369a1;
    }

    .tip-green {
      background: var(--green-bg);
      color: #1b5e20;
    }

    .mp-foot {
      display: flex;
      gap: 8px;
      justify-content: flex-end;
      margin-top: 12px;
      flex-wrap: wrap;
    }

    .btn-cancel {
      padding: 7px 14px;
      border: 1.5px solid var(--border);
      border-radius: 8px;
      background: #fff;
      font-size: 13px;
      font-family: inherit;
      color: var(--text2);
      cursor: pointer;
    }

    .btn-cancel:hover {
      background: var(--gray-bg);
    }

    .btn-submit {
      padding: 7px 15px;
      border: none;
      border-radius: 8px;
      font-size: 13px;
      font-family: inherit;
      color: #fff;
      cursor: pointer;
      font-weight: 600;
    }

    .btn-blue {
      background: var(--blue);
    }

    .btn-blue:hover {
      background: #1558d6;
    }

    .btn-red {
      background: var(--red);
    }

    .btn-red:hover {
      background: #c62828;
    }

    .btn-sky {
      background: #0369a1;
    }

    .btn-sky:hover {
      background: #0284c7;
    }

    .btn-green {
      background: var(--green);
    }

    .btn-green:hover {
      background: #1b5e20;
    }

    .street-results {
      max-height: 140px;
      overflow-y: auto;
      border: 1.5px solid var(--border);
      border-radius: 9px;
      background: #fff;
      display: none;
      margin-top: 4px;
    }

    .street-results.show {
      display: block;
    }

    .sr-item {
      padding: 8px 12px;
      cursor: pointer;
      font-size: 13px;
      color: var(--text);
      border-bottom: 1px solid var(--gray-bg);
    }

    .sr-item:last-child {
      border-bottom: none;
    }

    .sr-item:hover {
      background: var(--blue-bg);
    }

    .sr-sub {
      font-size: 11px;
      color: var(--text3);
      margin-top: 1px;
    }

    .map-toast {
      position: fixed;
      bottom: 24px;
      left: 50%;
      transform: translateX(-50%) translateY(30px);
      background: var(--navy);
      color: #fff;
      padding: 10px 20px;
      border-radius: 11px;
      font-size: 13px;
      font-weight: 500;
      z-index: 3000;
      opacity: 0;
      transition: all .3s;
      pointer-events: none;
      white-space: nowrap;
    }

    .map-toast.show {
      opacity: 1;
      transform: translateX(-50%) translateY(0);
    }

    .rf-status-btn {
      padding: 3px 9px;
      border-radius: 6px;
      font-size: 11px;
      font-weight: 600;
      border: 1.5px solid var(--border);
      background: #fff;
      color: var(--text2);
      cursor: pointer;
    }

    .rf-status-btn.active {
      background: var(--blue);
      color: #fff;
      border-color: var(--blue);
    }

    .route-result-item {
      padding: 9px 10px;
      border-radius: 9px;
      border: 1.5px solid var(--border);
      margin-bottom: 6px;
      cursor: pointer;
      transition: border .15s;
    }

    .route-result-item:hover {
      border-color: var(--blue);
    }

    .rri-head {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 4px;
    }

    .rri-name {
      font-size: 12px;
      font-weight: 700;
      color: var(--text);
    }

    .rri-badge {
      padding: 2px 7px;
      border-radius: 5px;
      font-size: 10px;
      font-weight: 700;
    }

    .rri-notes {
      font-size: 11px;
      color: var(--text3);
    }
  </style>
</head>

<body>
  <?php include('includes/sidebar.php'); ?>
  <?php include('includes/nav.php'); ?>

  <div class="main-content">
    <div class="container-fluid py-3">

      <div class="mode-banner" id="modeBanner">
        <span class="mode-dot"></span>
        <span id="modeText">Click waypoints on the map...</span>
        <button class="btn-submit btn-green" style="padding:5px 12px;font-size:12px;" onclick="finishDrawing()">✔ Finish</button>
        <button class="mode-cancel" onclick="cancelMode()">✕ Cancel</button>
      </div>

      <div class="police-layout">

        <!-- mapp -->
        <div class="map-card">
          <div class="map-toolbar">
            <span class="map-toolbar-lbl">Add:</span>
            <button class="tool-btn tb-route" onclick="openPopup('popRoute')">🗺️ Evacuation Route</button>
            <button class="tool-btn tb-route" onclick="openPopup('popRoad')">🛣️ Road Status</button>
            <div class="tool-sep"></div>
            <span class="tool-btn tb-route" style="cursor:default;">📍 <?php echo htmlspecialchars($regionLabel); ?></span>
            <div class="tool-sep"></div>
            <button class="tool-btn" onclick="resetToUnit()"
              style="background:#f1f5f9;color:#475569;border-color:#e2e8f0;border:1.5px solid #e2e8f0;">
              🏠 My Location
            </button>
            <div class="tool-sep"></div>
            <div style="position:relative;">
              <input id="mapStreetSearch" class="mp-input"
                placeholder="🔍 Find street on map..."
                oninput="liveStreetSearch(this.value)"
                style="width:180px;padding:6px 10px;font-size:12px;">
              <div id="mapStreetResults" class="street-results"></div>
            </div>
          </div>
          <div id="map"></div>
          <div class="map-statusbar">
            <div class="live-dot"></div>
            <?php echo htmlspecialchars($policeUnit); ?> &nbsp;·&nbsp;
            <span id="coordTxt" style="font-family:monospace">Hover for coordinates</span>
            &nbsp;·&nbsp;
            <span id="modeInd" style="color:var(--blue);font-weight:600;"></span>
          </div>
        </div>

        <div class="side-panel">
          <div class="side-card">
            <div class="side-title"> Map Layers</div>
            <div class="layer-row">
              <div class="layer-left"><button class="ltoggle on" id="lt-evac" onclick="toggleLayer('evac',this)"></button><span></span><span class="lname">Evac Routes</span></div>
              <span class="lcnt lc-sky" id="lc-evac"><?php echo $counts['evac_routes']; ?></span>
            </div>
            <div class="layer-row">
              <div class="layer-left"><button class="ltoggle on" id="lt-myroads" onclick="toggleLayer('myroads',this)"></button><span></span><span class="lname">My Road Status</span></div>
              <span class="lcnt lc-b" id="lc-myroads"><?php echo $counts['all_roads']; ?></span>
            </div>
            <div class="layer-row">
              <div class="layer-left"><button class="ltoggle" id="lt-unitroads" onclick="toggleLayer('unitroads',this)"></button><span></span><span class="lname">Unit Roads</span></div>
              <!-- <span class="lcnt lc-b" id="lc-unitroads">0</span> -->
              <span class="lcnt lc-b" id="lc-unitroads"><?php echo $counts['unit_roads_count']; ?></span>
            </div>
            <div class="layer-row">
              <div class="layer-left"><button class="ltoggle on" id="lt-alerts" onclick="toggleLayer('alerts',this)"></button><span></span><span class="lname">Alerts</span></div>
              <span class="lcnt lc-r" id="lc-alerts"><?php echo $counts['danger_alerts']; ?></span>
            </div>
            <div class="layer-row">
              <div class="layer-left"><button class="ltoggle on" id="lt-zones" onclick="toggleLayer('zones',this)"></button><span></span><span class="lname">Zones</span></div>
              <span class="lcnt lc-y" id="lc-zones"><?php echo $counts['safe_zones']; ?></span>
            </div>
            <div class="layer-row">
              <div class="layer-left"><button class="ltoggle" id="lt-sat" onclick="switchBase(this)"></button><span></span><span class="lname">Satellite</span></div>
            </div>
          </div>
          <!-- Legend -->
          <div class="side-card">
            <div class="side-title"> Legend</div>
            <div class="leg-row">
              <div class="leg-line" style="background:#0369a1;height:3px;border-radius:2px;border-top:3px dashed #0369a1;height:0;width:26px;"></div><span class="leg-lbl">Evacuation Route</span>
            </div>
            <div class="leg-row">
              <div class="leg-line" style="background:var(--red);"></div><span class="leg-lbl">Road — Closed</span>
            </div>
            <div class="leg-row">
              <div class="leg-line" style="background:var(--yellow);"></div><span class="leg-lbl">Road — Warning</span>
            </div>
            <div class="leg-row">
              <div class="leg-line" style="background:var(--green);"></div><span class="leg-lbl">Road — Open</span>
            </div>
            <div class="leg-row">
              <div style="width:13px;height:13px;border-radius:50%;background:var(--red);flex-shrink:0;"></div><span class="leg-lbl">Alert Pin</span>
            </div>
            <div class="leg-row">
              <div style="width:16px;height:16px;border-radius:50%;border:2px dashed var(--yellow);background:rgba(245,158,11,.12);flex-shrink:0;"></div><span class="leg-lbl">Warning Zone</span>
            </div>
            <div class="leg-row">
              <div style="width:16px;height:16px;border-radius:50%;border:2px dashed var(--green);background:rgba(46,125,50,.12);flex-shrink:0;"></div><span class="leg-lbl">Safe Zone</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="mp-overlay" id="popRoute">
    <div class="mp-box">
      <div class="mp-head">
        <div class="mp-title">🗺️ Add Evacuation Route</div>
        <button class="mp-close" onclick="closePopup('popRoute')">✕</button>
      </div>
      <div class="mp-tip tip-sky">Click waypoints on the map. Click <strong>Finish Route</strong> in the banner when done.</div>
      <!-- <div class="mp-field"><label class="mp-label">Route Name</label><input class="mp-input" id="rtName" placeholder="e.g. Tyre → Saida via Coast Road"></div> -->
      <div class="mp-field"><label class="mp-label">From</label><input class="mp-input" id="rtFrom" placeholder="e.g. Tyre city center"></div>
      <div class="mp-field"><label class="mp-label">To</label><input class="mp-input" id="rtTo" placeholder="e.g. Saida"></div>
      <div class="mp-field"><label class="mp-label">Status</label>
        <select class="mp-input" id="rtStatus">
          <option value="open">🟢 Open — Safe to use</option>
          <option value="warning">🟡 Caution — Proceed carefully</option>
          <option value="closed">🔴 Closed — Do not use</option>
        </select>
      </div>
      <div class="mp-field"><label class="mp-label">Notes</label><input class="mp-input" id="rtNotes" placeholder="e.g. Avoid between 18:00–06:00, checkpoint at km 12"></div>
      <div class="mp-field"><label class="mp-label">Waypoints (auto-filled when drawing)</label>
        <input class="mp-input" id="rtPoints" readonly placeholder="Draw on map to fill...">
      </div>
      <div class="mp-foot">
        <button class="btn-cancel" onclick="closePopup('popRoute')">Cancel</button>
        <button class="btn-submit btn-sky" onclick="startRouteMode()">✏️ Start Drawing</button>
        <button class="btn-submit btn-green" id="btnSaveRoute" style="display:none" onclick="saveRoute()">💾 Save Route</button>
      </div>
    </div>
  </div>

  <div class="mp-overlay" id="popRoad">
    <div class="mp-box">
      <div class="mp-head">
        <div class="mp-title">🛣️ Set Road Status</div>
        <button class="mp-close" onclick="closePopup('popRoad')">✕</button>
      </div>
      <div class="mp-tip tip-blue">Search a street or draw the road manually on the map.</div>
      <div class="mp-field">
        <label class="mp-label">Search Street</label>
        <input class="mp-input" id="roadSearch" placeholder="e.g. Douaa Street, Tyre" oninput="searchStreet(this.value)">
        <div class="street-results" id="streetResults"></div>
      </div>
      <div class="mp-field"><label class="mp-label">Road Name</label><input class="mp-input" id="roadName" placeholder="e.g. Douaa Street — Tyre center"></div>
      <div class="mp-field"><label class="mp-label">Status</label>
        <select class="mp-input" id="roadStatus">
          <option value="blocked">🔴 Blocked</option>
          <option value="warning">🟡 Warning</option>
          <option value="safe">🟢 Safe</option>
        </select>
      </div>
      <div class="mp-field"><label class="mp-label">Reason</label><input class="mp-input" id="roadReason" placeholder="e.g. Military checkpoint, debris..."></div>
      <div id="roadSelTip" class="mp-tip tip-green" style="display:none">✅ Ready to save.</div>
      <div class="mp-foot">
        <button class="btn-cancel" onclick="closePopup('popRoad')">Cancel</button>
        <button class="btn-submit btn-blue" onclick="startRoadMode()">✏️ Draw on Map</button>
        <button class="btn-submit btn-red" onclick="confirmRoad()">💾 Save</button>
      </div>
    </div>
  </div>

  <div class="mp-overlay" id="popEditRoute">
    <div class="mp-box">
      <div class="mp-head">
        <div class="mp-title">✏️ Edit Route</div>
        <button class="mp-close" onclick="closePopup('popEditRoute')">✕</button>
      </div>
      <input type="hidden" id="editRouteId">
      <div class="mp-field"><label class="mp-label">From → To</label>
        <input class="mp-input" id="editRtLabel" readonly
          style="background:var(--gray-bg);font-weight:600;">
      </div>
      <div class="mp-field"><label class="mp-label">Status</label>
        <select class="mp-input" id="editRtStatus">
          <option value="open">🟢 Open — Safe to use</option>
          <option value="warning">🟡 Caution — Proceed carefully</option>
          <option value="closed">🔴 Closed — Do not use</option>
        </select>
      </div>
      <div class="mp-field"><label class="mp-label">Notes</label>
        <input class="mp-input" id="editRtNotes" placeholder="e.g. Checkpoint cleared...">
      </div>
      <div class="mp-foot">
        <button class="btn-cancel" onclick="closePopup('popEditRoute')">Cancel</button>
        <button class="btn-submit btn-blue" onclick="updateRoute()">💾 Save</button>
      </div>
    </div>
  </div>

  <div class="mp-overlay" id="popEditRoad">
    <div class="mp-box">
      <div class="mp-head">
        <div class="mp-title">✏️ Edit Road</div><button class="mp-close" onclick="closePopup('popEditRoad')">✕</button>
      </div>
      <input type="hidden" id="editRoadId">
      <div class="mp-field"><label class="mp-label">Road Name</label><input class="mp-input" id="editRoadName"></div>
      <div class="mp-field"><label class="mp-label">Status</label>
        <select class="mp-input" id="editRoadStatus">
          <option value="blocked">🔴 Blocked</option>
          <option value="warning">🟡 Warning</option>
          <option value="safe">🟢 Safe</option>
        </select>
      </div>
      <div class="mp-field"><label class="mp-label">Reason</label><input class="mp-input" id="editRoadReason"></div>
      <div class="mp-foot">
        <button class="btn-cancel" onclick="closePopup('popEditRoad')">Cancel</button>
        <button class="btn-submit btn-blue" onclick="updateRoad()">💾 Update</button>
      </div>
    </div>
  </div>

  <div class="map-toast" id="mapToast"></div>

  <?php include('includes/script.php'); ?>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
    var REGION_BOUNDS = <?php echo json_encode($bounds); ?>;
    var POLICE_LOCATION = <?php echo json_encode($org_location); ?>;
    var ORG_ID = <?php echo $org_id; ?>;

    var dbEvacRoutes = <?php echo json_encode(array_values($mapData['evac_routes']  ?? [])); ?>;
    var dbPoliceRoads = <?php echo json_encode(array_values($mapData['police_roads'] ?? [])); ?>;
    var dbUnitAlerts = <?php echo json_encode(array_values($mapData['unit_alerts']  ?? [])); ?>;
    var dbUnitWarnZ = <?php echo json_encode(array_values($mapData['unit_warn_zones'] ?? [])); ?>;
    var dbUnitSafeZ = <?php echo json_encode(array_values($mapData['unit_safe_zones'] ?? [])); ?>;
    var dbUnitRoads = <?php echo json_encode(array_values($mapData['unit_roads']   ?? [])); ?>;

    var INCIDENT_ID = <?php echo (int)($incident_id ?? 0); ?>;
    var HAS_MISSION = <?php echo $has_mission ? 'true' : 'false'; ?>;
    var IS_RESOLVED = <?php echo $is_resolved ? 'true' : 'false'; ?>;
    var INCIDENT_NAME = <?php echo json_encode($incident_name ?? ''); ?>;

    var map = L.map('map', {
      zoomControl: true,
      doubleClickZoom: false
    });
    var tileVoyager = L.tileLayer(
      'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '© OSM © CARTO',
        subdomains: 'abcd',
        maxZoom: 20
      }
    ).addTo(map);
    var tileSat = L.tileLayer(
      'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 19
      }
    );

    var LEBANON_CITIES = {
      'tyre': [33.2704, 35.2038, 14],
      'sur': [33.2704, 35.2038, 14],
      'sour': [33.2704, 35.2038, 14],
      'صور': [33.2704, 35.2038, 14],
      'saida': [33.5571, 35.3729, 14],
      'sidon': [33.5571, 35.3729, 14],
      'nabatieh': [33.3772, 35.4844, 13],
      'bint jbeil': [33.1172, 35.4336, 13],
      'bint jbail': [33.1172, 35.4336, 13],
      'marjayoun': [33.3597, 35.5922, 13],
      'hasbaya': [33.3997, 35.6867, 13],
      'beirut': [33.8938, 35.5018, 13],
      'tripoli': [34.4369, 35.8497, 13],
      'trablous': [34.4369, 35.8497, 13],
      'jounieh': [33.9806, 35.6178, 14],
      'jbeil': [34.1236, 35.6517, 13],
      'byblos': [34.1236, 35.6517, 13],
      'zahle': [33.8464, 35.9022, 13],
      'baalbek': [34.0044, 36.2156, 13],
      'zgharta': [34.3981, 35.8942, 13],
      'aley': [33.8108, 35.5989, 13],
      'baabda': [33.8339, 35.5414, 13],
      'akkar': [34.5333, 36.0833, 12],
      'chtaura': [33.8128, 35.8547, 13],
    };

    function getUnitCoords(loc) {
      if (!loc) return [33.8547, 35.8623, 9];
      var k = loc.toLowerCase().trim();
      if (LEBANON_CITIES[k]) return LEBANON_CITIES[k];
      for (var c in LEBANON_CITIES) {
        if (c === k) return LEBANON_CITIES[c];
      }
      for (var c in LEBANON_CITIES) {
        if (k.indexOf(c) !== -1) return LEBANON_CITIES[c];
      }
      for (var c in LEBANON_CITIES) {
        if (c.indexOf(k) !== -1) return LEBANON_CITIES[c];
      }
      return [33.8547, 35.8623, 9];
    }

    var _uc = getUnitCoords(POLICE_LOCATION);
    map.setView([_uc[0], _uc[1]], _uc[2]);

    var lgEvac = L.layerGroup().addTo(map);
    var lgMyRoads = L.layerGroup().addTo(map);
    var lgUnitRoads = L.layerGroup();
    var lgAlerts = L.layerGroup().addTo(map);
    var lgZones = L.layerGroup().addTo(map);

    var layerMap = {
      evac: lgEvac,
      myroads: lgMyRoads,
      unitroads: lgUnitRoads,
      alerts: lgAlerts,
      zones: lgZones
    };

    function toggleLayer(key, btn) {
      var lg = layerMap[key];
      if (!lg) return;
      if (map.hasLayer(lg)) {
        map.removeLayer(lg);
        btn && btn.classList.remove('on');
      } else {
        map.addLayer(lg);
        btn && btn.classList.add('on');
      }
    }

    function switchBase(btn) {
      if (map.hasLayer(tileSat)) {
        map.removeLayer(tileSat);
        map.addLayer(tileVoyager);
        btn.classList.remove('on');
      } else {
        map.removeLayer(tileVoyager);
        map.addLayer(tileSat);
        btn.classList.add('on');
      }
    }

    var currentMode = null;
    var routePoints = [];
    var routePolyline = null;
    var roadPoints = [];
    var roadPolyline = null;
    var pendRoadPts = null;
    var tempMarkers = [];
    var evacRoutes = [];
    var policeRoads = [];
    var idSeq = 1;
    var routeFilterStatus = 'all';
    var routeSearchQuery = '';

    map.on('mousemove', function(e) {
      document.getElementById('coordTxt').textContent =
        e.latlng.lat.toFixed(5) + ', ' + e.latlng.lng.toFixed(5);
    });

    map.on('click', function(e) {
      if (currentMode === 'route' || currentMode === 'route-edit') addRoutePoint(e.latlng);
      else if (currentMode === 'road') addRoadPoint(e.latlng);
    });

    function openPopup(id) {
      document.getElementById(id).classList.add('show');
    }

    function closePopup(id) {
      document.getElementById(id).classList.remove('show');
    }

    function escH(s) {
      return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function toast(msg, dur) {
      var t = document.getElementById('mapToast');
      t.textContent = msg;
      t.classList.add('show');
      setTimeout(function() {
        t.classList.remove('show');
      }, dur || 2500);
    }

    function startRouteMode() {
      var from = document.getElementById('rtFrom').value.trim();
      var to = document.getElementById('rtTo').value.trim();
      if (!from || !to) {
        toast('Enter From and To first.');
        return;
      }
      closePopup('popRoute');
      currentMode = 'route';
      routePoints = [];
      tempMarkers.forEach(function(m) {
        map.removeLayer(m);
      });
      tempMarkers = [];
      if (routePolyline) {
        map.removeLayer(routePolyline);
        routePolyline = null;
      }
      document.getElementById('rtPoints').value = '';
      document.getElementById('btnSaveRoute').style.display = 'none';
      document.getElementById('modeBanner').classList.add('show');
      document.getElementById('modeText').textContent = 'Drawing route: ' + from + ' → ' + to + ' — click waypoints on map';
      document.getElementById('modeInd').textContent = '✏️ Drawing Route';
      map.getContainer().style.cursor = 'crosshair';
    }

    function addRoutePoint(latlng) {
      routePoints.push([latlng.lat, latlng.lng]);
      var m = L.circleMarker(latlng, {
        radius: 5,
        color: '#7c3aed',
        fillColor: '#fff',
        fillOpacity: 1,
        weight: 2
      }).addTo(map);
      tempMarkers.push(m);
      if (routePoints.length > 1) {
        if (routePolyline) map.removeLayer(routePolyline);
        var col = currentMode === 'route-edit' ? '#7c3aed' : '#0369a1';
        routePolyline = L.polyline(routePoints, {
          color: col,
          weight: 4,
          dashArray: '8,6',
          opacity: .9
        }).addTo(map);
      }
      if (document.getElementById('rtPoints'))
        document.getElementById('rtPoints').value = routePoints.length + ' waypoints';
    }

    function finishDrawing() {
      if (currentMode === 'route') {
        if (routePoints.length < 2) {
          toast('Add at least 2 waypoints.');
          return;
        }
        document.getElementById('btnSaveRoute').style.display = 'inline-block';
        openPopup('popRoute');
        cancelModeUI();
      } else if (currentMode === 'road') {
        if (roadPoints.length < 2) {
          toast('Add at least 2 points.');
          return;
        }
        document.getElementById('roadSelTip').style.display = 'block';
        openPopup('popRoad');
        cancelModeUI();
      }
    }

    function saveRoute() {
      if (routePoints.length < 2) {
        toast('Draw at least 2 points on the map first');
        return;
      }
      var r = {
        id: idSeq++,
        from: document.getElementById('rtFrom').value.trim(),
        to: document.getElementById('rtTo').value.trim(),
        status: document.getElementById('rtStatus').value,
        notes: document.getElementById('rtNotes').value.trim(),
        points: routePoints.slice()
      };
      if (!r.from || !r.to) {
        toast('From and To are required.');
        return;
      }

      closePopup('popRoute');

      Swal.fire({
        title: 'Saving Route...',
        html: '📍 ' + r.from + ' → ' + r.to,
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: function() {
          Swal.showLoading();
        }
      });

      fetch('actions/police_save_route.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            org_id: ORG_ID,
            from_name: r.from,
            to_name: r.to,
            route_status: r.status,
            notes: r.notes,
            region: POLICE_LOCATION,
            points: r.points,
            incident_id: INCIDENT_ID || null

          })
        })
        .then(function(res) {
          return res.json();
        })
        .then(function(data) {
          if (data.status === 'success' || data.id) {
            r.dbId = data.id || null;
            evacRoutes.push(r);
            renderRouteOnMap(r);
            tempMarkers.forEach(function(m) {
              map.removeLayer(m);
            });
            tempMarkers = [];
            routePoints = [];
            routePolyline = null;
            ['rtFrom', 'rtTo', 'rtNotes', 'rtPoints'].forEach(function(id) {
              document.getElementById(id).value = '';
            });
            document.getElementById('btnSaveRoute').style.display = 'none';
            document.getElementById('lc-evac').textContent = evacRoutes.length;
            renderRouteSearchList();
            Swal.fire({
                icon: 'success',
                title: 'Route Saved!',
                timer: 1500,
                showConfirmButton: false
              })
              .then(function() {
                window.location.reload();
              });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message || 'Save failed'
            });
          }
        })
        .catch(function() {
          Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Could not reach server.'
          });
        });
    }

    function renderRouteOnMap(r) {
      var colors = {
        open: '#0369a1',
        warning: '#f59e0b',
        closed: '#e53935'
      };
      var col = colors[r.status] || '#0369a1';
      var label = r.status === 'open' ? '🟢 Open' : r.status === 'warning' ? '🟡 Caution' : '🔴 Closed';

      var popupHtml =
        '<div style="min-width:200px;font-family:inherit">' +
        '<div style="font-weight:700;font-size:13px;margin-bottom:6px;">🗺️ ' + escH(r.from) + ' → ' + escH(r.to) + '</div>' +
        '<span style="padding:2px 8px;border-radius:5px;font-size:11px;font-weight:700;' +
        'background:' + (r.status === 'open' ? '#e8f5e9' : r.status === 'warning' ? '#fffbeb' : '#fdecea') + ';' +
        'color:' + (r.status === 'open' ? '#2e7d32' : r.status === 'warning' ? '#b45309' : '#e53935') + '">' + label + '</span>' +
        (r.notes ? '<div style="margin-top:7px;font-size:11px;color:#475569;">' + escH(r.notes) + '</div>' : '') +
        '<div style="margin-top:8px;display:flex;gap:5px;">' +
        '<button onclick="editRouteById(' + (r.dbId || r.id) + ')" ' +
        'style="flex:1;padding:5px;border-radius:5px;border:none;background:#e8f0fe;color:#1d6ef5;font-size:11px;font-weight:600;cursor:pointer;">✏️ Edit</button>' +
        '<button onclick="deleteRouteById(' + (r.dbId || r.id) + ')" ' +
        'style="flex:1;padding:5px;border-radius:5px;border:none;background:#fdecea;color:#e53935;font-size:11px;font-weight:600;cursor:pointer;">🗑 Delete</button>' +
        '</div></div>';

      L.polyline(r.points, {
        color: col,
        weight: 5,
        dashArray: '10,7',
        opacity: .95
      }).addTo(lgEvac);

      var hitPoly = L.polyline(r.points, {
          color: col,
          weight: 20,
          opacity: 0.001
        })
        .bindPopup(popupHtml, {
          maxWidth: 260
        })
        .addTo(lgEvac);

      r._poly = hitPoly;
    }

    function editRouteById(id) {
      var r = evacRoutes.find(function(x) {
        return (x.dbId || x.id) === id;
      });
      if (!r) return;
      document.getElementById('editRouteId').value = id;
      document.getElementById('editRtLabel').value = r.from + ' → ' + r.to;
      document.getElementById('editRtStatus').value = r.status;
      document.getElementById('editRtNotes').value = r.notes || '';
      map.closePopup();
      openPopup('popEditRoute');
    }

    function updateRoute() {
      var id = parseInt(document.getElementById('editRouteId').value);
      var r = evacRoutes.find(function(x) {
        return (x.dbId || x.id) === id;
      });
      if (!r) return;
      r.status = document.getElementById('editRtStatus').value;
      r.notes = document.getElementById('editRtNotes').value.trim();
      closePopup('popEditRoute');
      Swal.fire({
        title: 'Updating...',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: function() {
          Swal.showLoading();
        }
      });
      fetch('actions/police_update_route.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            id: r.dbId,
            route_status: r.status,
            notes: r.notes
          })
        })
        .then(function(res) {
          return res.json();
        })
        .then(function(data) {
          if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Route Updated!',
                timer: 1500,
                showConfirmButton: false
              })
              .then(function() {
                window.location.reload();
              });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message
            });
          }
        })
        .catch(function() {
          Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Could not reach server.'
          });
        });
    }

    function deleteRouteById(id) {
      Swal.fire({
        title: 'Delete this route?',
        text: 'This evacuation route will be permanently removed.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e53935',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
      }).then(function(result) {
        if (!result.isConfirmed) return;

        Swal.fire({
          title: 'Deleting...',
          allowOutsideClick: false,
          showConfirmButton: false,
          didOpen: function() {
            Swal.showLoading();
          }
        });

        fetch('actions/police_delete_route.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              id: id
            })
          })
          .then(function(res) {
            return res.json();
          })
          .then(function(data) {
            if (data.status === 'success') {
              var r = evacRoutes.find(function(x) {
                return (x.dbId || x.id) === id;
              });
              if (r && r._poly) lgEvac.removeLayer(r._poly);
              evacRoutes = evacRoutes.filter(function(x) {
                return (x.dbId || x.id) !== id;
              });
              document.getElementById('lc-evac').textContent = evacRoutes.length;
              renderRouteSearchList();
              map.closePopup();
              // Swal.fire({ icon:'success', title:'Deleted!', timer:1500, showConfirmButton:false });
              Swal.fire({
                  icon: 'success',
                  title: 'Deleted!',
                  timer: 1500,
                  showConfirmButton: false
                })
                .then(function() {
                  window.location.reload();
                });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
              });
            }
          })
          .catch(function() {
            Swal.fire({
              icon: 'error',
              title: 'Connection Error',
              text: 'Could not reach server.'
            });
          });
      });
    }

    function startRoadMode() {
      closePopup('popRoad');
      currentMode = 'road';
      roadPoints = [];
      if (roadPolyline) {
        map.removeLayer(roadPolyline);
        roadPolyline = null;
      }
      document.getElementById('modeBanner').classList.add('show');
      document.getElementById('modeText').textContent = 'Click road path on map — click Finish when done';
      document.getElementById('modeInd').textContent = '✏️ Drawing Road';
      map.getContainer().style.cursor = 'crosshair';
    }

    function addRoadPoint(latlng) {
      roadPoints.push([latlng.lat, latlng.lng]);
      if (roadPolyline) map.removeLayer(roadPolyline);
      if (roadPoints.length > 1)
        roadPolyline = L.polyline(roadPoints, {
          color: '#64748b',
          weight: 3,
          dashArray: '5,4'
        }).addTo(map);
    }

    function searchStreet(q) {
      var box = document.getElementById('streetResults');
      if (q.length < 3) {
        box.classList.remove('show');
        return;
      }
      fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(q + ', Lebanon') + '&limit=5')
        .then(function(r) {
          return r.json();
        })
        .then(function(data) {
          if (!data.length) {
            box.innerHTML = '<div class="sr-item" style="color:var(--text3)">No results</div>';
            box.classList.add('show');
            return;
          }
          box.innerHTML = data.map(function(d) {
            return '<div class="sr-item" onclick="selectStreet(' + d.lat + ',' + d.lon + ',\'' + escH(d.display_name).replace(/'/g, "\\'") + '\')">' +
              '<div>' + escH(d.display_name.split(',')[0]) + '</div>' +
              '<div class="sr-sub">' + escH(d.display_name) + '</div></div>';
          }).join('');
          box.classList.add('show');
        });
    }

    function selectStreet(lat, lon, name) {
      document.getElementById('streetResults').classList.remove('show');
      document.getElementById('roadName').value = name.split(',')[0];
      map.setView([parseFloat(lat), parseFloat(lon)], 16);
      var lt = parseFloat(lat),
        ln = parseFloat(lon);
      pendRoadPts = [
        [lt - 0.0005, ln - 0.0005],
        [lt, ln],
        [lt + 0.0005, ln + 0.0005]
      ];
      if (roadPolyline) map.removeLayer(roadPolyline);
      roadPolyline = L.polyline(pendRoadPts, {
        color: '#f59e0b',
        weight: 4,
        dashArray: '8,5',
        opacity: .8
      }).addTo(map);
      document.getElementById('roadSelTip').style.display = 'block';
      toast('📍 Street found — click Save or Draw on Map to adjust.');
    }

    function confirmRoad() {
      var name = document.getElementById('roadName').value.trim();
      var status = document.getElementById('roadStatus').value;
      var reason = document.getElementById('roadReason').value.trim();

      if (!name) {
        toast('Enter a road name.');
        return;
      }

      var pts = roadPoints.length >= 2 ? roadPoints.slice() :
        (pendRoadPts && pendRoadPts.length >= 2 ? pendRoadPts : []);

      if (pts.length < 2) {
        toast('Search a street or draw the road on the map first.');
        return;
      }

      closePopup('popRoad');

      Swal.fire({
        title: 'Saving Road Status...',
        html: '🛣️ ' + name,
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: function() {
          Swal.showLoading();
        }
      });

      fetch('actions/police_save_road.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            org_id: ORG_ID,
            road_name: name,
            road_type: status,
            reason: reason,
            notes: '',
            region: POLICE_LOCATION,
            points: pts,
            incident_id: INCIDENT_ID || null
          })
        })
        .then(function(res) {
          return res.json();
        })
        .then(function(data) {
          if (data.status === 'success' || data.id) {
            var r = {
              id: idSeq++,
              dbId: data.id || null,
              name: name,
              status: status,
              reason: reason,
              points: pts
            };
            policeRoads.push(r);
            renderRoadOnMap(r);

            if (roadPolyline) {
              map.removeLayer(roadPolyline);
              roadPolyline = null;
            }
            roadPoints = [];
            pendRoadPts = null;
            ['roadSearch', 'roadName', 'roadReason'].forEach(function(id) {
              document.getElementById(id).value = '';
            });
            document.getElementById('roadSelTip').style.display = 'none';
            document.getElementById('streetResults').classList.remove('show');
            document.getElementById('lc-myroads').textContent = policeRoads.length;

            // Swal.fire({ icon:'success', title:'Road Saved!', timer:1500, showConfirmButton:false });
            Swal.fire({
                icon: 'success',
                title: 'Road Saved',
                timer: 1500,
                showConfirmButton: false
              })
              .then(function() {
                window.location.reload();
              });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message || 'Save failed'
            });
          }
        })
        .catch(function() {
          Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Could not reach server.'
          });
        });
    }

    function renderRoadOnMap(r) {
      var colors = {
        safe: '#2e7d32',
        warning: '#f59e0b',
        blocked: '#e53935'
      };
      var col = colors[r.status] || '#64748b';
      var label = r.status === 'safe' ? '🟢 Safe' : r.status === 'warning' ? '🟡 Warning' : '🔴 Blocked';
      var poly = L.polyline(r.points, {
          color: col,
          weight: 5,
          opacity: .9
        })
        .bindPopup(
          '<div style="font-weight:700;font-size:13px;margin-bottom:6px;">🛣️ ' + escH(r.name) + '</div>' +
          '<span style="padding:2px 8px;border-radius:5px;font-size:11px;font-weight:700;background:' + (r.status === 'safe' ? '#e8f5e9' : r.status === 'warning' ? '#fffbeb' : '#fdecea') + ';color:' + (r.status === 'safe' ? '#2e7d32' : r.status === 'warning' ? '#b45309' : '#e53935') + '">' + label + '</span>' +
          (r.reason ? '<div style="margin-top:6px;font-size:11px;color:#475569;">' + escH(r.reason) + '</div>' : '') +
          '<div style="margin-top:8px;display:flex;gap:5px;">' +
          '<button onclick="editRoadById(' + (r.dbId || r.id) + ')" style="padding:3px 8px;border-radius:5px;border:none;background:#e8f0fe;color:#1d6ef5;font-size:11px;font-weight:600;cursor:pointer;">✏️ Edit</button>' +
          '<button onclick="deleteRoadById(' + (r.dbId || r.id) + ')" style="padding:3px 8px;border-radius:5px;border:none;background:#fdecea;color:#e53935;font-size:11px;font-weight:600;cursor:pointer;">🗑 Delete</button>' +
          '</div>'
        ).addTo(lgMyRoads);
      r._poly = poly;
    }

    function editRoadById(id) {
      var r = policeRoads.find(function(x) {
        return (x.dbId || x.id) === id;
      });
      if (!r) return;
      document.getElementById('editRoadId').value = id;
      document.getElementById('editRoadName').value = r.name;
      document.getElementById('editRoadStatus').value = r.status;
      document.getElementById('editRoadReason').value = r.reason;
      map.closePopup();
      2
      openPopup('popEditRoad');
    }

    function updateRoad() {
      var id = parseInt(document.getElementById('editRoadId').value);
      var r = policeRoads.find(function(x) {
        return (x.dbId || x.id) === id;
      });
      if (!r) return;

      r.name = document.getElementById('editRoadName').value.trim();
      r.status = document.getElementById('editRoadStatus').value;
      r.reason = document.getElementById('editRoadReason').value.trim();

      closePopup('popEditRoad');

      Swal.fire({
        title: 'Updating...',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: function() {
          Swal.showLoading();
        }
      });

      fetch('actions/police_update_road.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            id: r.dbId,
            road_name: r.name,
            road_type: r.status,
            reason: r.reason
          })
        })
        .then(function(res) {
          return res.json();
        })
        .then(function(data) {
          if (data.status === 'success') {
            if (r._poly) {
              lgMyRoads.removeLayer(r._poly);
              r._poly = null;
            }
            if (r.points && r.points.length >= 2) renderRoadOnMap(r);
            // Swal.fire({ icon:'success', title:'Road Updated!', timer:1500, showConfirmButton:false });
            Swal.fire({
                icon: 'success',
                title: 'Road Updated!',
                timer: 1500,
                showConfirmButton: false
              })
              .then(function() {
                window.location.reload();
              });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message
            });
          }
        })
        .catch(function() {
          Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Could not reach server.'
          });
        });
    }

    function deleteRoadById(id) {
      Swal.fire({
        title: 'Delete this road?',
        text: 'Road status will be removed.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e53935',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
      }).then(function(result) {
        if (!result.isConfirmed) return;

        Swal.fire({
          title: 'Deleting...',
          allowOutsideClick: false,
          showConfirmButton: false,
          didOpen: function() {
            Swal.showLoading();
          }
        });

        fetch('actions/police_delete_road.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              id: id
            })
          })
          .then(function(res) {
            return res.json();
          })
          .then(function(data) {
            if (data.status === 'success') {
              var r = policeRoads.find(function(x) {
                return (x.dbId || x.id) === id;
              });
              if (r && r._poly) lgMyRoads.removeLayer(r._poly);
              policeRoads = policeRoads.filter(function(x) {
                return (x.dbId || x.id) !== id;
              });
              document.getElementById('lc-myroads').textContent = policeRoads.length;
              map.closePopup();
              // Swal.fire({ icon:'success', title:'Deleted!', timer:1500, showConfirmButton:false });
              Swal.fire({
                  icon: 'success',
                  title: 'Deleted',
                  timer: 1500,
                  showConfirmButton: false
                })
                .then(function() {
                  window.location.reload();
                });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
              });
            }
          })
          .catch(function() {
            Swal.fire({
              icon: 'error',
              title: 'Connection Error',
              text: 'Could not reach server.'
            });
          });
      });
    }

    function cancelMode() {
      cancelModeUI();
      if (routePolyline) {
        map.removeLayer(routePolyline);
        routePolyline = null;
      }
      if (roadPolyline) {
        map.removeLayer(roadPolyline);
        roadPolyline = null;
      }
      tempMarkers.forEach(function(m) {
        map.removeLayer(m);
      });
      tempMarkers = [];
      routePoints = [];
      roadPoints = [];
    }

    function cancelModeUI() {
      currentMode = null;
      document.getElementById('modeBanner').classList.remove('show');
      document.getElementById('modeInd').textContent = '';
      map.getContainer().style.cursor = '';
    }

    function loadDBData() {
      dbEvacRoutes.forEach(function(r) {
        var route = {
          id: idSeq++,
          dbId: r.id,
          from: r.from_name,
          to: r.to_name,
          status: r.route_status,
          notes: r.notes || '',
          points: (r.route_points || []).map(function(p) {
            return Array.isArray(p) ? p : [parseFloat(p.lat || p[0]), parseFloat(p.lng || p[1])];
          })
        };
        if (route.points.length >= 2) {
          evacRoutes.push(route);
          renderRouteOnMap(route);
        }
      });
      document.getElementById('lc-evac').textContent = evacRoutes.length;

      dbPoliceRoads.forEach(function(r) {
        var road = {
          id: idSeq++,
          dbId: r.id,
          name: r.name,
          status: r.status,
          reason: r.reason || '',
          points: (r.route_points || []).map(function(p) {
            return Array.isArray(p) ? p : [parseFloat(p.lat || p[0]), parseFloat(p.lng || p[1])];
          })
        };
        if (road.points.length >= 2) {
          policeRoads.push(road);
          renderRoadOnMap(road);
        }
      });
      document.getElementById('lc-myroads').textContent = policeRoads.length;

      dbUnitAlerts.forEach(function(a) {
        var col = a.severity === 'high' ? '#e53935' : a.severity === 'medium' ? '#f59e0b' : '#2e7d32';
        var icon = L.divIcon({
          className: '',
          html: '<div style="width:26px;height:26px;border-radius:50%;background:' + col + ';border:3px solid #fff;display:flex;align-items:center;justify-content:center;font-size:11px;box-shadow:0 2px 8px rgba(0,0,0,.25);">⚠️</div>',
          iconSize: [26, 26],
          iconAnchor: [13, 13]
        });
        L.marker([a.lat, a.lng], {
            icon
          })
          .bindPopup('<div style="font-weight:700;font-size:13px;margin-bottom:5px;">' + escH(a.title) + '</div>' +
            '<span style="padding:2px 7px;border-radius:4px;font-size:10px;font-weight:700;background:' + (a.severity === 'high' ? '#fdecea' : a.severity === 'medium' ? '#fffbeb' : '#e8f5e9') + ';color:' + (a.severity === 'high' ? '#e53935' : a.severity === 'medium' ? '#b45309' : '#2e7d32') + '">' + a.severity + '</span>' +
            (a.desc ? '<div style="margin-top:6px;font-size:11px;color:#475569;">' + escH(a.desc) + '</div>' : ''))
          .addTo(lgAlerts);
      });
      document.getElementById('lc-alerts').textContent = dbUnitAlerts.length;

      dbUnitWarnZ.forEach(function(z) {
        L.circle([z.center_lat, z.center_lng], {
            radius: z.radius_meters,
            color: '#f59e0b',
            fillColor: '#f59e0b',
            fillOpacity: .12,
            weight: 2,
            dashArray: '6,4'
          })
          .bindPopup('<div style="font-weight:700;">⚡ ' + escH(z.name) + '</div><div style="font-size:11px;color:#b45309;margin-top:3px;">Warning Zone</div>')
          .addTo(lgZones);
      });
      dbUnitSafeZ.forEach(function(z) {
        L.circle([z.center_lat, z.center_lng], {
            radius: z.radius_meters,
            color: '#2e7d32',
            fillColor: '#2e7d32',
            fillOpacity: .12,
            weight: 2,
            dashArray: '6,4'
          })
          .bindPopup('<div style="font-weight:700;">🛡️ ' + escH(z.name) + '</div><div style="font-size:11px;color:#2e7d32;margin-top:3px;">Safe Zone</div>')
          .addTo(lgZones);
      });
      document.getElementById('lc-zones').textContent = dbUnitWarnZ.length + dbUnitSafeZ.length;

      dbUnitRoads.forEach(function(r) {
        if (!r.route_points || r.route_points.length < 2) return;
        var colors = {
          open: '#2e7d32',
          warning: '#f59e0b',
          closed: '#e53935'
        };
        var col = colors[r.status] || '#64748b';
        L.polyline(r.route_points, {
            color: col,
            weight: 4,
            opacity: .7
          })
          .bindPopup('<div style="font-weight:700;font-size:13px;">🛣️ ' + escH(r.name) + '</div>' +
            '<div style="font-size:11px;color:#475569;margin-top:3px;">Unit dashboard road — read only</div>')
          .addTo(lgUnitRoads);
      });
      document.getElementById('lc-unitroads').textContent = dbUnitRoads.length;
      renderRouteSearchList();
    }

    function resetToUnit() {
      map.setView([_uc[0], _uc[1]], _uc[2]);
      toast('📍 Returned to ' + POLICE_LOCATION);
    }
    loadDBData();
    if (!HAS_MISSION) {
      document.querySelectorAll('.tool-btn').forEach(function(btn) {
        if (btn.getAttribute('onclick')) {
          btn.style.opacity = '0.4';
          btn.style.cursor = 'not-allowed';
          btn.onclick = function(e) {
            e.preventDefault();
            toast('⚠️ No active mission — map is locked');
          };
        }
      });
      var overlay = document.createElement('div');
      overlay.style.cssText =
        'position:absolute;inset:0;background:rgba(10,22,40,0.6);' +
        'z-index:1000;display:flex;align-items:center;justify-content:center;' +
        'border-radius:18px;flex-direction:column;gap:12px;';
      overlay.innerHTML =
        '<div style="color:#fff;font-size:18px;font-weight:700;">🔒 Map Locked</div>' +
        '<div style="color:#94a3b8;font-size:13px;text-align:center;max-width:260px;">' +
        'No active mission assigned to this unit.<br>Contact your commander to assign an incident.</div>';
      document.querySelector('.map-card').style.position = 'relative';
      document.querySelector('.map-card').appendChild(overlay);
    }

    <?php if ($incident_id && !$is_resolved): ?>
      setInterval(function() {
        fetch('actions/check_incident_status.php?incident_id=<?php echo (int)$incident_id; ?>')
          .then(function(r) {
            return r.json();
          })
          .then(function(data) {
            if (data.is_resolved) {
              Swal.fire({
                title: '🎖️ Mission Complete',
                html: '<b>' + INCIDENT_NAME + '</b> has been resolved.<br><br>' +
                  'Thank you for your service. Your unit\'s efforts made a difference.<br><br>' +
                  'The map is now locked until a new mission is assigned.',
                icon: 'success',
                confirmButtonText: 'Acknowledged',
                confirmButtonColor: '#2e7d32',
                allowOutsideClick: false
              }).then(function() {
                window.location.reload();
              });
            }
          })
          .catch(function() {});
      }, 30000);
    <?php endif; ?>

    function setRouteFilter(status, btn) {
      routeFilterStatus = status;
      document.querySelectorAll('.rf-status-btn').forEach(function(b) {
        b.classList.remove('active');
      });
      if (btn) btn.classList.add('active');
      renderRouteSearchList();
    }

    function searchRoutes(q) {
      routeSearchQuery = q.toLowerCase().trim();
      renderRouteSearchList();
    }

    function renderRouteSearchList() {
      var el = document.getElementById('routeSearchResults');
      if (!el) return;
      var q = routeSearchQuery;
      var filtered = evacRoutes.filter(function(r) {
        if (!q) return true;
        return r.from.toLowerCase().includes(q) ||
          r.to.toLowerCase().includes(q) ||
          (r.notes || '').toLowerCase().includes(q);
      });

      if (!filtered.length) {
        el.innerHTML = '<p style="font-size:12px;color:var(--text3);padding:6px 2px;">' +
          (q ? 'No routes match "' + escH(q) + '".' : 'No routes yet.') +
          '</p>';
        return;
      }
      var colors = {
        open: '#2e7d32',
        warning: '#b45309',
        closed: '#e53935'
      };
      var bgColors = {
        open: '#e8f5e9',
        warning: '#fffbeb',
        closed: '#fdecea'
      };
      el.innerHTML = filtered.map(function(r) {
        var col = colors[r.status] || '#64748b';
        var bg = bgColors[r.status] || '#f1f5f9';
        var label = r.status.charAt(0).toUpperCase() + r.status.slice(1);
        var rid = r.dbId || r.id;
        return '<div class="route-result-item" onclick="focusRouteOnMap(' + rid + ')">' +
          '<div class="rri-head">' +
          '<span class="rri-name">🗺️ ' + escH(r.from) + ' → ' + escH(r.to) + '</span>' +
          '<span class="rri-badge" style="background:' + bg + ';color:' + col + '">' + label + '</span>' +
          '</div>' +
          (r.notes ? '<div class="rri-notes" style="font-size:11px;color:#94a3b8;margin-bottom:5px;">' + escH(r.notes) + '</div>' : '') +
          '<div style="display:flex;gap:4px;">' +
          '<button onclick="event.stopPropagation();editRouteById(' + rid + ')" ' +
          'style="flex:1;padding:4px;border-radius:5px;border:none;background:#e8f0fe;color:#1d6ef5;font-size:11px;font-weight:600;cursor:pointer;">✏️ Edit</button>' +
          '<button onclick="event.stopPropagation();deleteRouteById(' + rid + ')" ' +
          'style="flex:1;padding:4px;border-radius:5px;border:none;background:#fdecea;color:#e53935;font-size:11px;font-weight:600;cursor:pointer;">🗑️ Del</button>' +
          '</div>' +
          '</div>';
      }).join('');
    }

    function focusRouteOnMap(id) {
      var r = evacRoutes.find(function(x) {
        return (x.dbId || x.id) === id;
      });
      if (!r || !r.points || r.points.length < 2) return;
      map.fitBounds(L.polyline(r.points).getBounds(), {
        padding: [60, 60]
      });
      if (r._poly) r._poly.openPopup();
    }
    var streetSearchTimer = null;

    function liveStreetSearch(q) {
      var box = document.getElementById('mapStreetResults');
      clearTimeout(streetSearchTimer);
      if (q.length < 3) {
        box.classList.remove('show');
        return;
      }

      streetSearchTimer = setTimeout(function() {
        fetch('https://nominatim.openstreetmap.org/search?format=json&q=' +
            encodeURIComponent(q + ', Lebanon') +
            '&limit=5&polygon_geojson=1')
          .then(function(r) {
            return r.json();
          })
          .then(function(data) {
            if (!data.length) {
              box.innerHTML = '<div class="sr-item" style="color:var(--text3)">No results found</div>';
              box.classList.add('show');
              return;
            }
            box.innerHTML = data.map(function(d) {
              return '<div class="sr-item" onclick="flyToStreet(' + d.lat + ',' + d.lon + ',\'' +
                d.display_name.replace(/'/g, "\\'") + '\')">' +
                '<div>' + d.display_name.split(',')[0] + '</div>' +
                '<div class="sr-sub">' + d.display_name.split(',').slice(1, 3).join(',') + '</div>' +
                '</div>';
            }).join('');
            box.classList.add('show');
          });
      }, 400);
    }

    function flyToStreet(lat, lon, name) {
      document.getElementById('mapStreetResults').classList.remove('show');
      document.getElementById('mapStreetSearch').value = name.split(',')[0];
      map.setView([parseFloat(lat), parseFloat(lon)], 17);
      var marker = L.circleMarker([parseFloat(lat), parseFloat(lon)], {
        radius: 10,
        color: '#1d6ef5',
        fillColor: '#1d6ef5',
        fillOpacity: 0.4,
        weight: 3
      }).addTo(map).bindPopup('📍 ' + name.split(',')[0]).openPopup();
      setTimeout(function() {
        map.removeLayer(marker);
      }, 4000);
      toast('📍 ' + name.split(',')[0]);
    }

    document.addEventListener('click', function(e) {
      var box = document.getElementById('mapStreetResults');
      var inp = document.getElementById('mapStreetSearch');
      if (box && inp && !box.contains(e.target) && e.target !== inp) {
        box.classList.remove('show');
      }
    });
  </script>
  <?php if ($_SESSION['type'] === 'police'): ?>
    <?php
    $sentMissions = $sentMissions ?? [];
    $sentCount = count($sentMissions) + count($canceledNotifs);
    ?>

    <div id="missionNotifOverlay" style="
        display:none; position:fixed; inset:0;
        background:rgba(10,22,40,0.45); z-index:999999;
        align-items:center; justify-content:center;
        backdrop-filter:blur(4px);">
      <div style="
            background:#fff; border-radius:18px; padding:24px;
            width:440px; max-width:92vw; max-height:85vh;
            overflow-y:auto; box-shadow:0 20px 60px rgba(10,22,40,0.25);">

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
          <div>
            <div style="font-size:16px;font-weight:800;color:#0f172a;">
              🔔 Mission Notifications
            </div>
            <div style="font-size:12px;color:#94a3b8;margin-top:2px;">
              Missions awaiting your response
            </div>
          </div>
          <button onclick="closeMissionNotif()" style="
                    width:30px;height:30px;border-radius:8px;border:none;
                    background:#f1f5f9;cursor:pointer;font-size:14px;color:#475569;">✕</button>
        </div>

        <?php if ($sentCount === 0 && count($canceledNotifs) === 0):  ?>
          <div style="text-align:center;padding:30px 0;color:#94a3b8;">
            <div style="font-size:32px;margin-bottom:10px;">✅</div>
            <div style="font-size:13px;">No pending missions</div>
          </div>
        <?php else: ?>
          <?php foreach ($sentMissions as $m): ?>
            <div style="
                        border:1.5px solid #e2e8f0; border-radius:14px;
                        padding:16px; margin-bottom:12px;">

              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                <span style="font-size:13px;font-weight:700;color:#0f172a;">
                  📋 <?php echo htmlspecialchars($m['title']); ?>
                </span>
                <span style="
                                padding:3px 10px; border-radius:6px; font-size:11px; font-weight:700;
                                background:<?php echo $m['priority'] === 'High' ? '#fdecea' : ($m['priority'] === 'Medium' ? '#fffbeb' : '#e8f5e9'); ?>;
                                color:<?php echo $m['priority'] === 'High' ? '#e53935' : ($m['priority'] === 'Medium' ? '#b45309' : '#2e7d32'); ?>;">
                  <?php echo htmlspecialchars($m['priority']); ?>
                </span>
              </div>

              <?php if (!empty($m['incident_name'])): ?>
                <div style="font-size:11px;color:#475569;margin-bottom:8px;">
                  <span style="color:#94a3b8;">Incident:</span>
                  <span style="font-weight:600;"><?php echo htmlspecialchars($m['incident_name']); ?></span>
                </div>
              <?php endif; ?>

              <?php if (!empty($m['description'])): ?>
                <div style="
                                font-size:12px;color:#475569;
                                background:#f8fafc;border-radius:8px;
                                padding:9px 11px;margin-bottom:12px;line-height:1.5;">
                  <?php echo htmlspecialchars($m['description']); ?>
                </div>
              <?php endif; ?>

              <div style="display:flex;gap:8px;">
                <button onclick="respondMission(<?php echo (int)$m['mission_id']; ?>, 'accept')" style="
                                flex:1;padding:9px;border-radius:9px;border:none;
                                background:#2e7d32;color:#fff;font-size:13px;
                                font-weight:700;cursor:pointer;">
                  ✔ Accept
                </button>
                <button onclick="respondMission(<?php echo (int)$m['mission_id']; ?>, 'reject')" style="
                                flex:1;padding:9px;border-radius:9px;
                                border:1.5px solid #e2e8f0;
                                background:#fff;color:#64748b;font-size:13px;
                                font-weight:700;cursor:pointer;">
                  ✕ Reject
                </button>
              </div>
            </div>
          <?php endforeach; ?>
          <?php foreach ($canceledNotifs as $notif): ?>
            <div style="border:1.5px solid #fde8e8;border-radius:14px;padding:16px;margin-bottom:12px;background:#fff5f5;">
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <span style="font-size:22px;">❌</span>
                <div>
                  <div style="font-size:13px;font-weight:700;color:#e53935;">Mission Canceled</div>
                  <div style="font-size:11px;color:#94a3b8;"><?php echo date('H:i · d M Y', strtotime($notif['created_at'])); ?></div>
                </div>
              </div>
              <div style="font-size:12px;color:#475569;background:#fef2f2;border-radius:8px;padding:9px 11px;">
                <?php echo htmlspecialchars($notif['message']); ?>
              </div>
              <button onclick="respondMission(<?php echo (int)$notif['id']; ?>, 'read')"
                style="margin-top:10px;width:100%;padding:8px;border-radius:9px;
                   border:1.5px solid #fecaca;background:#fff;color:#e53935;font-size:12px;font-weight:600;cursor:pointer;">
                ✓ Dismiss
              </button>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <script>
      function openMissionNotif() {
        document.getElementById('missionNotifOverlay').style.display = 'flex';
      }

      function closeMissionNotif() {
        document.getElementById('missionNotifOverlay').style.display = 'none';
      }
      document.addEventListener('click', function(e) {
        var overlay = document.getElementById('missionNotifOverlay');
        if (overlay && e.target === overlay) closeMissionNotif();
      });

      function respondMission(missionId, action) {
        closeMissionNotif();
        if (action === 'read') {
          $.post('actions/mark_notifs_read.php', {
            id: missionId
          }, function() {
            location.reload();
          }, 'json');
          return;
        }
        Swal.fire({
          title: action === 'accept' ? 'Accepting...' : 'Rejecting...',
          allowOutsideClick: false,
          showConfirmButton: false,
          didOpen: function() {
            Swal.showLoading();
          }
        });
        fetch('actions/police_respond_mission.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              mission_id: missionId,
              action: action
            })
          })
          .then(function(r) {
            return r.json();
          })
          .then(function(data) {
            if (data.status === 'success') {
              Swal.fire({
                icon: 'success',
                title: action === 'accept' ? '✔ Mission Accepted!' : 'Mission Rejected',
                timer: 1500,
                showConfirmButton: false
              }).then(function() {
                window.location.reload();
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Something went wrong'
              });
            }
          })
          .catch(function() {
            Swal.fire({
              icon: 'error',
              title: 'Connection Error',
              text: 'Could not reach server.'
            });
          });
      }
    </script>
  <?php endif; ?>
  <?php
  $mRow = $police->getRowSafe(
    "SELECT current_mission_id FROM police_units WHERE organization_id = ? LIMIT 1",
    [$org_id]
  );
  $missionIdForPoll = (int)($mRow['current_mission_id'] ?? 0);
  ?>

  <?php if ($missionIdForPoll > 0 && !$is_resolved): ?>
    <script>
      var _lastMissionStatus = 'active';
      var _missionId = <?= $missionIdForPoll ?>;

      setInterval(function() {
        fetch('actions/poll_mission_status.php?mission_id=' + _missionId)
          .then(function(r) {
            return r.json();
          })
          .then(function(data) {
            if (!data.mission_status) return;
            if (data.mission_status === _lastMissionStatus) return;

            if (data.mission_status === 'completed') {
              _lastMissionStatus = 'completed';
              Swal.fire({
                title: '🎖️ Mission Complete',
                html: '<b>' + (data.title || 'Your mission') + '</b> has been completed.<br><br>The map is now locked.',
                icon: 'success',
                confirmButtonText: 'Acknowledged',
                confirmButtonColor: '#2e7d32',
                allowOutsideClick: false
              }).then(function() {
                window.location.reload();
              });
            }

            if (data.mission_status === 'none') {
              _lastMissionStatus = 'none';
              Swal.fire({
                title: '❌ Mission Canceled',
                html: 'Your mission has been <b>canceled</b>.<br>The map is now locked.',
                icon: 'warning',
                confirmButtonText: 'OK',
                confirmButtonColor: '#e53935',
                allowOutsideClick: false
              }).then(function() {
                window.location.reload();
              });
            }
          })
          .catch(function() {});
      }, 10000);
    </script>
  <?php endif; ?>
</body>

</html>