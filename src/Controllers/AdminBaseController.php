<?php
namespace App\Controllers;

class AdminBaseController {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->checkAuth();
        require_once __DIR__ . '/../../api/db.php';
    }

    protected function checkAuth() {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header("Location: " . BASE_URL . "/admin_login.php");
            exit;
        }
    }
}
