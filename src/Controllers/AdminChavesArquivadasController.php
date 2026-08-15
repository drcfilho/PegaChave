<?php
namespace App\Controllers;

use App\Models\ChaveRepository;
use App\Models\UsuarioRepository; // Used for auth_arquivados

class AdminChavesArquivadasController extends AdminBaseController {
    public function index() {
        $pdo = $this->pdo;
        extract($this->config);
        $chaveRepo = new ChaveRepository($pdo);
        $usuarioRepo = new UsuarioRepository($pdo);
        
        $message = '';
        $messageType = '';
        $autenticado = $_SESSION['arquivados_autenticado'] ?? false;
        
        // 1. Processar autenticação para ver a aba (compartilha a mesma sessão dos usuários arquivados)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'auth_arquivados') {
            $senhaInput = $_POST['senha'] ?? '';
            $adminId = $_SESSION['admin_user_id'];
            
            try {
                $admin = $usuarioRepo->buscarSenhaAdmin($adminId);
                
                if ($admin && password_verify($senhaInput, $admin['senha'])) {
                    $_SESSION['arquivados_autenticado'] = true;
                    $autenticado = true;
                    registrar_log($pdo, 'Acesso Autorizado', "Acessou a aba de chaves arquivadas.");
                } else {
                    $message = "Senha incorreta. Acesso negado.";
                    $messageType = "error";
                    registrar_log($pdo, 'Tentativa de Acesso Falha', "Tentativa falha de acessar a aba de chaves arquivadas.");
                }
            } catch (\PDOException $e) {
                $message = "Erro de banco de dados: " . $e->getMessage();
                $messageType = "error";
            }
        }
        
        // 2. Processar restauração de chave
        if ($autenticado && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'restore_chave') {
            $id = $_POST['id'] ?? '';
            try {
                $chaveInfo = $chaveRepo->buscarPorId($id);
                $nomeChave = $chaveInfo ? $chaveInfo['nome_sala'] : "ID $id";

                $chaveRepo->restaurar($id);
                
                registrar_log($pdo, 'Restauração de Chave', "Chave '$nomeChave' foi restaurada do arquivo.");
                $message = "Chave '$nomeChave' restaurada com sucesso!";
                $messageType = "success";
            } catch (\PDOException $e) {
                $message = "Erro ao restaurar chave: " . $e->getMessage();
                $messageType = "error";
            }
        }
        
        // 3. Buscar chaves arquivadas
        $chavesArquivadas = [];
        if ($autenticado) {
            try {
                $chavesArquivadas = $chaveRepo->buscarTodasArquivadas();
            } catch (\PDOException $e) {
                $message = "Erro ao buscar dados: " . $e->getMessage();
                $messageType = "error";
            }
        }
        
        global $nome_escola, $cor_primaria, $cor_secundaria;
        require_once __DIR__ . '/../Views/admin_chaves_arquivadas.php';
    }
}
