<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once "db.php";

// INPUT 
$user_lat      = isset($_GET['lat'])    ? floatval($_GET['lat'])    : null;
$user_lng      = isset($_GET['lng'])    ? floatval($_GET['lng'])    : null;
$status_filter = isset($_GET['status']) ? trim($_GET['status'])     : null;

$REC_RADIUS_KM = 15;   
$distance_select = "NULL";
$order_clause    = "o.name ASC";

// DISTANCE CALCULATION 
if ($user_lat !== null && $user_lng !== null) {
    $lat = floatval($user_lat);
    $lng = floatval($user_lng);

    $distance_select = "
        ROUND(6371 * ACOS(GREATEST(-1, LEAST(1,
            COS(RADIANS($lat)) * COS(RADIANS(o.lat)) *
            COS(RADIANS(o.lng) - RADIANS($lng)) +
            SIN(RADIANS($lat)) * SIN(RADIANS(o.lat))
        ))), 2)
    ";

    $order_clause = "
        distance_km ASC,
        CASE LOWER(h.hospital_status)
            WHEN 'safe'      THEN 1
            WHEN 'warning'   THEN 2
            WHEN 'dangerous' THEN 3
            ELSE 4
        END ASC,
        occupancy_pct ASC
    ";
}

// STATUS FILTER 
$status_where = "";
if ($status_filter && strtolower($status_filter) !== "all") {
    $safe = $conn->real_escape_string($status_filter);
    $status_where = "AND LOWER(h.hospital_status) = '" . strtolower($safe) . "'";
}

// SELECT FIELDS
$select = "
    o.id,
    o.name,
    o.location,
    o.lat,
    o.lng,
    h.phone,
    h.total_beds,
    h.available_beds,
    (h.total_beds - h.available_beds) AS occupied_beds,
    h.hospital_status,
    h.updated_at,
    ($distance_select) AS distance_km,
    CASE WHEN h.total_beds > 0
         THEN ROUND((h.total_beds - h.available_beds) / h.total_beds * 100, 1)
         ELSE 0 END AS occupancy_pct
";

// FROM 
$from = "
    FROM organizations o
    JOIN hospitals h ON o.id = h.organization_id
    WHERE o.type = 'hospital'
      AND o.lat IS NOT NULL
      AND o.lng IS NOT NULL
";

// CAST FUNCTION 
function castHospitalRow($row) {
    $row['total_beds']     = (int)$row['total_beds'];
    $row['available_beds'] = (int)$row['available_beds'];
    $row['occupied_beds']  = (int)$row['occupied_beds'];
    $row['occupancy_pct']  = (float)$row['occupancy_pct'];
    $row['distance_km']    = $row['distance_km'] !== null ? (float)$row['distance_km'] : null;
    return $row;
}

try {

    // RECOMMENDED QUERY 
    $having_distance = $user_lat !== null
        ? "HAVING distance_km IS NOT NULL AND distance_km <= $REC_RADIUS_KM"
        : "";

    $sql_rec = "
        SELECT $select
        $from
        AND LOWER(h.hospital_status) != 'dangerous'
        $having_distance
        ORDER BY $order_clause
        LIMIT 100
    ";

    //  ALL DATA QUERY 
    $sql_all = "
        SELECT $select
        $from
        $status_where
        ORDER BY $order_clause
        LIMIT 100
    ";

    $res_rec = $conn->query($sql_rec);
    $res_all = $conn->query($sql_all);

    if (!$res_rec || !$res_all) {
        echo json_encode(["status" => "error", "message" => $conn->error]);
        exit;
    }

    //  RECOMMENDED DATA 
    $recommended = [];
    while ($row = $res_rec->fetch_assoc()) {
        $row = castHospitalRow($row);
        $row['is_recommended'] = true;
        $recommended[] = $row;
    }

    //  ALL DATA 
    $rec_ids = array_column($recommended, 'id');
    $data    = [];

    while ($row = $res_all->fetch_assoc()) {
        $row = castHospitalRow($row);
        $row['is_recommended'] = in_array($row['id'], $rec_ids);
        $data[] = $row;
    }

    //  OUTPUT 
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