<?php
namespace App\Models;

use PDO;

class UsuarioRepository extends BaseRepository {
    public function buscarTodosAtivos() {
        $stmt = $this->pdo->query("SELECT u.*, p.nome as perfil_nome FROM usuarios u LEFT JOIN perfis p ON u.perfil_id = p.id WHERE u.ativo = 1 AND u.excluido = 0 ORDER BY u.nome ASC");
        return $stmt->fetchAll();
    }

    public function buscarPorId($id) {
        $stmt = $this->pdo->prepare("SELECT u.*, p.nome as perfil_nome FROM usuarios u LEFT JOIN perfis p ON u.perfil_id = p.id WHERE u.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function buscarPorQrCode($qrCodeHash) {
        $stmt = $this->pdo->prepare("SELECT u.*, p.nome as perfil_nome FROM usuarios u LEFT JOIN perfis p ON u.perfil_id = p.id WHERE u.qr_code_hash = ? AND u.excluido = 0");
        $stmt->execute([$qrCodeHash]);
        return $stmt->fetch();
    }

    public function buscarPorMatriculaOuHash($codigo) {
        $stmt = $this->pdo->prepare("SELECT id, nome, matricula, perfil_id, ativo FROM usuarios WHERE qr_code_hash = ? OR matricula = ?");
        $stmt->execute([$codigo, $codigo]);
        return $stmt->fetch();
    }

    public function contarTotal() {
        return $this->pdo->query("SELECT COUNT(*) FROM usuarios WHERE excluido = 0")->fetchColumn();
    }

    public function buscarParaQrCode() {
        return $this->pdo->query("SELECT id, nome, matricula AS identificador, qr_code_hash, 'Usuário' AS categoria FROM usuarios ORDER BY nome ASC")->fetchAll();
    }

    public function cadastrar($nome, $email, $matricula, $perfil_id, $qr_code_hash) {
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nome, email, matricula, perfil_id, qr_code_hash) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$nome, $email, $matricula, $perfil_id, $qr_code_hash]);
    }

    public function atualizar($id, $nome, $email, $matricula, $perfil_id, $qr_code_hash, $ativo) {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, matricula = ?, perfil_id = ?, qr_code_hash = ?, ativo = ? WHERE id = ?");
        return $stmt->execute([$nome, $email, $matricula, $perfil_id, $qr_code_hash, $ativo, $id]);
    }

    public function buscarDadosBasicos($id) {
        $stmt = $this->pdo->prepare("SELECT nome, matricula FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function arquivar($id) {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET excluido = TRUE, ativo = FALSE WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function restaurar($id) {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET excluido = FALSE, ativo = TRUE WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function buscarTodosNaoExcluidos() {
        return $this->pdo->query("
            SELECT u.*, p.nome AS funcao 
            FROM usuarios u 
            JOIN perfis p ON u.perfil_id = p.id 
            WHERE u.excluido = FALSE
            ORDER BY u.nome ASC
        ")->fetchAll();
    }

    public function buscarTodosArquivados() {
        return $this->pdo->query("
            SELECT u.*, p.nome AS funcao 
            FROM usuarios u 
            JOIN perfis p ON u.perfil_id = p.id 
            WHERE u.excluido = TRUE
            ORDER BY u.nome ASC
        ")->fetchAll();
    }

    public function buscarTodosPerfis() {
        return $this->pdo->query("SELECT * FROM perfis ORDER BY nome ASC")->fetchAll();
    }

    public function buscarSenhaAdmin($id) {
        $stmt = $this->pdo->prepare("SELECT senha FROM administradores WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
