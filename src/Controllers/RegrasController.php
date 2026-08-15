<?php
namespace App\Controllers;

class RegrasController {
    protected $pdo;
    protected $config;

    public function __construct($pdo, $config) {
        $this->pdo = $pdo;
        $this->config = $config;
    }

    public function index() {
        $pdo = $this->pdo;
        extract($this->config);
        require_once __DIR__ . '/../Views/regras.php';
    }
}
