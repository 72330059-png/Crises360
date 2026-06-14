<?php

$host = getenv("DB_HOST");
$user = getenv("DB_USER");
$pass = getenv("DB_PASS");
$db   = getenv("DB_NAME");
$port = (int) getenv("DB_PORT");

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "DB Connection failed: " . $conn->connect_error
    ]));
}

?>