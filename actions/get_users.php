<<<<<<< HEAD
<?php
require_once("../class/DAL.class.php");
header("Content-Type: application/json");

$dal = new DAL();
$users = $dal->getdata("SELECT id, name FROM users WHERE 1");

echo json_encode($users);
=======
<?php
require_once("../class/DAL.class.php");
header("Content-Type: application/json");

$dal = new DAL();
$users = $dal->getdata("SELECT id, name FROM users WHERE 1");

echo json_encode($users);
>>>>>>> a2bd2e69c4ac9840f7cbf5a9fa1f22a9c525c7e8
