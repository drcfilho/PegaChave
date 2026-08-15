<?php
namespace App\Controllers;
// src/Controllers/QuiosqueController.php

class QuiosqueController extends BaseController {


    public function index() {
        $pdo = $this->pdo;
        extract($this->config);
        
        $blocos_disponiveis = [];
        if (($modo_portaria ?? 'geral') === 'blocos') {
            $stmt = $pdo->query("SELECT DISTINCT bloco FROM chaves WHERE bloco IS NOT NULL AND bloco != '' AND deleted_at IS NULL ORDER BY bloco ASC");
            $blocos_disponiveis = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        }
        
        $this->render('quiosque', get_defined_vars());
    }
}
