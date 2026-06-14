<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include "db.php";

$sql    = "SELECT id FROM alerts ORDER BY created_at DESC";
$result = $conn->query($sql);

$ids = [];
while ($row = $result->fetch_assoc()) {
    $ids[] = (int)$row["id"];
}

echo json_encode([
    "status" => "success",
    "ids"    => $ids,
    "total"  => count($ids)
]);
$conn->close();
?>