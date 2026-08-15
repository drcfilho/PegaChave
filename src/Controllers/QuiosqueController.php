<?php
namespace App\Controllers;
// src/Controllers/QuiosqueController.php

class QuiosqueController extends BaseController {
    protected $pdo;
    protected $config;

    public function __construct($pdo, $config) {
        $this->pdo = $pdo;
        $this->config = $config;
    }

    public function index() {
        $pdo = $this->pdo;
        extract($this->config);
        // Carrega configurações globais necessárias pela view ($nome_escola, $cor_primaria, etc.)
        
        
        // Renderiza a view
        $this->render('quiosque', get_defined_vars());
    }
}
