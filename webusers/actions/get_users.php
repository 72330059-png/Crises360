<?php
session_start();
require_once("../class/DAL.class.php");
header("Content-Type: application/json");

$dal = new DAL();
$users = $dal->getdata("SELECT id, name FROM users WHERE 1");

echo json_encode($users);
