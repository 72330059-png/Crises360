<?php
ob_start();
session_start();
require_once('class/DAL.class.php');
$dal = new DAL();
if (isset($_SESSION['org_id'])) {
    $id = (int)$_SESSION['org_id'];
    $dal->execute("UPDATE organizations SET ustatus='offline' WHERE id = $id");
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    setcookie(session_name(), '', time() - 42000, '/');
}
session_destroy();
ob_end_clean();
header('Location: login.php');
exit;
