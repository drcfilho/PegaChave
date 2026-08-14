<?php
namespace App\Controllers;

class RegrasController {
    public function index() {
        global $pdo, $nome_escola, $cor_primaria, $cor_secundaria;
        require_once __DIR__ . '/../Views/regras.php';
    }
}
