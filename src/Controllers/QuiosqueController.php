<?php
namespace App\Controllers;
// src/Controllers/QuiosqueController.php

class QuiosqueController {
    public function index() {
        global $pdo, $nome_escola, $cor_primaria, $cor_secundaria, $limite_chaves;
        // Carrega configurações globais necessárias pela view ($nome_escola, $cor_primaria, etc.)
        require_once __DIR__ . '/../../api/db.php';
        
        // Renderiza a view
        require_once __DIR__ . '/../Views/quiosque.php';
    }
}
