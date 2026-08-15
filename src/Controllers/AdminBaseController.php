<?php
namespace App\Controllers;

class AdminBaseController {
    protected $pdo;
    protected $config;

    public function __construct($pdo, $config) {
        $this->pdo = $pdo;
        $this->config = $config;
    }
}
