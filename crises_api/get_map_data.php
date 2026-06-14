<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include "db.php";

// ── ALERTS ────────────────────────────────────────────────────────────────
$alerts = [];
$r = $conn->query("
    SELECT id, title, severity, description, lat, lng, region,created_at
    FROM map_alerts 
    WHERE is_active = 1
");
while ($row = $r->fetch_assoc()) {
    $alerts[] = [
        "id"          => $row["id"],
        "title"       => $row["title"],
        "severity"    => $row["severity"],
        "description" => $row["description"],
        "lat"         => (float)$row["lat"],
        "lng"         => (float)$row["lng"],
        "region"      => $row["region"],
        "created_at"   => $row["created_at"]
    ];
}

// ── ZONES ─────────────────────────────────────────────────────────────────
$zones = [];
$r = $conn->query("
    SELECT id, name, type, center_lat, center_lng, radius_meters, polygon_points, region
    FROM map_zones 
    WHERE is_active = 1
");
while ($row = $r->fetch_assoc()) {
    // Only decode polygon_points if it's not null
    $points = null;
    if (!empty($row["polygon_points"])) {
        $points = json_decode($row["polygon_points"]);
    }

    $zones[] = [
        "id"            => $row["id"],
        "name"          => $row["name"],
        "type"          => $row["type"],
        "center_lat"    => (float)$row["center_lat"],
        "center_lng"    => (float)$row["center_lng"],
        "radius_meters" => (float)$row["radius_meters"],
        "polygon_points"=> $points,
        "region"        => $row["region"]
    ];
}

// ── ROADS ─────────────────────────────────────────────────────────────────
$roads = [];
$r = $conn->query("
    SELECT id, name, status, reason, route_points
    FROM map_roads 
    WHERE is_active = 1
");
while ($row = $r->fetch_assoc()) {
    $points = null;
    if (!empty($row["route_points"])) {
        $points = json_decode($row["route_points"]);
    }

    $roads[] = [
        "id"           => $row["id"],
        "name"         => $row["name"],
        "status"       => $row["status"],
        "reason"       => $row["reason"],
        "route_points" => $points
    ];
}

// ── ROUTES ────────────────────────────────────────────────────────────────
$routes = [];
$r = $conn->query("
    SELECT id, from_name, to_name, route_points, region, route_status, notes
    FROM map_routes 
    WHERE is_active = 1
");
while ($row = $r->fetch_assoc()) {
    $points = null;
    if (!empty($row["route_points"])) {
        $points = json_decode($row["route_points"]);
    }

    $routes[] = [
        "id"           => $row["id"],
        "from_name"    => $row["from_name"],
        "to_name"      => $row["to_name"],
        "route_points" => $points,
        "region"       => $row["region"],
        "status"       => $row["route_status"],
        "notes"        => $row["notes"]
    ];
}

echo json_encode([
    "status" => "success",
    "alerts" => $alerts,
    "zones"  => $zones,
    "roads"  => $roads,
    "routes" => $routes
]);

$conn->close();
?>