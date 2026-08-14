<?php
namespace App\Controllers;

use App\Models\ChaveRepository;
use App\Models\UsuarioRepository;

class AdminGerarQrController extends AdminBaseController {
    public function index() {
        global $pdo, $nome_escola, $cor_primaria, $cor_secundaria;
        
// admin_gerar_qr.php

try {
    $chaveRepo = new ChaveRepository($pdo);
    $usuarioRepo = new UsuarioRepository($pdo);
    
    // Buscar todas as chaves
    $chaves = $chaveRepo->buscarParaQrCode();
    
    // Buscar todos os usuários
    $usuarios = $usuarioRepo->buscarParaQrCode();
} catch (\PDOException $e) {
    echo "Erro de Banco de Dados: " . $e->getMessage();
    exit;
}

        require_once __DIR__ . '/../Views/admin_gerar_qr.php';
    }
}
