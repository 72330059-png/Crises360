<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include "db.php";

// INPUT 
$user_lat      = isset($_GET['lat'])    ? floatval($_GET['lat'])    : null;
$user_lng      = isset($_GET['lng'])    ? floatval($_GET['lng'])    : null;
$status_filter = isset($_GET['status']) ? trim($_GET['status'])     : null;

$REC_RADIUS_KM = 15;

// DISTANCE SQL 
$distance_expr = "NULL";
$has_gps       = ($user_lat !== null && $user_lng !== null);

if ($has_gps) {
    $lat = floatval($user_lat);
    $lng = floatval($user_lng);
    $distance_expr = "ROUND(6371 * ACOS(GREATEST(-1, LEAST(1,
        COS(RADIANS($lat)) * COS(RADIANS(s.lat)) *
        COS(RADIANS(s.lng) - RADIANS($lng)) +
        SIN(RADIANS($lat)) * SIN(RADIANS(s.lat))
    ))), 2)";
}

//  STATUS FILTER 
$status_where = "";
if ($status_filter && strtolower($status_filter) !== "all") {
    $sf = strtolower(trim($status_filter));
    if ($sf === "near-full" || $sf === "nearfull" || $sf === "near full") {
       
        $status_where = "AND LOWER(TRIM(s.status)) LIKE '%near%full%'";
    } elseif ($sf === "open") {
        $status_where = "AND LOWER(TRIM(s.status)) = 'open'";
    } elseif ($sf === "full") {
       
        $status_where = "AND LOWER(TRIM(s.status)) = 'full'
                         AND LOWER(TRIM(s.status)) NOT LIKE '%near%'";
    } else {
        $safe = $conn->real_escape_string($sf);
        $status_where = "AND LOWER(TRIM(s.status)) = '$safe'";
    }
}
$select = "
    s.id,
    s.shelter_name,
    s.location,
    s.capacity,
    s.occupied,
    s.available,
    s.status,
    s.lat,
    s.lng,
    s.created_at,
    o.name AS org_name,
    ($distance_expr) AS distance_km,
    CASE WHEN s.capacity > 0
         THEN ROUND(s.occupied / s.capacity * 100, 1)
         ELSE 0 END AS occupancy_pct
";

$from_base = "
    FROM shelters s
    LEFT JOIN organizations o ON s.organization_id = o.id
    WHERE s.lat IS NOT NULL
      AND s.lng IS NOT NULL
";


$inner_order = $has_gps
    ? "distance_km ASC,
       CASE
           WHEN LOWER(TRIM(s.status)) = 'open'               THEN 1
           WHEN LOWER(TRIM(s.status)) LIKE '%near%full%'      THEN 2
           WHEN LOWER(TRIM(s.status)) = 'full'               THEN 3
           ELSE 4
       END ASC"
    : "s.shelter_name ASC";


$outer_order = $has_gps
    ? "distance_km ASC,
       CASE
           WHEN LOWER(TRIM(status)) = 'open'               THEN 1
           WHEN LOWER(TRIM(status)) LIKE '%near%full%'      THEN 2
           WHEN LOWER(TRIM(status)) = 'full'               THEN 3
           ELSE 4
       END ASC"
    : "shelter_name ASC";


function castRow($row) {
    $row['capacity']      = (int)$row['capacity'];
    $row['occupied']      = (int)$row['occupied'];
    $row['available']     = (int)$row['available'];
    $row['occupancy_pct'] = (float)$row['occupancy_pct'];
    $row['distance_km']   = $row['distance_km'] !== null ? (float)$row['distance_km'] : null;
    return $row;
}

try {

    // 
    if ($has_gps) {
        $sql_rec = "
            SELECT * FROM (
                SELECT $select $from_base
                ORDER BY $inner_order
                LIMIT 200
            ) AS sub
            WHERE distance_km IS NOT NULL
              AND distance_km <= $REC_RADIUS_KM
            ORDER BY $outer_order
            LIMIT 100
        ";
    } else {
        $sql_rec = "SELECT $select $from_base ORDER BY $inner_order LIMIT 100";
    }

    // 
    if ($has_gps) {
        $sql_all = "
            SELECT * FROM (
                SELECT $select $from_base $status_where
                ORDER BY $inner_order
                LIMIT 200
            ) AS sub
            ORDER BY $outer_order
            LIMIT 100
        ";
    } else {
        $sql_all = "SELECT $select $from_base $status_where ORDER BY $inner_order LIMIT 100";
    }

    $res_rec = $conn->query($sql_rec);
    $res_all = $conn->query($sql_all);

    if (!$res_rec || !$res_all) {
        echo json_encode(["status" => "error", "message" => $conn->error]);
        exit;
    }

    // Recommended — top 3 only
    $recommended = [];
    while ($row = $res_rec->fetch_assoc()) {
        $row = castRow($row);
        $row['is_recommended'] = true;
        $recommended[] = $row;
        if (count($recommended) >= 3) break;
    }
    while ($res_rec->fetch_assoc()) {} 

    $rec_ids = array_column($recommended, 'id');

    $data = [];
    while ($row = $res_all->fetch_assoc()) {
        $row = castRow($row);
        $row['is_recommended'] = in_array($row['id'], $rec_ids);
        $data[] = $row;
    }

    echo json_encode([
        "status"            => "success",
        "count"             => count($data),
        "recommended_count" => count($recommended),
        "recommended"       => $recommended,
        "data"              => $data
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
$conn->close();
?>