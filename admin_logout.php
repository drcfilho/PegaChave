<?php
// admin_logout.php
session_start();
if (!defined('BASE_URL')) { define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\')); }
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
header("Location: " . BASE_URL . "/admin_login.php");
exit;
