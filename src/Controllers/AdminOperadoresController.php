<?php
namespace App\Controllers;

use App\Models\OperadorRepository;

class AdminOperadoresController extends AdminBaseController {
    private $operadorRepo;

    public function __construct($pdo, $config, $blade = null) {
        parent::__construct($pdo, $config, $blade);
        $this->operadorRepo = new OperadorRepository($pdo);
    }

    public function index() {
        $pdo = $this->pdo;
        extract($this->config);
        $operadores = $this->operadorRepo->getAll();
        
        $this->render('admin_operadores', get_defined_vars());
    }

    public function create() {
        $pdo = $this->pdo;
        extract($this->config);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {


            $usuario = trim($_POST['usuario']);
            $senha = $_POST['senha'];
            $nome = trim($_POST['nome']);
            $role = $_POST['role'];
            $permissoes = $_POST['permissoes'] ?? [];

            try {
                $this->operadorRepo->insert($usuario, $senha, $nome, $role, $permissoes);
                registrar_log($pdo, 'Cadastro de Operador', "Operador {$usuario} cadastrado.");
                header("Location: " . BASE_URL . "/admin/operadores?success=1");
                exit;
            } catch (\Exception $e) {
                die("Erro ao cadastrar operador: " . $e->getMessage());
            }
        }
    }

    public function update() {
        $pdo = $this->pdo;
        extract($this->config);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {


            $id = (int)$_POST['id'];
            $usuario = trim($_POST['usuario']);
            $senha = $_POST['senha']; // Opcional
            $nome = trim($_POST['nome']);
            $role = $_POST['role'];
            $permissoes = $_POST['permissoes'] ?? [];

            // Segurança: Não permitir que um operador altere a si mesmo para admin_master ou tire seu próprio acesso master
            if ($_SESSION['admin_user_id'] == $id && $role !== 'admin_master' && $_SESSION['admin_role'] === 'admin_master') {
                die("Você não pode remover seu próprio acesso de Mestre.");
            }

            try {
                $this->operadorRepo->update($id, $usuario, $senha, $nome, $role, $permissoes);
                registrar_log($pdo, 'Edição de Operador', "Operador ID {$id} ({$usuario}) atualizado.");
                header("Location: " . BASE_URL . "/admin/operadores?success=2");
                exit;
            } catch (\Exception $e) {
                die("Erro ao atualizar operador: " . $e->getMessage());
            }
        }
    }

    public function delete() {
        $pdo = $this->pdo;
        extract($this->config);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {


            $id = (int)$_POST['id'];

            if ($_SESSION['admin_user_id'] == $id) {
                die("Você não pode deletar a si mesmo.");
            }

            try {
                $this->operadorRepo->delete($id);
                registrar_log($pdo, 'Exclusão de Operador', "Operador ID {$id} excluído.");
                header("Location: " . BASE_URL . "/admin/operadores?success=3");
                exit;
            } catch (\Exception $e) {
                die("Erro ao excluir operador: " . $e->getMessage());
            }
        }
    }
}
