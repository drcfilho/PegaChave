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
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $matricula = trim($_POST['matricula'] ?? '');
            $perfil_id = filter_var($_POST['perfil_id'] ?? '', FILTER_VALIDATE_INT);

            $nome = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
            $matricula = preg_replace('/[^a-zA-Z0-9_-]/', '', $matricula);
            $qr_code_hash = 'user_' . $matricula;

            if (empty($nome) || empty($matricula) || empty($qr_code_hash) || !$perfil_id) {
                throw new Exception("Por favor, preencha todos os campos obrigatórios corretamente.");
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Formato de e-mail inválido.");
            }

            $usuarioRepo->cadastrar($nome, $email, $matricula, $perfil_id, $qr_code_hash);
            registrar_log($pdo, 'Cadastro de Usuário', "Usuário '$nome' (Matrícula: $matricula) cadastrado. Hash automático: $qr_code_hash");
            
            return $this->index("Usuário '$nome' cadastrado com sucesso!", "success");
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
            $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $matricula = trim($_POST['matricula'] ?? '');
            $perfil_id = filter_var($_POST['perfil_id'] ?? '', FILTER_VALIDATE_INT);
            $ativo = isset($_POST['ativo']) ? 1 : 0;

            $nome = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
            $matricula = preg_replace('/[^a-zA-Z0-9_-]/', '', $matricula);
            $qr_code_hash = 'user_' . $matricula;

            if (!$id || empty($nome) || empty($matricula) || empty($qr_code_hash) || !$perfil_id) {
                throw new Exception("Todos os campos obrigatórios devem ser preenchidos.");
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Formato de e-mail inválido.");
            }

            $usuarioRepo->atualizar($id, $nome, $email, $matricula, $perfil_id, $qr_code_hash, $ativo);
            registrar_log($pdo, 'Edição de Usuário', "Usuário ID $id atualizado para '$nome' (Matrícula: $matricula). Ativo: $ativo. Hash automático: $qr_code_hash");
            
            return $this->index("Usuário '$nome' atualizado com sucesso!", "success");
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
