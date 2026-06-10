<?php

$host = "mysql-1437fb54-mourtadadouaa-3f8a.k.aivencloud.com";
$user = "avnadmin";
$pass = "AVNS_XISdQLhPfP-pdPbsDK8";
$db   = "defaultdb";
$port = 20743;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "DB Connection failed: " . $conn->connect_error
    ]));
}

?>