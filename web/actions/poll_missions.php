<?php
session_start();
header("Content-Type: application/json");
require_once("../class/DAL.class.php");

$dal = new DAL();
$org_id = (int)$_SESSION['org_id'];

$rows = $dal->getdata("SELECT mission_id AS id, status FROM missions WHERE organization_id = $org_id");

echo json_encode(['missions' => $rows ?: []]);