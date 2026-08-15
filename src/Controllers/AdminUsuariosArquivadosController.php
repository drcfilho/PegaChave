<?php
namespace App\Controllers;

class AdminUsuariosArquivadosController extends AdminBaseController {
    public function index() {
        $pdo = $this->pdo;
        extract($this->config);
        $usuarioRepo = new \App\Models\UsuarioRepository($pdo);
        
// admin_usuarios_arquivados.php




$message = '';
$messageType = '';
$autenticado = $_SESSION['arquivados_autenticado'] ?? false;

// 1. Processar autenticação para ver a aba
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'auth_arquivados') {
    if (false) {
        // Redundant block removed since CSRF is handled via middleware
    } else {
        $senhaInput = $_POST['senha'] ?? '';
        $adminId = $_SESSION['admin_user_id'];
        
        try {
            $admin = $usuarioRepo->buscarSenhaAdmin($adminId);
            
            if ($admin && password_verify($senhaInput, $admin['senha'])) {
                $_SESSION['arquivados_autenticado'] = true;
                $autenticado = true;
                registrar_log($pdo, 'Acesso Autorizado', "Acessou a aba de usuários arquivados.");
            } else {
                $message = "Senha incorreta. Acesso negado.";
                $messageType = "error";
                registrar_log($pdo, 'Tentativa de Acesso Falha', "Tentativa falha de acessar a aba de usuários arquivados.");
            }
        } catch (\PDOException $e) {
            $message = "Erro de banco de dados: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// 2. Processar restauração de usuário
if ($autenticado && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'restore_usuario') {
    if (false) {
        // Redundant block removed since CSRF is handled via middleware
    } else {
        $id = $_POST['id'] ?? '';
        try {
            $userInfo = $usuarioRepo->buscarDadosBasicos($id);
            $nomeUsuario = $userInfo ? $userInfo['nome'] : "ID $id";

            $usuarioRepo->restaurar($id);
            
            registrar_log($pdo, 'Restauração de Usuário', "Usuário '$nomeUsuario' foi restaurado do arquivo.");
            $message = "Usuário '$nomeUsuario' restaurado com sucesso!";
            $messageType = "success";
        } catch (\PDOException $e) {
            $message = "Erro ao restaurar usuário: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// 3. Buscar usuários arquivados
$usuariosArquivados = [];
if ($autenticado) {
    try {
        $usuariosArquivados = $usuarioRepo->buscarTodosArquivados();
    } catch (\PDOException $e) {
        $message = "Erro ao buscar dados: " . $e->getMessage();
        $messageType = "error";
    }
}

        $this->render('admin_usuarios_arquivados', get_defined_vars());
    }
}
