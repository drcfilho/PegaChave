<?php
namespace App\Controllers;

class RegrasController extends BaseController {
    protected $pdo;
    protected $config;

    public function __construct($pdo, $config) {
        $this->pdo = $pdo;
        $this->config = $config;
    }

    public function index() {
        $pdo = $this->pdo;
        extract($this->config);
        $this->render('regras', get_defined_vars());
    }
}
