<?php
namespace App\Controllers;

class AdminUsuariosController extends AdminBaseController {
    public function index() {
        global $pdo, $nome_escola, $cor_primaria, $cor_secundaria;
        
        $usuarioRepo = new \App\Models\UsuarioRepository($pdo);
        




$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validar_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = "Token de segurança inválido. Tente novamente.";
        $messageType = "error";
    } else {
        $action = $_POST['action'];

    try {
        if ($action === 'add_usuario') {
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $matricula = trim($_POST['matricula'] ?? '');
            $perfil_id = filter_var($_POST['perfil_id'] ?? '', FILTER_VALIDATE_INT);

            // Saneamento básico
            $nome = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
            $matricula = preg_replace('/[^a-zA-Z0-9_-]/', '', $matricula);

            // Gerar hash automaticamente
            $qr_code_hash = 'user_' . $matricula;

            if (empty($nome) || empty($matricula) || empty($qr_code_hash) || !$perfil_id) {
                throw new Exception("Por favor, preencha todos os campos obrigatórios corretamente.");
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Formato de e-mail inválido.");
            }

            $usuarioRepo->cadastrar($nome, $email, $matricula, $perfil_id, $qr_code_hash);
            registrar_log($pdo, 'Cadastro de Usuário', "Usuário '$nome' (Matrícula: $matricula) cadastrado. Hash automático: $qr_code_hash");
            $message = "Usuário '$nome' cadastrado com sucesso!";
            $messageType = "success";
        }

        elseif ($action === 'edit_usuario') {
            $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $matricula = trim($_POST['matricula'] ?? '');
            $perfil_id = filter_var($_POST['perfil_id'] ?? '', FILTER_VALIDATE_INT);
            $ativo = isset($_POST['ativo']) ? 1 : 0;

            $nome = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
            $matricula = preg_replace('/[^a-zA-Z0-9_-]/', '', $matricula);

            // Gerar hash automaticamente
            $qr_code_hash = 'user_' . $matricula;

            if (!$id || empty($nome) || empty($matricula) || empty($qr_code_hash) || !$perfil_id) {
                throw new Exception("Todos os campos obrigatórios devem ser preenchidos.");
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Formato de e-mail inválido.");
            }

            $usuarioRepo->atualizar($id, $nome, $email, $matricula, $perfil_id, $qr_code_hash, $ativo);
            registrar_log($pdo, 'Edição de Usuário', "Usuário ID $id atualizado para '$nome' (Matrícula: $matricula). Ativo: $ativo. Hash automático: $qr_code_hash");
            $message = "Usuário '$nome' atualizado com sucesso!";
            $messageType = "success";
        }

        elseif ($action === 'delete_usuario') {
            $id = $_POST['id'] ?? '';
            // Obter nome antes de excluir
            $userInfo = $usuarioRepo->buscarDadosBasicos($id);
            $nomeExcluido = $userInfo ? $userInfo['nome'] . ' (Matrícula: ' . $userInfo['matricula'] . ')' : "ID $id";

            // Não apagamos mais as movimentações para manter o histórico de auditoria intacto.
            $usuarioRepo->arquivar($id);
            registrar_log($pdo, 'Arquivamento de Usuário', "Usuário '$nomeExcluido' foi arquivado.");
            $message = "Usuário arquivado para auditoria com sucesso.";
            $messageType = "success";
        }
    } catch (\PDOException $e) {
        $message = "Erro ao salvar dados: " . $e->getMessage();
        $messageType = "error";
    }
  }
}

try {
    $usuarios = $usuarioRepo->buscarTodosNaoExcluidos();

    $perfis = $usuarioRepo->buscarTodosPerfis();
} catch (\PDOException $e) {
    echo "Erro de Banco de Dados: " . $e->getMessage();
    exit;
}

        require_once __DIR__ . '/../Views/admin_usuarios.php';
    }
}
