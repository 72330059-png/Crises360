<?php
session_start();
require_once("class/map.class.php");
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php"); exit;
}
$map = new maps();
$activeIncidents = $map->getActiveIncidents();
$activeIncidentId = (int)($_SESSION['active_incident'] ?? 0);
$activeIncidentName = '';
foreach($activeIncidents as $inc) {
    if($inc['id'] == $activeIncidentId) {
        $activeIncidentName = $inc['incident_name'];
        break;
    }
}
$isResolvedSession = false;
if($activeIncidentId && !$activeIncidentName) {
    $isResolvedSession = true;
}
$archiveMode = isset($_SESSION['archive_incident']) && $_SESSION['archive_incident'] > 0;
$archiveIncidentId = (int)($_SESSION['archive_incident'] ?? 0);
$viewAll = isset($_SESSION['view_all']) && $_SESSION['view_all'] === true;

if($isResolvedSession) {
    $mapData = ['alerts'=>[], 'warnZones'=>[], 'safeZones'=>[], 'roads'=>[]];
} elseif($archiveMode) {
    $mapData = $map->getAllMapDataArchive($archiveIncidentId);
    $viewAll = false;
} elseif($viewAll) {
    $mapData = $map->getAllMapData(null);
} elseif($activeIncidentId) {
    $mapData = $map->getAllMapData($activeIncidentId);
} else {
    $mapData = ['alerts'=>[], 'warnZones'=>[], 'safeZones'=>[], 'roads'=>[]];
}
$counts  = $map->getMapCounts();
$alerts = array_map(function($a){
    return [
        'id'       => (int)$a['id'],
        'title'    => $a['title'],
        'severity' => $a['severity'],
        'desc'     => $a['description'],  
        'lat'      => (float)$a['lat'],
        'lng'      => (float)$a['lng'],
        'region'   => $a['region'],
    ];
}, $mapData['alerts']);

$warns = array_map(function($z){
    return [
        'id'     => (int)$z['id'],
        'name'   => $z['name'],
        'center' => [(float)$z['center_lat'], (float)$z['center_lng']],
        'radius' => (int)$z['radius_meters'],
        'region' => $z['region'],
    ];
}, $mapData['warnZones']);

$safes = array_map(function($z){
    return [
        'id'     => (int)$z['id'],
        'name'   => $z['name'],
        'center' => [(float)$z['center_lat'], (float)$z['center_lng']],
        'radius' => (int)$z['radius_meters'],
        'region' => $z['region'],
    ];
}, $mapData['safeZones']);

$roads = array_map(function($r){
    return [
        'id'     => (int)$r['id'],
        'name'   => $r['name'],
        'status' => $r['status'],
        'reason' => $r['reason'],
        'points' => $r['route_points'],  
    ];
}, $mapData['roads']);

$policeRoadsData = [];
$evacRoutesData  = [];
if ($activeIncidentId && !$isResolvedSession) {
    $rawPR = $map->getPoliceRoadsByIncident($activeIncidentId);
    $policeRoadsData = array_map(function($r){
        return [
            'id'     => (int)$r['id'],
            'name'   => $r['name'],
            'status' => $r['status'],   // blocked / warning / safe
            'reason' => $r['reason'],
            'points' => $r['route_points'],
            'region' => $r['region'],
        ];
    }, $rawPR);

    $rawER = $map->getEvacRoutesByIncident($activeIncidentId);
    $evacRoutesData = array_map(function($r){
        return [
            'id'     => (int)$r['id'],
            'name'   => $r['from_name'].' → '.$r['to_name'],
            'status' => $r['route_status'],  // open / warning / closed
            'notes'  => $r['notes'],
            'points' => $r['route_points'],
            'region' => $r['region'],
        ];
    }, $rawER);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lebanon Crisis Map</title>
<?php include('includes/header.php'); ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
:root{
  --red:#e53935;--red-bg:#fdecea;
  --yellow:#f59e0b;--yellow-bg:#fffbeb;
  --green:#2e7d32;--green-bg:#e8f5e9;
  --blue:#1d6ef5;--blue-bg:#e8f0fe;
  --purple:#7c3aed;--purple-bg:#f3e8ff;
  --gray:#64748b;--gray-bg:#f1f5f9;
  --text:#0f172a;--text2:#475569;--text3:#94a3b8;
  --border:#e2e8f0;--surface:#fff;--bg:#f0f4f8;
}
.crisis-topbar{background:var(--surface);border-radius:16px;border:1px solid var(--border);padding:12px 16px;margin-bottom:14px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.crisis-title{font-size:18px;font-weight:700;color:var(--text);margin-right:4px;white-space:nowrap;}
.tb-sep{width:1px;height:26px;background:var(--border);flex-shrink:0;}
.tb-search{position:relative;min-width:160px;flex:1;max-width:230px;}
.tb-search input{width:100%;padding:7px 10px 7px 30px;border:1.5px solid var(--border);border-radius:9px;font-size:13px;font-family:inherit;outline:none;background:var(--surface);color:var(--text);transition:border .15s;}
.tb-search input:focus{border-color:var(--blue);}
.tb-search-ic{position:absolute;left:9px;top:50%;transform:translateY(-50%);color:var(--text3);font-size:12px;}
.tb-select{padding:7px 11px;border:1.5px solid var(--border);border-radius:9px;font-size:13px;font-family:inherit;background:var(--surface);color:var(--text);outline:none;cursor:pointer;}
.tb-select:focus{border-color:var(--blue);}
.tb-btn{padding:7px 12px;border:1.5px solid var(--border);border-radius:9px;background:var(--surface);font-size:12px;font-family:inherit;color:var(--text2);cursor:pointer;font-weight:500;white-space:nowrap;display:flex;align-items:center;gap:5px;transition:all .15s;}
.tb-btn:hover{border-color:var(--red);color:var(--red);}
.map-card{background:var(--surface);border-radius:18px;border:1px solid var(--border);overflow:hidden;}
.map-toolbar{display:flex;align-items:center;gap:5px;flex-wrap:wrap;padding:11px 13px;border-bottom:1px solid var(--border);}
.map-toolbar-lbl{font-size:11px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.5px;margin-right:2px;}
.tool-btn{display:flex;align-items:center;gap:5px;padding:6px 11px;border-radius:8px;font-size:12px;font-weight:600;font-family:inherit;cursor:pointer;border:1.5px solid transparent;transition:all .15s;}
.tb-alert{background:var(--red-bg);color:var(--red);border-color:rgba(229,57,53,.2);}
.tb-alert:hover{background:#fbd1cf;border-color:var(--red);}
.tb-radius{background:#fff3e0;color:#e65100;border-color:rgba(230,81,0,.2);}
.tb-radius:hover{background:#ffe0b2;border-color:#e65100;}
.tb-warn{background:var(--yellow-bg);color:#b45309;border-color:rgba(245,158,11,.2);}
.tb-warn:hover{background:#fde68a;border-color:var(--yellow);}
.tb-zone{background:var(--green-bg);color:var(--green);border-color:rgba(46,125,50,.2);}
.tb-zone:hover{background:#c8e6c9;border-color:var(--green);}
.tb-road{background:var(--blue-bg);color:var(--blue);border-color:rgba(29,110,245,.2);}
.tb-road:hover{background:#bbdefb;border-color:var(--blue);}
.tb-route{background:var(--purple-bg);color:var(--purple);border-color:rgba(124,58,237,.2);}
.tb-route:hover{background:#e9d5ff;border-color:var(--purple);}
.tb-clr{background:var(--gray-bg);color:var(--gray);border-color:var(--border);}
.tb-clr:hover{background:var(--border);}
.tool-sep{width:1px;height:21px;background:var(--border);margin:0 2px;}
#map{height:520px;width:100%;}
.map-statusbar{display:flex;align-items:center;gap:8px;padding:8px 13px;border-top:1px solid var(--border);font-size:12px;color:var(--text3);flex-wrap:wrap;}
.live-dot{width:7px;height:7px;border-radius:50%;background:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,.2);animation:lpulse 2s infinite;}
@keyframes lpulse{0%,100%{box-shadow:0 0 0 3px rgba(34,197,94,.2)}50%{box-shadow:0 0 0 6px rgba(34,197,94,.04)}}
.mode-banner{display:none;align-items:center;gap:10px;background:#0a1628;color:#fff;padding:10px 15px;border-radius:11px;font-size:13px;font-weight:500;margin-bottom:12px;}
.mode-banner.show{display:flex;}
.mode-dot{width:8px;height:8px;border-radius:50%;background:#fbbf24;animation:mblink 1s infinite;}
@keyframes mblink{0%,100%{opacity:1}50%{opacity:.3}}
.mode-cancel{margin-left:auto;padding:5px 11px;border-radius:7px;background:rgba(255,255,255,.15);border:none;color:#fff;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;}
.mode-cancel:hover{background:rgba(255,255,255,.25);}
.side-card{background:var(--surface);border-radius:16px;border:1px solid var(--border);padding:15px;margin-bottom:11px;}
.side-title{font-size:13px;font-weight:700;color:var(--text);margin-bottom:13px;}
.layer-row{display:flex;align-items:center;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--gray-bg);}
.layer-row:last-child{border-bottom:none;padding-bottom:0;}
.layer-left{display:flex;align-items:center;gap:8px;}
.ltoggle{width:34px;height:18px;border-radius:9px;background:#e2e8f0;position:relative;cursor:pointer;border:none;transition:background .2s;flex-shrink:0;}
.ltoggle.on{background:var(--blue);}
.ltoggle::after{content:'';position:absolute;width:12px;height:12px;border-radius:50%;background:#fff;top:3px;left:3px;transition:left .2s;box-shadow:0 1px 3px rgba(0,0,0,.2);}
.ltoggle.on::after{left:19px;}
.lname{font-size:13px;font-weight:500;color:#334155;}
.lcnt{font-size:11px;font-weight:700;padding:2px 7px;border-radius:5px;}
.lc-r{background:var(--red-bg);color:var(--red);}
.lc-y{background:var(--yellow-bg);color:#b45309;}
.lc-g{background:var(--green-bg);color:var(--green);}
.lc-b{background:var(--blue-bg);color:var(--blue);}
.route-finder{background:linear-gradient(135deg,#0a1628 0%,#1e3a5f 100%);border-radius:14px;padding:15px;margin-bottom:11px;color:#fff;}
.rf-title{font-size:13px;font-weight:700;margin-bottom:11px;}
.rf-input{width:100%;padding:8px 11px;border:1.5px solid rgba(255,255,255,.2);border-radius:9px;font-size:13px;font-family:inherit;background:rgba(255,255,255,.1);color:#fff;outline:none;margin-bottom:7px;}
.rf-input::placeholder{color:rgba(255,255,255,.45);}
.rf-input:focus{border-color:rgba(255,255,255,.5);}
.rf-btn{width:100%;padding:9px;border:none;border-radius:9px;background:var(--blue);color:#fff;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;transition:background .15s;}
.rf-btn:hover{background:#1558d6;}
.rf-result{margin-top:10px;padding:10px;background:rgba(255,255,255,.1);border-radius:9px;font-size:12px;color:rgba(255,255,255,.9);display:none;line-height:1.7;}
.rf-result.show{display:block;}
.leg-row{display:flex;align-items:center;gap:9px;padding:5px 0;}
.leg-box{width:26px;height:16px;border-radius:4px;}
.lb-danger{background:rgba(229,57,53,.15);border:2px dashed var(--red);}
.lb-warn{background:rgba(245,158,11,.15);border:2px dashed var(--yellow);}
.lb-safe{background:rgba(46,125,50,.15);border:2px dashed var(--green);}
.leg-road-c{width:26px;height:4px;border-radius:2px;background:var(--red);}
.leg-road-w{width:26px;height:4px;border-radius:2px;background:var(--yellow);}
.leg-road-o{width:26px;height:4px;border-radius:2px;background:var(--green);}
.leg-lbl{font-size:12px;font-weight:500;color:var(--text2);}
.mp-overlay{position:fixed;inset:0;background:rgba(10,22,40,.45);z-index:2000;display:none;align-items:center;justify-content:center;backdrop-filter:blur(4px);}
.mp-overlay.show{display:flex;}
.mp-box{background:#fff;border-radius:20px;padding:24px;width:400px;max-width:92vw;box-shadow:0 20px 60px rgba(10,22,40,.25);max-height:90vh;overflow-y:auto;}
.mp-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;}
.mp-title{font-size:16px;font-weight:700;color:var(--text);}
.mp-close{width:30px;height:30px;border-radius:8px;border:none;background:var(--gray-bg);cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;color:var(--text2);}
.mp-close:hover{background:var(--border);}
.mp-field{margin-bottom:12px;}
.mp-label{display:block;font-size:11px;font-weight:600;color:var(--text2);text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px;}
.mp-input{width:100%;padding:8px 11px;border:1.5px solid var(--border);border-radius:9px;font-size:13px;font-family:inherit;color:var(--text);outline:none;transition:border .15s;background:#fff;}
.mp-input:focus{border-color:var(--blue);}
.mp-input[readonly]{background:var(--gray-bg);font-family:monospace;font-size:12px;}
.mp-tip{border-radius:9px;padding:9px 12px;font-size:12px;line-height:1.55;margin-bottom:10px;}
.tip-blue{background:var(--blue-bg);color:#1e40af;}
.tip-green{background:var(--green-bg);color:#1b5e20;}
.tip-red{background:var(--red-bg);color:#b71c1c;}
.tip-yellow{background:var(--yellow-bg);color:#78350f;}
.mp-foot{display:flex;gap:8px;justify-content:flex-end;margin-top:14px;flex-wrap:wrap;}
.btn-cancel{padding:8px 15px;border:1.5px solid var(--border);border-radius:9px;background:#fff;font-size:13px;font-family:inherit;color:var(--text2);cursor:pointer;}
.btn-cancel:hover{background:var(--gray-bg);}
.btn-submit{padding:8px 16px;border:none;border-radius:9px;font-size:13px;font-family:inherit;color:#fff;cursor:pointer;font-weight:600;transition:background .15s;}
.btn-blue{background:var(--blue);}   .btn-blue:hover{background:#1558d6;}
.btn-red{background:var(--red);}     .btn-red:hover{background:#c62828;}
.btn-green{background:var(--green);} .btn-green:hover{background:#1b5e20;}
.btn-yellow{background:#d97706;}     .btn-yellow:hover{background:#b45309;}
.btn-purple{background:var(--purple);}.btn-purple:hover{background:#6d28d9;}
.street-results{max-height:150px;overflow-y:auto;border:1.5px solid var(--border);border-radius:9px;background:#fff;display:none;margin-top:4px;}
.street-results.show{display:block;}
.sr-item{padding:9px 12px;cursor:pointer;font-size:13px;color:var(--text);border-bottom:1px solid var(--gray-bg);transition:background .1s;}
.sr-item:last-child{border-bottom:none;}
.sr-item:hover{background:var(--blue-bg);}
.sr-sub{font-size:11px;color:var(--text3);margin-top:1px;}
.map-toast{position:fixed;bottom:26px;left:50%;transform:translateX(-50%) translateY(30px);background:#0a1628;color:#fff;padding:10px 20px;border-radius:11px;font-size:13px;font-weight:500;z-index:3000;opacity:0;transition:all .3s;pointer-events:none;white-space:nowrap;box-shadow:0 8px 24px rgba(10,22,40,.3);}
.map-toast.show{opacity:1;transform:translateX(-50%) translateY(0);}
.alert-pin{width:32px;height:32px;border-radius:50%;background:var(--red);border:3px solid #fff;display:flex;align-items:center;justify-content:center;font-size:14px;box-shadow:0 3px 10px rgba(229,57,53,.4);}
.warn-pin{width:32px;height:32px;border-radius:50%;background:var(--yellow);border:3px solid #fff;display:flex;align-items:center;justify-content:center;font-size:14px;box-shadow:0 3px 10px rgba(245,158,11,.4);}
.safe-pin{width:32px;height:32px;border-radius:50%;background:var(--green);border:3px solid #fff;display:flex;align-items:center;justify-content:center;font-size:14px;box-shadow:0 3px 10px rgba(46,125,50,.4);}
.leaflet-popup-content-wrapper{border-radius:12px!important;box-shadow:0 8px 24px rgba(0,0,0,.12)!important;}
.leaflet-popup-content{font-family:'Segoe UI',sans-serif!important;font-size:13px!important;margin:12px 16px!important;}
.pop-title{font-weight:700;font-size:14px;margin-bottom:6px;}
.pop-row{display:flex;justify-content:space-between;gap:10px;color:#475569;margin-bottom:3px;font-size:12px;}
.pop-badge{display:inline-block;padding:2px 8px;border-radius:5px;font-size:11px;font-weight:700;margin-top:5px;}
.pb-r{background:var(--red-bg);color:var(--red);}
.pb-y{background:var(--yellow-bg);color:#b45309;}
.pb-g{background:var(--green-bg);color:var(--green);}
.pb-b{background:var(--blue-bg);color:var(--blue);}
.pb-p{background:var(--purple-bg);color:var(--purple);}
input[type=range]{width:100%;accent-color:var(--blue);}
.range-val{font-weight:700;color:var(--blue);font-family:monospace;}
/* ---- AI Assistant Panel ---- */
.ai-panel{background:linear-gradient(135deg,#0a1628 0%,#1e3a5f 100%);border-radius:14px;padding:15px;margin-bottom:11px;color:#fff;}
.ai-panel-title{font-size:13px;font-weight:700;margin-bottom:4px;display:flex;align-items:center;gap:7px;}
.ai-badge{font-size:10px;font-weight:700;background:#7c3aed;padding:2px 7px;border-radius:5px;letter-spacing:.5px;}
.ai-subtitle{font-size:11px;color:rgba(255,255,255,.5);margin-bottom:11px;}
.ai-textarea{width:100%;padding:9px 11px;border:1.5px solid rgba(255,255,255,.2);border-radius:9px;font-size:13px;font-family:inherit;background:rgba(255,255,255,.08);color:#fff;outline:none;resize:none;height:72px;line-height:1.5;}
.ai-textarea::placeholder{color:rgba(255,255,255,.35);}
.ai-textarea:focus{border-color:rgba(255,255,255,.45);}
.ai-examples{display:flex;flex-wrap:wrap;gap:5px;margin:8px 0;}
.ai-ex{font-size:10px;padding:3px 8px;border-radius:6px;background:rgba(255,255,255,.1);color:rgba(255,255,255,.7);cursor:pointer;border:none;font-family:inherit;transition:background .15s;}
.ai-ex:hover{background:rgba(255,255,255,.2);}
.ai-btn{width:100%;padding:9px;border:none;border-radius:9px;background:#7c3aed;color:#fff;font-size:13px;font-weight:700;font-family:inherit;cursor:pointer;transition:background .15s;display:flex;align-items:center;justify-content:center;gap:7px;}
.ai-btn:hover{background:#6d28d9;}
.ai-btn:disabled{background:#334155;cursor:not-allowed;}
.ai-thinking{display:none;align-items:center;gap:8px;margin-top:10px;font-size:12px;color:rgba(255,255,255,.6);}
.ai-thinking.show{display:flex;}
.ai-dots span{animation:aidot 1.2s infinite;display:inline-block;}
.ai-dots span:nth-child(2){animation-delay:.2s;}
.ai-dots span:nth-child(3){animation-delay:.4s;}
@keyframes aidot{0%,80%,100%{opacity:.2}40%{opacity:1}}
/* ---- Confirmation card (shown after AI responds) ---- */
.ai-confirm{display:none;background:rgba(255,255,255,.06);border:1.5px solid rgba(255,255,255,.15);border-radius:11px;padding:12px;margin-top:10px;}
.ai-confirm.show{display:block;}
.ai-confirm-title{font-size:12px;font-weight:700;color:#fbbf24;margin-bottom:8px;}
.ai-loc-list{margin-bottom:9px;}
.ai-loc-item{display:flex;align-items:center;gap:7px;padding:5px 0;border-bottom:1px solid rgba(255,255,255,.08);font-size:12px;}
.ai-loc-item:last-child{border-bottom:none;}
.ai-loc-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}
.ai-loc-dot.found{background:#22c55e;}
.ai-loc-dot.notfound{background:#ef4444;}
.ai-loc-name{font-weight:600;flex:1;}
.ai-loc-coords{font-family:monospace;font-size:10px;color:rgba(255,255,255,.5);}
.ai-action-info{font-size:11px;color:rgba(255,255,255,.6);margin-bottom:10px;line-height:1.6;}
.ai-confirm-btns{display:flex;gap:7px;}
.ai-confirm-yes{flex:1;padding:8px;border:none;border-radius:8px;background:#22c55e;color:#fff;font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;}
.ai-confirm-yes:hover{background:#16a34a;}
.ai-confirm-no{padding:8px 12px;border:1.5px solid rgba(255,255,255,.2);border-radius:8px;background:transparent;color:rgba(255,255,255,.7);font-size:12px;font-family:inherit;cursor:pointer;}
.ai-confirm-no:hover{background:rgba(255,255,255,.1);}
.ai-result-msg{display:none;margin-top:10px;padding:9px 11px;border-radius:9px;font-size:12px;line-height:1.6;}
.ai-result-msg.show{display:block;}
.ai-result-msg.success{background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.3);color:#86efac;}
.ai-result-msg.error{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5;}
</style>
</head>
<body>
<?php include('includes/sidebar.php'); ?>
<?php include('includes/nav.php'); ?>
<div class="main-content">
<div class="container-fluid py-4">

<div class="crisis-topbar">
  <div class="tb-search"><span class="tb-search-ic">🔍</span><input type="text" id="locSearch" placeholder="Search city or location..."></div>
  <select class="tb-select" id="regionFilter">
    <option value="all">All Regions</option>
    <option value="beirut">Beirut</option>
    <option value="south">South Lebanon</option>
    <option value="bekaa">Bekaa</option>
    <option value="mount">Mount Lebanon</option>
    <option value="north">North Lebanon</option>
  </select>
  <div class="tb-sep"></div>
<select class="tb-select" id="incidentSelect" onchange="setIncident(this.value)" 
  style="border-color:<?php echo $activeIncidentId ? 'var(--red)' : 'var(--border)'; ?>">
  <option value="0">— Select Incident —</option>
  <?php foreach($activeIncidents as $inc): ?>
    <option value="<?php echo $inc['id']; ?>" <?php echo $inc['id']==$activeIncidentId ? 'selected' : ''; ?>>
      🚨 <?php echo htmlspecialchars($inc['incident_name']); ?> — <?php echo htmlspecialchars($inc['location']); ?>
    </option>
  <?php endforeach; ?>
</select>
<?php if($activeIncidentId): ?>
<span style="font-size:11px;font-weight:600;color:var(--red);background:var(--red-bg);padding:4px 9px;border-radius:7px;">
  🔴 Active
</span>
<?php endif; ?>
  <button class="tb-btn" id="btnClear">✕ Reset</button>
</div>

<div class="mode-banner" id="modeBanner">
  <span class="mode-dot"></span>
  <span id="modeText">...</span>
  <button class="mode-cancel" onclick="cancelMode()">✕ Cancel</button>
</div>

<div class="row g-3">

  <div class="col-lg-9">
    <div class="map-card" style="position:relative;">
      <div class="map-toolbar">
        <span class="map-toolbar-lbl">Add:</span>
          <?php if(!$viewAll && !$archiveMode): ?>
<button class="tool-btn tb-alert" onclick="openPopup('popAlert')">⚠️ Alert Pin</button>
<button class="tool-btn tb-radius" onclick="openPopup('popRadius')">🎯 Radius Zone</button>
<div class="tool-sep"></div>
<button class="tool-btn tb-road" onclick="openPopup('popRoad')">🛣️ Road Status</button>
<?php endif; ?>
 <div class="tool-sep"></div>
 <?php if($activeIncidentId && !$archiveMode): ?>
<button class="tb-btn" id="btnViewRoutes" onclick="toggleRoutesLayer()"
  style="border-color:var(--purple);color:var(--purple);">
  🚦 View Routes
</button>
<?php endif; ?>
        <div class="tool-sep"></div>
        <!-- <button class="tool-btn tb-clr"    onclick="clearAll()">🗑️ Clear</button> -->
         <?php if(!$viewAll): ?>
<button class="tb-btn" id="btnViewAll" onclick="setViewAll()" 
  style="border-color:var(--blue);color:var(--blue);">🗺️ View All</button>
<?php else: ?>
<button class="tb-btn" id="btnExitViewAll" onclick="exitViewAll()" 
  style="border-color:var(--green);color:var(--green);">✕ Exit View All</button>
<?php endif; ?>
      </div>

      <div id="map"></div>
      <div class="map-statusbar">
        <div class="live-dot"></div>
        Live &nbsp;·&nbsp; Lebanon &nbsp;·&nbsp;
        <span id="coordTxt" style="font-family:monospace">Hover for coordinates</span>
        &nbsp;·&nbsp;
        <span id="modeInd" style="color:var(--blue);font-weight:600"></span>
      </div>
      <!-- Floating Legend Button on map -->
<div id="legendFloat" style="position:absolute;bottom:48px;left:10px;z-index:1000;">
  <button onclick="toggleLegend()" 
    style="background:#fff;border:1.5px solid var(--border);border-radius:10px;padding:7px 12px;font-size:12px;font-weight:600;color:var(--text2);cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,.12);display:flex;align-items:center;gap:5px;">
    🗺️ Legend
  </button>
  <div id="legendBox" style="display:none;background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:12px 14px;margin-top:6px;box-shadow:0 4px 16px rgba(0,0,0,.12);min-width:180px;">
    <div class="leg-row"><div class="leg-box lb-danger"></div><span class="leg-lbl">Danger / Alert Zone</span></div>
    <div class="leg-row"><div class="leg-box lb-warn"></div><span class="leg-lbl">Warning Zone</span></div>
    <div class="leg-row"><div class="leg-box lb-safe"></div><span class="leg-lbl">Safe Zone</span></div>
    <div class="leg-row"><div class="leg-road-c"></div><span class="leg-lbl">Road — Closed</span></div>
    <div class="leg-row"><div class="leg-road-w"></div><span class="leg-lbl">Road — Warning</span></div>
    <div class="leg-row"><div class="leg-road-o"></div><span class="leg-lbl">Road — Open</span></div>
    <!-- <div class="leg-row"><div style="width:26px;height:4px;border-radius:2px;background:#f97316;"></div><span class="leg-lbl">Police Road</span></div> -->
    <div class="leg-row"><div style="width:26px;height:4px;border-radius:2px;background:#7c3aed;border-top:2px dashed #7c3aed;"></div><span class="leg-lbl">Evac Route</span></div>
  </div>
</div>
    </div>
  </div>

  <div class="col-lg-3">

   
 
<div class="ai-panel">
  <div class="ai-panel-title">
    🤖 AI Map Assistant
    <span class="ai-badge">Groq</span>
  </div>
  <div class="ai-subtitle">Type any situation — AI understands and places it on the map</div>
  <textarea class="ai-textarea" id="aiInput" 
    placeholder="e.g. Bint Jbeil and Khiam heavy shelling high danger&#10;or: close Tyre coastal road military checkpoint&#10;or: safe zone at Rafik Hariri hospital"></textarea>
  <div class="ai-examples">
    <button class="ai-ex" onclick="aiExample('Bint Jbeil high danger heavy shelling')">⚠️ Shelling alert</button>
    <button class="ai-ex" onclick="aiExample('Close coastal road Tyre military checkpoint')">🛣️ Road closed</button>
    <button class="ai-ex" onclick="aiExample('Safe zone Rafik Hariri hospital Beirut')">🛡️ Safe zone</button>
    <button class="ai-ex" onclick="aiExample('Warning zone Saida port area')">⚡ Warning zone</button>
  </div>
  <button class="ai-btn" id="aiSendBtn" onclick="sendToAI()">
    <span>✨</span> Analyze &amp; Place on Map
  </button>
  <div class="ai-thinking" id="aiThinking">
    <span>🧠 Gemini is reading...</span>
    <span class="ai-dots"><span>●</span><span>●</span><span>●</span></span>
  </div>
  <div class="ai-confirm" id="aiConfirm">
    <div class="ai-confirm-title" id="aiConfirmTitle">📍 I found these locations:</div>
    <div class="ai-loc-list" id="aiLocList"></div>
    <div class="ai-action-info" id="aiActionInfo"></div>
    <div class="ai-confirm-btns">
      <button class="ai-confirm-yes" onclick="confirmAIAction()">✅ Yes, Add to Map</button>
      <button class="ai-confirm-no" onclick="cancelAIAction()">✕ Cancel</button>
    </div>
  </div>
  <!-- Result message after saving -->
  <div class="ai-result-msg" id="aiResultMsg"></div>
</div>
 

    <div class="side-card">
      <div class="side-title">Map Layers</div>
      <div class="layer-row"><div class="layer-left"><button class="ltoggle on" onclick="toggleLayer('alerts',this)"></button><span>⚠️</span><span class="lname">Alerts</span></div><span class="lcnt lc-r" id="lc-alerts"><?php echo $counts['alerts']; ?></span></div>
      <div class="layer-row"><div class="layer-left"><button class="ltoggle on" onclick="toggleLayer('warn',this)"></button><span>⚡</span><span class="lname">Warning Zones</span></div><span class="lcnt lc-y" id="lc-warn"><?php echo $counts['warn']; ?></span></div>
      <div class="layer-row"><div class="layer-left"><button class="ltoggle on" onclick="toggleLayer('safe',this)"></button><span>🛡️</span><span class="lname">Safe Zones</span></div><span class="lcnt lc-g" id="lc-safe"><?php echo $counts['safe']; ?></span></div>
      <div class="layer-row"><div class="layer-left"><button class="ltoggle on" onclick="toggleLayer('roads',this)"></button><span>🛣️</span><span class="lname">Road Status</span></div><span class="lcnt lc-b" id="lc-roads"><?php echo $counts['roads']; ?></span></div>
<div class="layer-row">
  <div class="layer-left">
    <button class="ltoggle" id="lt-policeroads" onclick="toggleLayer('policeroads',this)"></button>
    <span>🚔</span><span class="lname">Police Roads</span>
  </div>
  <span class="lcnt" style="background:var(--purple-bg);color:var(--purple)" id="lc-policeroads">0</span>
</div>
<div class="layer-row">
  <div class="layer-left">
    <button class="ltoggle" id="lt-evacroutes" onclick="toggleLayer('evacroutes',this)"></button>
    <span>🟣</span><span class="lname">Evac Routes</span>
  </div>
  <span class="lcnt" style="background:var(--purple-bg);color:var(--purple)" id="lc-evacroutes">0</span>
</div>
      <div class="layer-row"><div class="layer-left"><button class="ltoggle" id="lt-sat" onclick="switchBase(this)"></button><span>📡</span><span class="lname">Satellite</span></div></div>
    </div>
  </div>
</div>
</div>
</div>

<div class="mp-overlay" id="popAlert">
  <div class="mp-box">
    <div class="mp-head"><div class="mp-title">⚠️ Add Alert Pin</div><button class="mp-close" onclick="closePopup('popAlert')">✕</button></div>
    <div class="mp-field"><label class="mp-label">Title</label><input class="mp-input" id="aTitle" placeholder="e.g. Damaged building — Tyre port"></div>
    <div class="mp-field"><label class="mp-label">Severity</label>
      <select class="mp-input" id="aSev">
        <option value="high">🔴 High — Immediate danger</option>
        <option value="medium">🟡 Medium — Caution</option>
        <option value="low">🟢 Low — Monitor</option>
      </select>
    </div>
    <div class="mp-field"><label class="mp-label">Description</label><input class="mp-input" id="aDesc" placeholder="Details..."></div>
    <div class="mp-field"><label class="mp-label">Region</label>
      <select class="mp-input" id="aRegion">
        <option value="beirut">Beirut</option>
        <option value="south">South Lebanon</option>
        <option value="bekaa">Bekaa</option>
        <option value="mount">Mount Lebanon</option>
        <option value="north">North Lebanon</option>
      </select>
    </div>
    <div class="mp-field"><label class="mp-label">Coordinates</label>
      <input class="mp-input" id="aCoords" readonly placeholder="Click map after picking location...">
    </div>
    <div class="mp-tip tip-blue" id="aPickTip">📍 Fill the fields then click "Pick Location" — the map will wait for your click.</div>
    <div class="mp-tip tip-green" id="aSaveTip" style="display:none">✅ Location set — click Save to confirm.</div>
    <div class="mp-foot">
      <button class="btn-cancel" onclick="closePopup('popAlert')">Cancel</button>
      <button class="btn-submit btn-blue" onclick="startAlertMode()">📍 Pick Location</button>
      <button class="btn-submit btn-red" id="btnSaveAlert" style="display:none" onclick="confirmAlert()">💾 Save</button>
    </div>
  </div>
</div>

<div class="mp-overlay" id="popRadius">
  <div class="mp-box">
    <div class="mp-head"><div class="mp-title">🎯 Add Zone</div><button class="mp-close" onclick="closePopup('popRadius')">✕</button></div>
    <div class="mp-tip tip-red">Set the details and radius — then click the center on the map.</div>
    <div class="mp-field"><label class="mp-label">Zone Name</label><input class="mp-input" id="rzName" placeholder="e.g. Tyre port — blast radius"></div>
    <div class="mp-field"><label class="mp-label">Type</label>
      <select class="mp-input" id="rzType">
        <option value="danger">🔴 Danger Zone</option>
        <option value="warning">🟡 Warning Zone</option>
        <option value="safe">🟢 Safe Zone</option>
      </select>
    </div>
    <div class="mp-field"><label class="mp-label">Region</label>
      <select class="mp-input" id="rzRegion">
        <option value="beirut">Beirut</option>
        <option value="south">South Lebanon</option>
        <option value="bekaa">Bekaa</option>
        <option value="mount">Mount Lebanon</option>
        <option value="north">North Lebanon</option>
      </select>
    </div>
    <div class="mp-field"><label class="mp-label">Radius: <span class="range-val" id="rzVal">300</span> meters</label>
      <input type="range" id="rzRadius" min="50" max="5000" value="300" step="50" oninput="document.getElementById('rzVal').textContent=this.value"></div>
    <div class="mp-foot"><button class="btn-cancel" onclick="closePopup('popRadius')">Cancel</button><button class="btn-submit btn-red" onclick="startRadiusMode()">Pick Center on Map</button></div>
  </div>
</div>

<div class="mp-overlay" id="popRoad">
  <div class="mp-box">
    <div class="mp-head"><div class="mp-title">🛣️ Set Road Status</div><button class="mp-close" onclick="closePopup('popRoad')">✕</button></div>
    <div class="mp-tip tip-blue">Search a street name → it finds it on the real map → mark it closed/warning/open. Or draw the road manually.</div>
    <div class="mp-field">
      <label class="mp-label">Search Street Name</label>
      <input class="mp-input" id="roadSearch" placeholder="e.g. Douaa Street, Tyre" oninput="searchStreet(this.value)">
      <div class="street-results" id="streetResults"></div>
    </div>
    <div class="mp-field"><label class="mp-label">Road Label</label><input class="mp-input" id="roadName" placeholder="e.g. Street Douaa — Tyre center"></div>
    <div class="mp-field"><label class="mp-label">Status</label>
      <select class="mp-input" id="roadStatus"><option value="closed">🔴 Closed — Do not enter</option><option value="warning">🟡 Warning — Proceed with caution</option><option value="open">🟢 Open — Normal passage</option></select></div>
    <div class="mp-field"><label class="mp-label">Reason</label><input class="mp-input" id="roadReason" placeholder="e.g. Military checkpoint, debris, flooding..."></div>
    <div class="mp-tip tip-yellow" id="roadSelTip" style="display:none">✅ Road geometry loaded from map — ready to save.</div>
    <div class="mp-foot">
      <button class="btn-cancel" onclick="closePopup('popRoad')">Cancel</button>
      <button class="btn-submit btn-red" onclick="confirmRoad()">Save Status</button>
    </div>
  </div>
</div>

<div class="mp-overlay" id="popRouteFinder">
  <div class="mp-box">
    <div class="mp-head"><div class="mp-title">🧭 Route Finder</div><button class="mp-close" onclick="closePopup('popRouteFinder')">✕</button></div>
    <div class="mp-tip tip-blue">Finds the safest route and highlights closed/warning roads that may affect it.</div>
    <div class="mp-field"><label class="mp-label">From</label><input class="mp-input" id="rfFromPop" placeholder="e.g. Tyre, Bint Jbeil..."></div>
    <div class="mp-field"><label class="mp-label">To</label><input class="mp-input" id="rfToPop" placeholder="e.g. Saida, Beirut..."></div>
    <div class="mp-field"><label class="mp-label">Avoid</label>
      <select class="mp-input" id="rfAvoid"><option value="closed">Closed roads only</option><option value="both">Closed + Warning roads</option><option value="none">No avoidance (fastest)</option></select></div>
    <div class="mp-foot"><button class="btn-cancel" onclick="closePopup('popRouteFinder')">Cancel</button><button class="btn-submit btn-purple" onclick="findRoutePopup()">Find Route</button></div>
  </div>
</div>

<div class="map-toast" id="mapToast"></div>

<?php include('includes/script.php'); ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var alertsData=<?php echo json_encode($alerts, JSON_UNESCAPED_UNICODE); ?>;
var warnZones=<?php echo json_encode($warns,  JSON_UNESCAPED_UNICODE); ?>;
var safeZones=<?php echo json_encode($safes,  JSON_UNESCAPED_UNICODE); ?>;
var roadsData=<?php echo json_encode($roads,  JSON_UNESCAPED_UNICODE); ?>;
var viewAllMode = <?php echo ($viewAll || $archiveMode) ? 'true' : 'false'; ?>;
// map
var map=L.map('map',{zoomControl:true,doubleClickZoom:false});
var lb=L.latLngBounds([33.05,35.10],[34.70,36.65]);
map.fitBounds(lb,{padding:[20,20]});
var tileVoyager=L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',{attribution:'© OSM © CARTO',subdomains:'abcd',maxZoom:20}).addTo(map);
var tileSat=L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',{maxZoom:19});
var lgA=L.layerGroup().addTo(map);
var lgW=L.layerGroup().addTo(map);
var lgS=L.layerGroup().addTo(map);
var lgR=L.layerGroup().addTo(map);
var lgB=L.layerGroup().addTo(map);
var lgRoute=L.layerGroup().addTo(map);
map.on('mousemove',function(e){document.getElementById('coordTxt').textContent=e.latlng.lat.toFixed(5)+'N  '+e.latlng.lng.toFixed(5)+'E';});

function mkI(cls){var em=cls==='alert-pin'?'⚠️':cls==='warn-pin'?'⚡':'🛡️';return L.divIcon({html:'<div class="'+cls+'">'+em+'</div>',iconSize:[32,32],iconAnchor:[16,16],popupAnchor:[0,-18],className:''});}
function sBadge(s){return s==='high'?'<span class="pop-badge pb-r">High Risk</span>':s==='medium'?'<span class="pop-badge pb-y">Medium Risk</span>':'<span class="pop-badge pb-g">Low Risk</span>';}
function mkPopup(icon, title, rows, badgeClass, badgeLabel, deleteAction) {
  var rowsHtml = rows.map(function(r) {
    return '<div style="display:flex;justify-content:space-between;font-size:12px;color:#475569;margin-bottom:4px">'+
           '<span>'+r[0]+'</span><span style="font-weight:600">'+r[1]+'</span></div>';
  }).join('');
  return '<div style="min-width:200px;font-family:inherit">'+
    '<div style="font-weight:700;font-size:14px;margin-bottom:8px;color:#0f172a">'+icon+' '+title+'</div>'+
    rowsHtml+
    '<span class="pop-badge '+badgeClass+'">'+badgeLabel+'</span>'+
    (!viewAllMode ?
      '<div style="border-top:1px solid #e2e8f0;margin-top:10px;padding-top:10px">'+
      '<button onclick="'+deleteAction+'" style="width:100%;padding:6px;background:#fdecea;color:#e53935;border:1.5px solid rgba(229,57,53,.3);border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit">'+
      '🗑️ Delete</button></div>'
    : '') +
    '</div>';
}

function renderAlerts(d) {
  lgA.clearLayers();
  d.forEach(function(a) {
    var sev = a.severity==='high' ? ['pb-r','High Risk'] : a.severity==='medium' ? ['pb-y','Medium Risk'] : ['pb-g','Low Risk'];
    var popup = mkPopup('⚠️', a.title,
      [['📍 Region', a.region], ['ℹ️ Info', a.desc]],
      sev[0], sev[1], 'deleteAlert('+a.id+')'
    );
    L.marker([a.lat,a.lng],{icon:mkI('alert-pin')}).addTo(lgA).bindPopup(popup);
  });
  upd('alerts', d.length);
}

function renderWarn(d) {
  lgW.clearLayers();
  d.forEach(function(z) {
    var popup = mkPopup('⚡', z.name,
      [['📏 Radius', z.radius+'m'], ['📍 Region', z.region||'—']],
      'pb-y', 'Warning Zone', 'deleteZone('+z.id+')'
    );
    L.circle(z.center,{radius:z.radius,color:'#f59e0b',fillColor:'#f59e0b',fillOpacity:.15,weight:2.5,dashArray:'8,5'})
      .addTo(lgW).bindPopup(popup);
    if(z.radius>0)
      L.marker(z.center,{icon:mkI('warn-pin')}).addTo(lgW).bindPopup(popup); 
  });
  upd('warn', d.length);
}

function renderSafe(d) {
  lgS.clearLayers();
  d.forEach(function(z) {
    var popup = mkPopup('🛡️', z.name,
      [['📏 Radius', z.radius+'m'], ['📍 Region', z.region||'—']],
      'pb-g', 'Safe Zone', 'deleteZone('+z.id+')'
    );
    L.circle(z.center,{radius:z.radius,color:'#2e7d32',fillColor:'#2e7d32',fillOpacity:.15,weight:2,dashArray:'8,5'})
      .addTo(lgS).bindPopup(popup);
    if(z.radius>0)
      L.marker(z.center,{icon:mkI('safe-pin')}).addTo(lgS).bindPopup(popup);
  });
  upd('safe', d.length);
}

function renderRoads(d) {
  lgR.clearLayers();
  d.forEach(function(r) {
    var col = rColors[r.status]||'#64748b';
    var opts = {color:col,weight:6,opacity:.9};
    if(r.status==='closed')  opts.dashArray='10,6';
    else if(r.status==='warning') opts.dashArray='14,4';
    var bc = r.status==='open'?'pb-g':r.status==='warning'?'pb-y':'pb-r';
    var bl = r.status.charAt(0).toUpperCase()+r.status.slice(1);
    var popup = mkPopup('🛣️', r.name,
      [['Status', r.status.toUpperCase()], ['Reason', r.reason]],
      bc, bl, 'deleteRoad('+r.id+')'
    );
    L.polyline(r.points,opts).addTo(lgR).bindPopup(popup);
    var mid = r.points[Math.floor(r.points.length/2)];
    L.circleMarker(mid,{radius:5,fillColor:col,color:'#fff',weight:2,fillOpacity:1}).addTo(lgR);
  });
  upd('roads', d.length);
  // renderRoadList(d);
}

function deleteAlert(id) {
  Swal.fire({title:'Delete alert?', text:'This will be permanently removed.', icon:'warning',
    showCancelButton:true, confirmButtonColor:'#e53935', cancelButtonColor:'#64748b', confirmButtonText:'Yes, delete'
  }).then(function(res) {
    if(!res.isConfirmed) return;
    $.ajax({
      url: 'actions/map_ajax.php',
      type: 'POST',
      data: {action:'delete_alert', id:id},
      dataType: 'json',                  
      success: function(r) {
        if(r.status === 'success') {
          Swal.fire({icon:'success', title:'Deleted', timer:1500, showConfirmButton:false})
            .then(function(){ window.location.reload(); });
        } else {
          Swal.fire({icon:'error', title:'Error', text:r.message});
        }
      }
    });
  });
}

function deleteZone(id) {
  Swal.fire({title:'Delete zone?', text:'This will be permanently removed.', icon:'warning',
    showCancelButton:true, confirmButtonColor:'#e53935', cancelButtonColor:'#64748b', confirmButtonText:'Yes, delete'
  }).then(function(res) {
    if(!res.isConfirmed) return;
    $.ajax({
      url: 'actions/map_ajax.php',
      type: 'POST',
      data: {action:'delete_zone', id:id},
      dataType: 'json',
      success: function(r) {
        if(r.status === 'success') {
          Swal.fire({icon:'success', title:'Deleted', timer:1500, showConfirmButton:false})
            .then(function(){ window.location.reload(); });
        } else {
          Swal.fire({icon:'error', title:'Error', text:r.message});
        }
      }
    });
  });
}

function deleteRoad(id) {
  Swal.fire({title:'Delete road?',text:'This will be permanently removed.',icon:'warning',
    showCancelButton:true,confirmButtonColor:'#e53935',cancelButtonColor:'#64748b',confirmButtonText:'Yes, delete'
  }).then(function(res) {
    if(!res.isConfirmed) return;

    $.ajax({
      url:'actions/map_ajax.php',type:'POST',data:{action:'delete_road',id:id},dataType:'json', 
      success:function(r) {
        if(r.status === 'success'){
        Swal.fire({icon:'success', title:'Deleted', timer:1500, showConfirmButton:false})
  .then(function(){ window.location.reload(); });
}else{
       Swal.fire({icon:'error',title:'Error',text:r.message});
    }
  }
  });
});
}
var rColors={open:'#2e7d32',warning:'#f59e0b',closed:'#e53935'};
function renderRoadList(d){
  var el=document.getElementById('roadList');
  if(!d.length){el.innerHTML='<p style="font-size:12px;color:var(--text3)">No road statuses yet.</p>';return;}
  el.innerHTML=d.map(function(r){
    var bc=r.status==='open'?'rb-open':r.status==='warning'?'rb-warn':'rb-closed';
    return '<div class="road-item"><div><div class="road-name">'+r.name+'</div><div class="road-detail">'+r.reason+'</div></div><span class="road-badge '+bc+'">'+r.status+'</span></div>';
  }).join('');
}
function upd(n,v){
  var a=document.getElementById('lc-'+n);if(a)a.textContent=v;
}

renderAlerts(alertsData);renderWarn(warnZones);renderSafe(safeZones);renderRoads(roadsData);

fetch('https://raw.githubusercontent.com/datasets/geo-countries/master/data/countries.geojson')
  .then(function(r){return r.json();}).then(function(d){
    var f=d.features.filter(function(x){return x.properties.ISO_A2==='LB';});
    if(f.length) L.geoJSON(f,{style:{color:'#334155',weight:2,fillOpacity:0}}).addTo(lgB);
  }).catch(function(){});

var lgMap={alerts:lgA,warn:lgW,safe:lgS,roads:lgR,borders:lgB};
function toggleLayer(n,btn){var lg=lgMap[n];if(!lg)return;var on=btn.classList.toggle('on');if(on)map.addLayer(lg);else map.removeLayer(lg);var fc=document.getElementById('fc-'+n);if(fc)fc.checked=on;}
function switchBase(btn){var on=btn.classList.toggle('on');if(on)map.addLayer(tileSat);else map.removeLayer(tileSat);}
// ['alerts','warn','safe','roads'].forEach(function(n){document.getElementById('fc-'+n).addEventListener('change',function(e){var lg=lgMap[n];if(lg){if(e.target.checked)map.addLayer(lg);else map.removeLayer(lg);}});});

var rv={beirut:[33.894,35.502,12],south:[33.27,35.20,11],bekaa:[33.85,36.10,10],mount:[33.83,35.66,11],north:[34.42,35.85,11]};
document.getElementById('regionFilter').addEventListener('change',function(){var v=this.value;if(v==='all'){map.fitBounds(lb);}else{var r=rv[v];if(r)map.setView([r[0],r[1]],r[2]);}});
var cities={beirut:[33.894,35.502,12],sidon:[33.557,35.372,13],saida:[33.557,35.372,13],tyre:[33.272,35.204,13],sur:[33.272,35.204,13],tripoli:[34.437,35.849,12],zahle:[33.846,35.902,13],jounieh:[33.981,35.618,13],baalbek:[34.004,36.219,12],nabatieh:[33.377,35.484,13],'bint jbeil':[33.117,35.434,13],'bint jbail':[33.117,35.434,13]};
document.getElementById('locSearch').addEventListener('keyup',function(e){
  if(e.key!=='Enter')return;
  var q=this.value.toLowerCase().trim();if(!q)return;
  for(var k in cities){if(k.includes(q)||q.includes(k)){var v=cities[k];map.setView([v[0],v[1]],v[2]);showToast('📍 '+k.charAt(0).toUpperCase()+k.slice(1));return;}}
  fetch('https://nominatim.openstreetmap.org/search?format=json&q='+encodeURIComponent(this.value+' Lebanon')+'&limit=1')
    .then(function(r){return r.json();}).then(function(d){if(d&&d.length){map.setView([parseFloat(d[0].lat),parseFloat(d[0].lon)],15);showToast('📍 '+d[0].display_name.split(',')[0]);}else showToast('Not found');}).catch(function(){showToast('Search error');});
});
document.getElementById('btnClear').addEventListener('click',function(){document.getElementById('regionFilter').value='all';document.getElementById('locSearch').value='';map.fitBounds(lb);showToast('View reset');});

var curMode=null,pendLat=null,pendLng=null,drawPts=[],drawPrev=null,pendRoadPts=null;
var rzPend={name:'',type:'danger',radius:300};

function startMode(m){
  curMode=m;
  var txt={alert:'⚠️ Click exact location on map to place alert pin',warnzone:'⚡ Click points on map to draw WARNING zone polygon — double-click to finish',safezone:'🛡️ Click points on map to draw SAFE zone polygon — double-click to finish',radius:'🎯 Click the CENTER point on the map',road:'🛣️ Click points along the road — double-click to finish'};
  document.getElementById('modeText').textContent=txt[m]||m;
  document.getElementById('modeBanner').classList.add('show');
  document.getElementById('modeInd').textContent='● '+m+' mode';
  map.getContainer().style.cursor='crosshair';
  drawPts=[];if(drawPrev){map.removeLayer(drawPrev);drawPrev=null;}
}
function cancelMode(){curMode=null;document.getElementById('modeBanner').classList.remove('show');document.getElementById('modeInd').textContent='';map.getContainer().style.cursor='';drawPts=[];if(drawPrev){map.removeLayer(drawPrev);drawPrev=null;}}

map.on('click',function(e){
  if(!curMode)return;
  var lat=e.latlng.lat,lng=e.latlng.lng;
  if(curMode==='alert'){
  pendLat=lat; pendLng=lng;
  document.getElementById('aCoords').value=lat.toFixed(5)+', '+lng.toFixed(5);
  document.getElementById('aPickTip').style.display='none';
  document.getElementById('aSaveTip').style.display='block';
  document.getElementById('btnSaveAlert').style.display='inline-block';
  cancelMode();
  openPopup('popAlert');
}
else if(curMode==='radius'){
  var n=rzPend.name, rad=rzPend.radius, typ=rzPend.type, reg=rzPend.region||'unknown';
  cancelMode();

  Swal.fire({
    title: 'Saving...',
    text: 'Please wait.',
    allowOutsideClick: false,
    showConfirmButton: false,
    didOpen: function(){ Swal.showLoading(); }
  });

  $.ajax({
    url: 'actions/map_ajax.php',
    type: 'POST',
    data: {action:'add_zone', name:n, type:typ, center_lat:lat, center_lng:lng, radius_meters:rad, region:reg},
    dataType: 'json',
    success: function(r){
      if(r.status==='success'){
        Swal.fire({icon:'success', title:'Zone Saved!', timer:1500, showConfirmButton:false})
          .then(function(){ window.location.reload(); });
      } else {
        Swal.fire({icon:'error', title:'Error', text:r.message});
      }
    }
  });
}
  else if(curMode==='warnzone'||curMode==='safezone'||curMode==='road'){
    drawPts.push([lat,lng]);
    if(drawPrev)map.removeLayer(drawPrev);
    var col=curMode==='warnzone'?'#f59e0b':curMode==='safezone'?'#2e7d32':'#e53935';
    if(curMode==='road'&&drawPts.length>1) drawPrev=L.polyline(drawPts,{color:col,weight:5,dashArray:'8,5',opacity:.7}).addTo(map);
    else if(curMode!=='road'&&drawPts.length>2) drawPrev=L.polygon(drawPts,{color:col,fillColor:col,fillOpacity:.15,weight:2,dashArray:'8,5'}).addTo(map);
    L.circleMarker([lat,lng],{radius:4,fillColor:col,color:'#fff',weight:2,fillOpacity:1}).addTo(map);
  }
});

map.on('dblclick',function(e){
  if(curMode==='warnzone'&&drawPts.length>=3){
    var n=prompt('Name for this Warning Zone:','Warning Zone '+(warnZones.length+1));if(!n){cancelMode();return;}
    L.polygon(drawPts.slice(),{color:'#f59e0b',fillColor:'#f59e0b',fillOpacity:.15,weight:2.5,dashArray:'8,5'}).addTo(lgW).bindPopup('<div class="pop-title">⚡ '+n+'</div><span class="pop-badge pb-y">Warning Zone</span>');
    warnZones.push({id:warnZones.length+1,name:n,center:drawPts[0],radius:0,region:'unknown'});upd('warn',warnZones.length);
    cancelMode();showToast('⚡ Warning zone added!');
  }
  else if(curMode==='safezone'&&drawPts.length>=3){
    var n=prompt('Name for this Safe Zone:','Safe Zone '+(safeZones.length+1));if(!n){cancelMode();return;}
    L.polygon(drawPts.slice(),{color:'#2e7d32',fillColor:'#2e7d32',fillOpacity:.15,weight:2,dashArray:'8,5'}).addTo(lgS).bindPopup('<div class="pop-title">🛡️ '+n+'</div><span class="pop-badge pb-g">Safe Zone</span>');
    safeZones.push({id:safeZones.length+1,name:n,center:drawPts[0],radius:0,region:'unknown'});upd('safe',safeZones.length);
    cancelMode();showToast('🛡️ Safe zone added!');
  }
  else if(curMode==='road'&&drawPts.length>=2){
    pendRoadPts=drawPts.slice();cancelMode();
    document.getElementById('roadSelTip').style.display='block';
    openPopup('popRoad');
  }
});

function startRadiusMode(){
  rzPend.name   = document.getElementById('rzName').value || 'Zone';
  rzPend.type   = document.getElementById('rzType').value;
  rzPend.radius = parseInt(document.getElementById('rzRadius').value);
  rzPend.region = document.getElementById('rzRegion').value;
  closePopup('popRadius');
  startMode('radius');
}
function startAlertMode(){
  var t=document.getElementById('aTitle').value.trim();
  if(!t){ showToast('Enter a title first'); return; }
  closePopup('popAlert');
  startMode('alert');
}

function confirmAlert(){
  if(!pendLat){ showToast('Pick a location first'); return; }
  var t=document.getElementById('aTitle').value||'New Alert';
  var s=document.getElementById('aSev').value;
  var d=document.getElementById('aDesc').value||'No description';
  var reg=document.getElementById('aRegion').value;

  closePopup('popAlert');

  Swal.fire({
    title: 'Saving...',
    text: 'Please wait.',
    allowOutsideClick: false,
    showConfirmButton: false,
    didOpen: function(){ Swal.showLoading(); }
  });

  $.ajax({
    url: 'actions/map_ajax.php',
    type: 'POST',
    data: {action:'add_alert', title:t, severity:s, description:d, lat:pendLat, lng:pendLng, region:reg},
    dataType: 'json',
    success: function(r){
      if(r.status==='success'){
        Swal.fire({icon:'success', title:'Alert Saved!', timer:1500, showConfirmButton:false})
          .then(function(){ window.location.reload(); });
      } else {
        Swal.fire({icon:'error', title:'Error', text:r.message});
      }
    }
  });
}

var srTimer=null,srResults=[];
function searchStreet(v){
  clearTimeout(srTimer);
  if(v.length<3){document.getElementById('streetResults').classList.remove('show');return;}
  srTimer=setTimeout(function(){
    fetch('https://nominatim.openstreetmap.org/search?format=json&q='+encodeURIComponent(v+' Lebanon')+'&limit=7&polygon_geojson=1')
      .then(function(r){return r.json();}).then(function(res){
        srResults=res||[];
        var el=document.getElementById('streetResults');
        if(!srResults.length){el.innerHTML='<div class="sr-item" style="color:var(--text3)">No results</div>';el.classList.add('show');return;}
        el.innerHTML=srResults.map(function(r,i){return '<div class="sr-item" onclick="selectStreet('+i+')"><div>'+r.display_name.split(',')[0]+'</div><div class="sr-sub">'+r.display_name.split(',').slice(1,3).join(',')+'</div></div>';}).join('');
        el.classList.add('show');
      }).catch(function(){});
  },400);
}
function selectStreet(i){
  var r=srResults[i];
  document.getElementById('streetResults').classList.remove('show');
  document.getElementById('roadName').value=r.display_name.split(',')[0];
  document.getElementById('roadSelTip').style.display='block';
  if(r.geojson&&(r.geojson.type==='LineString'||r.geojson.type==='MultiLineString')){
    var lyr=L.geoJSON(r.geojson,{style:{color:'#1d6ef5',weight:5,opacity:.6,dashArray:'8,5'}}).addTo(map);
    try{map.fitBounds(lyr.getBounds(),{padding:[30,30]});}catch(ex){}
    if(r.geojson.type==='LineString') pendRoadPts=r.geojson.coordinates.map(function(c){return[c[1],c[0]];});
    else pendRoadPts=r.geojson.coordinates[0].map(function(c){return[c[1],c[0]];});
    showToast('Street found on map');
  } else {
    map.setView([parseFloat(r.lat),parseFloat(r.lon)],16);
    pendRoadPts=[[parseFloat(r.lat)-0.001,parseFloat(r.lon)],[parseFloat(r.lat)+0.001,parseFloat(r.lon)]];
    showToast('Location found — draw the road or use this point');
  }
}
function startRoadDraw(){closePopup('popRoad');startMode('road');}
function confirmRoad(){
  var n=document.getElementById('roadName').value||'Unnamed Road';
  var st=document.getElementById('roadStatus').value;
  var rs=document.getElementById('roadReason').value||'No reason given';
  if(!pendRoadPts||pendRoadPts.length<2){showToast('Search a road or draw it on the map first');return;}
   closePopup('popRoad');
  Swal.fire({
    title:'Saving...',text:'Please wait.',
    allowOutsideClick:false,showConfirmButton:false,
    didOpen:function(){Swal.showLoading();}
  });
  $.ajax({
    url:'actions/map_ajax.php',
    type:'POST',
    data:{action:'add_road', name:n, status:st, reason:rs, route_points:JSON.stringify(pendRoadPts)},
    dataType:'json',
    success:function(r){
      if(r.status==='success'){
        Swal.fire({icon:'success',title:'Road Saved!',timer:1500,showConfirmButton:false})
          .then(function(){ window.location.reload(); });
      } else {
        Swal.fire({icon:'error',title:'Error',text:r.message});
      }
    }
  });
}
function geocode(q){
  for(var k in cities){if(k.includes(q.toLowerCase())||q.toLowerCase().includes(k))return Promise.resolve([cities[k][0],cities[k][1]]);}
  return fetch('https://nominatim.openstreetmap.org/search?format=json&q='+encodeURIComponent(q+' Lebanon')+'&limit=1').then(function(r){return r.json();}).then(function(d){return d&&d.length?[parseFloat(d[0].lat),parseFloat(d[0].lon)]:null;});
}
function findRoute(){var f=document.getElementById('rfFrom').value.trim(),t=document.getElementById('rfTo').value.trim();if(!f||!t){showToast('Enter From and To');return;}calcRoute(f,t,'closed');}
function findRoutePopup(){var f=document.getElementById('rfFromPop').value.trim(),t=document.getElementById('rfToPop').value.trim(),av=document.getElementById('rfAvoid').value;if(!f||!t){showToast('Enter both locations');return;}closePopup('popRouteFinder');calcRoute(f,t,av);}
function calcRoute(from,to,avoid){
  var res=document.getElementById('rfResult');res.className='rf-result show';res.innerHTML='⏳ Calculating...';
  lgRoute.clearLayers();
  Promise.all([geocode(from),geocode(to)]).then(function(pts){
    var a=pts[0],b=pts[1];
    if(!a||!b){res.innerHTML='❌ Could not locate one of the places.';return;}
    var line=L.polyline([a,b],{color:'#7c3aed',weight:5,opacity:.9,dashArray:'12,6'}).addTo(lgRoute);
    try{map.fitBounds(line.getBounds(),{padding:[50,50]});}catch(ex){}
    L.circleMarker(a,{radius:8,fillColor:'#7c3aed',color:'#fff',weight:3,fillOpacity:1}).addTo(lgRoute).bindPopup('<b>From: '+from+'</b>');
    L.circleMarker(b,{radius:8,fillColor:'#2e7d32',color:'#fff',weight:3,fillOpacity:1}).addTo(lgRoute).bindPopup('<b>To: '+to+'</b>');
    var dist=(map.distance(a,b)/1000).toFixed(1);
    var closed=roadsData.filter(function(r){if(avoid==='none')return false;if(avoid==='closed')return r.status==='closed';return r.status==='closed'||r.status==='warning';});
    var warnMsg=closed.length?'<br>⚠️ '+closed.length+' road(s) to avoid:<br>'+closed.map(function(r){return '• '+r.name+' ('+r.status+')'}).join('<br>'):'<br>🟢 No closures found on this corridor';
    res.innerHTML='✅ <b>'+from+' → '+to+'</b><br>📏 ~'+dist+' km straight line'+warnMsg+'<br><small style="opacity:.7">Tip: for real road routing upgrade to OpenRouteService API</small>';
    showToast('Route shown — '+dist+' km');
  }).catch(function(){res.innerHTML='❌ Route calculation failed';});
}

function clearAll(){if(!confirm('Reset to default data?'))return;alertsData=alertsData.slice(0,8);warnZones=warnZones.slice(0,2);safeZones=safeZones.slice(0,4);roadsData=roadsData.slice(0,5);lgRoute.clearLayers();renderAlerts(alertsData);renderWarn(warnZones);renderSafe(safeZones);renderRoads(roadsData);document.getElementById('rfResult').classList.remove('show');showToast('Map reset');}

function openPopup(id){document.getElementById(id).classList.add('show');}
function closePopup(id){document.getElementById(id).classList.remove('show');}
document.querySelectorAll('.mp-overlay').forEach(function(el){el.addEventListener('click',function(e){if(e.target===el)el.classList.remove('show');});});

function showToast(m){var t=document.getElementById('mapToast');t.textContent=m;t.classList.add('show');clearTimeout(t._t);t._t=setTimeout(function(){t.classList.remove('show');},2800);}
function setIncident(id) {
    if(!id || id == 0) {
        $.ajax({
            url: 'actions/map_ajax.php',
            type: 'POST',
            data: {action: 'set_incident', incident_id: 0},
            dataType: 'json',
            success: function(){ window.location.reload(); }
        });
        return;
    }
    $.ajax({
        url: 'actions/map_ajax.php',
        type: 'POST',
        data: {action: 'set_incident', incident_id: id},
        dataType: 'json',
        success: function(r) {
            if(r.status === 'success') {
                window.location.reload();
            }
        }
    });
}
function setViewAll() {
    $.ajax({
        url: 'actions/map_ajax.php',
        type: 'POST',
        data: {action: 'set_view_all', value: 1},
        dataType: 'json',
        success: function(){ window.location.reload(); }
    });
}

function exitViewAll() {
    $.ajax({
        url: 'actions/map_ajax.php',
        type: 'POST',
        data: {action: 'set_view_all', value: 0},
        dataType: 'json',
        success: function(){ window.location.reload(); }
    });
}

<?php if($isResolvedSession): ?>
Swal.fire({
    title: '📋 Incident Resolved',
    html: 'The incident you were working on has been <b>resolved</b>.<br>What would you like to do?',
    icon: 'info',
    showCancelButton: true,
    confirmButtonText: '📦 View Archive',
    cancelButtonText: '🗺️ Select New Incident',
    confirmButtonColor: '#7c3aed',
    cancelButtonColor: '#64748b'
}).then(function(res) {
    if(res.isConfirmed) {
        $.ajax({
            url: 'actions/map_ajax.php',
            type: 'POST',
            data: {action: 'set_archive', incident_id: <?php echo $activeIncidentId; ?>},
            dataType: 'json',
            success: function(){ window.location.reload(); }
        });
    } else {
        $.ajax({
            url: 'actions/map_ajax.php',
            type: 'POST',
            data: {action: 'set_incident', incident_id: 0},
            dataType: 'json',
            success: function(){ window.location.reload(); }
        });
    }
});
<?php endif; ?>

var policeRoadsData = <?php echo json_encode($policeRoadsData, JSON_UNESCAPED_UNICODE); ?>;
var evacRoutesData  = <?php echo json_encode($evacRoutesData,  JSON_UNESCAPED_UNICODE); ?>;

var lgPR = L.layerGroup();
var lgER = L.layerGroup(); 

// Add to lgMap so toggleLayer() works
lgMap['policeroads'] = lgPR;
lgMap['evacroutes']  = lgER;

// Police road colors: blocked=orange, warning=yellow, safe=green
var prColors = {blocked:'#cb0202', warning:'#f59e0b', safe:'#2e7d32'};

function renderPoliceRoads(d) {
    lgPR.clearLayers();
    d.forEach(function(r) {
        if (!r.points || r.points.length < 2) return;
        var col = prColors[r.status] || '#f97316';
        var opts = {color: col, weight: 5, opacity: 0.85, dashArray: '6,4'};
        var popup = '<div style="min-width:190px;font-family:inherit">' +
            '<div style="font-weight:700;font-size:14px;margin-bottom:8px;color:#0f172a">🚔 ' + r.name + '</div>' +
            '<div style="font-size:12px;color:#475569;margin-bottom:4px"><span>Status</span> <span style="font-weight:600">' + r.status.toUpperCase() + '</span></div>' +
            '<div style="font-size:12px;color:#475569;margin-bottom:4px"><span>Reason</span> <span style="font-weight:600">' + r.reason + '</span></div>' +
            '<span style="display:inline-block;padding:2px 8px;border-radius:5px;font-size:11px;font-weight:700;background:var(--purple-bg);color:var(--purple)">Police Road</span>' +
            '</div>';
        L.polyline(r.points, opts).addTo(lgPR).bindPopup(popup);
        var mid = r.points[Math.floor(r.points.length / 2)];
        L.circleMarker(mid, {radius:5, fillColor:col, color:'#fff', weight:2, fillOpacity:1}).addTo(lgPR);
    });
    document.getElementById('lc-policeroads').textContent = d.length;
}

function renderEvacRoutes(d) {
    lgER.clearLayers();
    d.forEach(function(r) {
        if (!r.points || r.points.length < 2) return;
        var col = r.status === 'closed' ? '#e53935' : r.status === 'warning' ? '#f59e0b' : '#7c3aed';
        var popup = '<div style="min-width:190px;font-family:inherit">' +
            '<div style="font-weight:700;font-size:14px;margin-bottom:8px;color:#0f172a">🟣 ' + r.name + '</div>' +
            '<div style="font-size:12px;color:#475569;margin-bottom:4px"><span>Status</span> <span style="font-weight:600">' + r.status.toUpperCase() + '</span></div>' +
            '<div style="font-size:12px;color:#475569;margin-bottom:4px"><span>Notes</span> <span style="font-weight:600">' + (r.notes || '—') + '</span></div>' +
            '<span style="display:inline-block;padding:2px 8px;border-radius:5px;font-size:11px;font-weight:700;background:var(--purple-bg);color:var(--purple)">Evacuation Route</span>' +
            '</div>';
        L.polyline(r.points, {color: col, weight: 6, opacity: 0.9, dashArray: '14,5'}).addTo(lgER).bindPopup(popup);
        // Start marker
        L.circleMarker(r.points[0], {radius:7, fillColor:'#7c3aed', color:'#fff', weight:2, fillOpacity:1})
            .addTo(lgER).bindPopup('<b>Start: ' + r.name.split(' → ')[0] + '</b>');
        // End marker
        var last = r.points[r.points.length - 1];
        L.circleMarker(last, {radius:7, fillColor:'#2e7d32', color:'#fff', weight:2, fillOpacity:1})
            .addTo(lgER).bindPopup('<b>End: ' + r.name.split(' → ')[1] + '</b>');
    });
    document.getElementById('lc-evacroutes').textContent = d.length;
}

// Render them
renderPoliceRoads(policeRoadsData);
renderEvacRoutes(evacRoutesData);

var routesVisible = false;
function toggleRoutesLayer() {
    routesVisible = !routesVisible;
    var btn = document.getElementById('btnViewRoutes');
    if (routesVisible) {
        map.addLayer(lgPR);
        map.addLayer(lgER);
        document.getElementById('lt-policeroads').classList.add('on');
        document.getElementById('lt-evacroutes').classList.add('on');
        btn.style.borderColor = 'var(--green)';
        btn.style.color       = 'var(--green)';
        btn.textContent       = '✓ Routes ON';
        showToast('🚦 Police roads & evac routes shown');
    } else {
        map.removeLayer(lgPR);
        map.removeLayer(lgER);
        document.getElementById('lt-policeroads').classList.remove('on');
        document.getElementById('lt-evacroutes').classList.remove('on');
        btn.style.borderColor = 'var(--purple)';
        btn.style.color       = 'var(--purple)';
        btn.textContent       = '🚦 View Routes';
        showToast('Routes hidden');
    }
}
function toggleLegend() {
  var box = document.getElementById('legendBox');
  box.style.display = box.style.display === 'none' ? 'block' : 'none';
}
</script>

<script>
var aiPendingData = null;
 
// Fill textarea with example text
function aiExample(text) {
    document.getElementById('aiInput').value = text;
    document.getElementById('aiInput').focus();
}
 
// Send text to ai_map.php
function sendToAI() {
    var text = document.getElementById('aiInput').value.trim();
    if (!text) { showToast('Type something first'); return; }
 
    // Reset UI
    document.getElementById('aiConfirm').classList.remove('show');
    document.getElementById('aiResultMsg').classList.remove('show');
    document.getElementById('aiThinking').classList.add('show');
    document.getElementById('aiSendBtn').disabled = true;
    aiPendingData = null;
 
    // Get current incident ID from session 
    var incidentId = <?php echo $activeIncidentId ?? 0; ?>;
 
    $.ajax({
        url: 'actions/ai_map.php',
        type: 'POST',
        data: { text: text, incident_id: incidentId },
        dataType: 'json',
        timeout: 30000,  // 30 seconds max (Gemini + Nominatim can be slow)
        success: function(r) {
            document.getElementById('aiThinking').classList.remove('show');
            document.getElementById('aiSendBtn').disabled = false;
 
            if (r.status !== 'success') {
                showAIResult('error', '❌ ' + r.message);
                return;
            }
 
            // Store the response for when user confirms
            aiPendingData = r;
 
            // Build the location list HTML
            var locHtml = '';
            var foundCount = 0;
            r.locations.forEach(function(loc) {
                if (loc.found) foundCount++;
                locHtml += '<div class="ai-loc-item">' +
                    '<div class="ai-loc-dot ' + (loc.found ? 'found' : 'notfound') + '"></div>' +
                    '<span class="ai-loc-name">' + loc.name + '</span>' +
                    (loc.found
                        ? '<span class="ai-loc-coords">' + loc.lat.toFixed(4) + ', ' + loc.lng.toFixed(4) + '</span>'
                        : '<span class="ai-loc-coords" style="color:#f87171">not found</span>'
                    ) +
                    '</div>';
            });
 
            // Build the action description
            var actionIcon = r.action === 'alert' ? '⚠️' : r.action === 'zone' ? '🎯' : '🛣️';
            var actionLabel = r.action === 'alert'
                ? 'Alert pin — severity: ' + r.severity.toUpperCase()
                : r.action === 'zone'
                ? 'Zone — type: ' + r.zone_type.toUpperCase()
                : 'Road — status: ' + r.road_status.toUpperCase();
 
            var actionInfo = actionIcon + ' Action: ' + actionLabel + '\n' +
                '📝 Description: ' + r.description + '\n' +
                '📍 Region: ' + r.region + '\n' +
                (foundCount < r.locations.length
                    ? '⚠️ ' + (r.locations.length - foundCount) + ' location(s) not found on map'
                    : '✅ All ' + foundCount + ' location(s) found');
 
            document.getElementById('aiConfirmTitle').textContent = 
                '📍 I found ' + foundCount + ' of ' + r.locations.length + ' location(s):';
            document.getElementById('aiLocList').innerHTML = locHtml;
            document.getElementById('aiActionInfo').innerText = actionInfo;
            document.getElementById('aiConfirm').classList.add('show');
        },
        error: function(xhr, status) {
            document.getElementById('aiThinking').classList.remove('show');
            document.getElementById('aiSendBtn').disabled = false;
            var msg = status === 'timeout'
                ? '⏱️ Request timed out — check your internet connection'
                : '❌ Server error — check that ai_map.php is in the actions/ folder';
            showAIResult('error', msg);
        }
    });
}
 
// User clicked "Yes, Add to Map"
function confirmAIAction() {
    if (!aiPendingData) return;
 
    var r = aiPendingData;
    var locations = r.locations.filter(function(l) { return l.found; });
 
    if (!locations.length) {
        showAIResult('error', '❌ No valid locations to add');
        return;
    }
 
    document.getElementById('aiConfirm').classList.remove('show');
 
    var done = 0;
    var total = locations.length;
    var errors = 0;
 
    // Save each location one by one
    locations.forEach(function(loc) {
        var ajaxData = {};
 
        if (r.action === 'alert') {
            // Add alert pin
            ajaxData = {
                action:      'add_alert',
                title:       loc.name + ' — ' + r.description,
                severity:    r.severity,
                description: r.description,
                lat:         loc.lat,
                lng:         loc.lng,
                region:      r.region
            };
        } else if (r.action === 'zone') {
            // Add zone circle
            ajaxData = {
                action:         'add_zone',
                name:           loc.name + ' — ' + r.zone_type + ' zone',
                type:           r.zone_type,
                center_lat:     loc.lat,
                center_lng:     loc.lng,
                radius_meters:  500, 
                region:         r.region
            };
        } else if (r.action === 'road') {
            var pts = [
                [loc.lat - 0.002, loc.lng],
                [loc.lat + 0.002, loc.lng]
            ];
            ajaxData = {
                action:       'add_road',
                name:         loc.name,
                status:       r.road_status,
                reason:       r.description,
                route_points: JSON.stringify(pts)
            };
        }
 
        $.ajax({
            url:      'actions/map_ajax.php',
            type:     'POST',
            data:     ajaxData,
            dataType: 'json',
            success: function(res) {
                if (res.status !== 'success') errors++;
                done++;
                if (done === total) finishAI(total, errors);
            },
            error: function() {
                errors++;
                done++;
                if (done === total) finishAI(total, errors);
            }
        });
    });
}
 
// Called after all saves are done
function finishAI(total, errors) {
    aiPendingData = null;
    document.getElementById('aiInput').value = '';
    if (errors === 0) {
        showAIResult('success', '✅ ' + total + ' location(s) added to the map successfully!');
        setTimeout(function() { window.location.reload(); }, 1500);
    } else {
        showAIResult('error', '⚠️ ' + (total - errors) + ' saved, ' + errors + ' failed. Refreshing...');
        setTimeout(function() { window.location.reload(); }, 2000);
    }
}
 
// User clicked Cancel
function cancelAIAction() {
    aiPendingData = null;
    document.getElementById('aiConfirm').classList.remove('show');
    showAIResult('error', '↩️ Cancelled — nothing was added');
    setTimeout(function() {
        document.getElementById('aiResultMsg').classList.remove('show');
    }, 2000);
}
 
// Show result message (success or error)
function showAIResult(type, msg) {
    var el = document.getElementById('aiResultMsg');
    el.textContent = msg;
    el.className = 'ai-result-msg show ' + type;
}
</script>
</body>
</html>
