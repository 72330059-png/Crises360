
<?php
session_start();
require_once('class/DAL.class.php');
$dal = new DAL();

if (isset($_SESSION['id'])) {
    $id = (int) $_SESSION['id'];
    $dal->execute("UPDATE users SET ustatus='offline' WHERE id = $id");
}

// then destroy session
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    setcookie(session_name(), '', time() - 42000, '/');
}
session_destroy();
header("Location: login.php");
exit;
?>
