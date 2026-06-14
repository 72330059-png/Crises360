<?php
session_start();
header("Content-Type: application/json");
require_once("../class/DAL.class.php");

$dal = new DAL();
$org_id = (int)$_SESSION['org_id'];

$rows = $dal->getdata("SELECT id, status FROM transfers WHERE organization_id = $org_id");

echo json_encode(['transfers' => $rows ?: []]);