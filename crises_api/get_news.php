<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include "db.php";



$sql = "SELECT id, title, content, category, type, status,
               featured, views, publish_date, created_at
        FROM news
        WHERE status = 'published'
        ORDER BY featured DESC, publish_date DESC";

$result = $conn->query($sql);
$news = array();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        $imageUrl = "";
        if (!empty($row["image"])) {
            $imageUrl = $baseUrl . $row["image"];
        }

        $news[] = array(
            "id"           => $row["id"],
            "title"        => $row["title"],
            "content"      => $row["content"],
            "category"     => $row["category"],
            "type"         => $row["type"],
            "status"       => $row["status"],
            "featured"     => (int)$row["featured"],
            "views"        => (int)$row["views"],
            "publish_date" => $row["publish_date"],
            "created_at"   => $row["created_at"]
        );
    }
}

echo json_encode($news);
$conn->close();
?>