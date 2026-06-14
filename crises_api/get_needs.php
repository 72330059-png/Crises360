<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once "db.php";

if (!$conn) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

//  INPUT 
$user_lat  = isset($_GET['lat'])      ? floatval($_GET['lat'])           : null;
$user_lng  = isset($_GET['lng'])      ? floatval($_GET['lng'])           : null;
$category  = isset($_GET['category']) ? trim($_GET['category'])          : null;

$REC_RADIUS_KM = 15;

// CATEGORY FILTER 
// Tab values → DB category values (from your actual data)
$category_where = "";
if ($category && strtolower($category) !== "all") {
    $cat = strtolower(trim($category));

    $map = [
        "food"    => ["food", "bakery", "restaurant", "supermarket"],
        "water"   => ["water", "water_station"],
        "medical" => ["medical", "pharmacy", "hospital"],
        "fuel"    => ["fuel", "fuel_station"],
    ];

    if (isset($map[$cat])) {
        $values = array_map(fn($v) => "'" . $conn->real_escape_string($v) . "'", $map[$cat]);
        $category_where = "AND LOWER(r.category) IN (" . implode(",", $values) . ")";
    } else {
        $safe = $conn->real_escape_string($cat);
        $category_where = "AND LOWER(r.category) = '$safe'";
    }
}

//  DISTANCE SQL 
$distance_select = "NULL";
$order_clause    = "resource_name ASC";

if ($user_lat !== null && $user_lng !== null) {
    $lat = floatval($user_lat);
    $lng = floatval($user_lng);

    $distance_select = "
        ROUND(6371 * ACOS(GREATEST(-1, LEAST(1,
            COS(RADIANS($lat)) * COS(RADIANS(r.lat)) *
            COS(RADIANS(r.lng) - RADIANS($lng)) +
            SIN(RADIANS($lat)) * SIN(RADIANS(r.lat))
        ))), 2)";

    // Nearest first, then active status, then name
    // No table alias in ORDER BY — works for both subquery and direct query
    $order_clause = "
        distance_km ASC,
        CASE LOWER(TRIM(status))
            WHEN 'active'    THEN 1
            WHEN 'available' THEN 1
            WHEN 'open'      THEN 1
            ELSE 2
        END ASC,
        resource_name ASC
    ";
}

//  SHARED SELECT + FROM 
$select = "
    r.resource_id,
    r.resource_name,
    r.category,
    r.address,
    r.contact_number,
    r.opening_hours,
    r.status,
    r.notes,
    r.created_at,
    o.id         AS org_id,
    o.name       AS organization_name,
    o.location,
    r.lat,
    r.lng,
    ($distance_select) AS distance_km
";

$from_base = "
    FROM resources r
    JOIN organizations o ON r.organization_id = o.id
    WHERE r.lat IS NOT NULL
      AND r.lng IS NOT NULL
      $category_where
";

try {

    //  QUERY 1: recommended — nearest ≤15 km only
    if ($user_lat !== null && $user_lng !== null) {
        $sql_rec = "
            SELECT * FROM (
                SELECT $select $from_base
                ORDER BY distance_km ASC
                LIMIT 200
            ) AS sub
            WHERE distance_km IS NOT NULL
              AND distance_km <= $REC_RADIUS_KM
            ORDER BY $order_clause
            LIMIT 100
        ";
    } else {
        $sql_rec = "
            SELECT $select $from_base
            ORDER BY r.resource_name ASC
            LIMIT 100
        ";
    }

    //  QUERY 2: full list respects category filter, all distances
    $sql_all = "
        SELECT $select $from_base
        ORDER BY $order_clause
        LIMIT 200
    ";

    $res_rec = $conn->query($sql_rec);
    $res_all = $conn->query($sql_all);

    if (!$res_rec || !$res_all) {
        echo json_encode(["status" => "error", "message" => $conn->error]);
        exit;
    }

    // Build recommended array — ALL ≤15 km results, no cap
    $recommended = [];
    while ($row = $res_rec->fetch_assoc()) {
        $row['distance_km']    = $row['distance_km'] !== null ? (float)$row['distance_km'] : null;
        $row['is_recommended'] = true;
        $recommended[] = $row;
    }

    // Mark all recommended IDs in the full list
    $rec_ids = array_column($recommended, 'resource_id');

    $data = [];
    while ($row = $res_all->fetch_assoc()) {
        $row['distance_km']    = $row['distance_km'] !== null ? (float)$row['distance_km'] : null;
        $row['is_recommended'] = in_array($row['resource_id'], $rec_ids);
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