<?php
namespace App\Controllers;

use Exception;

class AdminUsuariosController extends AdminBaseController {
    public function index($message = '', $messageType = '') {
        $pdo = $this->pdo;
        extract($this->config);
        $usuarioRepo = new \App\Models\UsuarioRepository($pdo);
        
        try {
            $usuarios = $usuarioRepo->buscarTodosNaoExcluidos();
            $perfis = $usuarioRepo->buscarTodosPerfis();
        } catch (\PDOException $e) {
            echo "Erro de Banco de Dados: " . $e->getMessage();
            exit;
        }

        require_once __DIR__ . '/../Views/admin_usuarios.php';
    }

    public function store() {
        if (!validar_csrf_token($_POST['csrf_token'] ?? '')) {
            return $this->index("Token de segurança inválido. Tente novamente.", "error");
        }

        $pdo = $this->pdo;
        $usuarioRepo = new \App\Models\UsuarioRepository($pdo);

        try {
            $resultado = $usuarioRepo->salvarNovoUsuario($_POST);
            registrar_log($pdo, 'Cadastro de Usuário', "Usuário '{$resultado['nome']}' (Matrícula: {$resultado['matricula']}) cadastrado. Hash automático: {$resultado['qr_code_hash']}");
            
            return $this->index("Usuário '{$resultado['nome']}' cadastrado com sucesso!", "success");
        } catch (\Exception $e) {
            return $this->index($e->getMessage(), "error");
        }
    }

    public function update() {
        if (!validar_csrf_token($_POST['csrf_token'] ?? '')) {
            return $this->index("Token de segurança inválido. Tente novamente.", "error");
        }

        $pdo = $this->pdo;
        $usuarioRepo = new \App\Models\UsuarioRepository($pdo);

        try {
            $resultado = $usuarioRepo->salvarAtualizacaoUsuario($_POST);
            registrar_log($pdo, 'Edição de Usuário', "Usuário ID {$resultado['id']} atualizado para '{$resultado['nome']}' (Matrícula: {$resultado['matricula']}). Ativo: {$resultado['ativo']}. Hash automático: {$resultado['qr_code_hash']}");
            
            return $this->index("Usuário '{$resultado['nome']}' atualizado com sucesso!", "success");
        } catch (\Exception $e) {
            return $this->index($e->getMessage(), "error");
        }
    }

    public function destroy() {
        if (!validar_csrf_token($_POST['csrf_token'] ?? '')) {
            return $this->index("Token de segurança inválido. Tente novamente.", "error");
        }

        $pdo = $this->pdo;
        $usuarioRepo = new \App\Models\UsuarioRepository($pdo);

        try {
            $id = $_POST['id'] ?? '';
            $userInfo = $usuarioRepo->buscarDadosBasicos($id);
            $nomeExcluido = $userInfo ? $userInfo['nome'] . ' (Matrícula: ' . $userInfo['matricula'] . ')' : "ID $id";

            $usuarioRepo->arquivar($id);
            registrar_log($pdo, 'Arquivamento de Usuário', "Usuário '$nomeExcluido' foi arquivado.");
            
            return $this->index("Usuário arquivado para auditoria com sucesso.", "success");
        } catch (\Exception $e) {
            return $this->index($e->getMessage(), "error");
        }
    }
}
