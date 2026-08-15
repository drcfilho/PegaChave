<?php
namespace App\Models;

use PDO;

class ChaveRepository extends BaseRepository {
    public function buscarTodas() {
        $stmt = $this->pdo->query("SELECT * FROM chaves WHERE deleted_at IS NULL ORDER BY nome_sala ASC");
        return $stmt->fetchAll();
    }

    public function buscarPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM chaves WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function buscarPorQrCode($qrCodeHash) {
        $stmt = $this->pdo->prepare("SELECT * FROM chaves WHERE qr_code_hash = ? AND deleted_at IS NULL");
        $stmt->execute([$qrCodeHash]);
        return $stmt->fetch();
    }

    public function buscarPorCodigoOuHash($codigo) {
        $stmt = $this->pdo->prepare("SELECT * FROM chaves WHERE (qr_code_hash = ? OR codigo_sala = ?) AND deleted_at IS NULL");
        $stmt->execute([$codigo, $codigo]);
        return $stmt->fetch();
    }

    public function atualizarStatus($id, $statusDisponivel) {
        $stmt = $this->pdo->prepare("UPDATE chaves SET status_disponivel = ? WHERE id = ?");
        return $stmt->execute([$statusDisponivel, $id]);
    }

    public function contarTotal() {
        return $this->pdo->query("SELECT COUNT(*) FROM chaves WHERE deleted_at IS NULL")->fetchColumn();
    }

    public function buscarParaQrCode() {
        return $this->pdo->query("SELECT id, nome_sala AS nome, bloco, andar, codigo_sala AS identificador, qr_code_hash, 'Chave' AS categoria FROM chaves WHERE deleted_at IS NULL ORDER BY nome_sala ASC")->fetchAll();
    }

    public function criar($nome_sala, $bloco, $andar, $matriculas_permitidas, $codigo_sala, $qr_code_hash, $descricao) {
        $stmt = $this->pdo->prepare("INSERT INTO chaves (nome_sala, bloco, andar, matriculas_permitidas, codigo_sala, qr_code_hash, descricao) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$nome_sala, $bloco, $andar, $matriculas_permitidas, $codigo_sala, $qr_code_hash, $descricao]);
    }

    public function atualizar($nome_sala, $bloco, $andar, $matriculas_permitidas, $codigo_sala, $qr_code_hash, $descricao, $id) {
        $stmt = $this->pdo->prepare("UPDATE chaves SET nome_sala = ?, bloco = ?, andar = ?, matriculas_permitidas = ?, codigo_sala = ?, qr_code_hash = ?, descricao = ? WHERE id = ?");
        return $stmt->execute([$nome_sala, $bloco, $andar, $matriculas_permitidas, $codigo_sala, $qr_code_hash, $descricao, $id]);
    }

    public function excluirPorId($id) {
        $stmt = $this->pdo->prepare("UPDATE chaves SET deleted_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function buscarTodasArquivadas() {
        $stmt = $this->pdo->query("SELECT * FROM chaves WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
        return $stmt->fetchAll();
    }

    public function restaurar($id) {
        $stmt = $this->pdo->prepare("UPDATE chaves SET deleted_at = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function excluirMovimentacoesPorChaveId($id) {
        $stmt = $this->pdo->prepare("DELETE FROM movimentacoes WHERE chave_id = ?");
        return $stmt->execute([$id]);
    }

    public function buscarTodasOrdenadasPorCodigo() {
        $stmt = $this->pdo->query("SELECT * FROM chaves WHERE deleted_at IS NULL ORDER BY codigo_sala ASC");
        return $stmt->fetchAll();
    }

    public function salvarNovaChave($dados) {
        $nome_sala = trim($dados['nome_sala'] ?? '');
        $bloco = trim($dados['bloco'] ?? '');
        $andar = trim($dados['andar'] ?? '');
        $matriculas_permitidas = trim($dados['matriculas_permitidas'] ?? '');
        $codigo_sala = trim($dados['codigo_sala'] ?? '');
        $descricao = trim($dados['descricao'] ?? '');

        $nome_sala = htmlspecialchars($nome_sala, ENT_QUOTES, 'UTF-8');
        $bloco = htmlspecialchars($bloco, ENT_QUOTES, 'UTF-8');
        $andar = htmlspecialchars($andar, ENT_QUOTES, 'UTF-8');
        $matriculas_permitidas = htmlspecialchars($matriculas_permitidas, ENT_QUOTES, 'UTF-8');
        $codigo_sala = preg_replace('/[^a-zA-Z0-9_-]/', '', $codigo_sala);
        $descricao = htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8');

        $qr_code_hash = 'chaves_' . $codigo_sala;

        if (empty($nome_sala) || empty($codigo_sala) || empty($qr_code_hash)) {
            throw new \Exception("Por favor, preencha todos os campos obrigatórios.");
        }

        $this->criar($nome_sala, $bloco, $andar, $matriculas_permitidas, $codigo_sala, $qr_code_hash, $descricao);
        return [
            'nome_sala' => $nome_sala,
            'bloco' => $bloco,
            'andar' => $andar,
            'matriculas_permitidas' => $matriculas_permitidas,
            'codigo_sala' => $codigo_sala,
            'qr_code_hash' => $qr_code_hash
        ];
    }

    public function salvarAtualizacaoChave($dados) {
        $id = filter_var($dados['id'] ?? '', FILTER_VALIDATE_INT);
        $nome_sala = trim($dados['nome_sala'] ?? '');
        $bloco = trim($dados['bloco'] ?? '');
        $andar = trim($dados['andar'] ?? '');
        $matriculas_permitidas = trim($dados['matriculas_permitidas'] ?? '');
        $codigo_sala = trim($dados['codigo_sala'] ?? '');
        $descricao = trim($dados['descricao'] ?? '');

        $nome_sala = htmlspecialchars($nome_sala, ENT_QUOTES, 'UTF-8');
        $bloco = htmlspecialchars($bloco, ENT_QUOTES, 'UTF-8');
        $andar = htmlspecialchars($andar, ENT_QUOTES, 'UTF-8');
        $matriculas_permitidas = htmlspecialchars($matriculas_permitidas, ENT_QUOTES, 'UTF-8');
        $codigo_sala = preg_replace('/[^a-zA-Z0-9_-]/', '', $codigo_sala);
        $descricao = htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8');

        $qr_code_hash = 'chaves_' . $codigo_sala;

        if (!$id || empty($nome_sala) || empty($codigo_sala) || empty($qr_code_hash)) {
            throw new \Exception("Todos os campos obrigatórios devem ser preenchidos.");
        }

        $this->atualizar($nome_sala, $bloco, $andar, $matriculas_permitidas, $codigo_sala, $qr_code_hash, $descricao, $id);
        return [
            'id' => $id,
            'nome_sala' => $nome_sala,
            'bloco' => $bloco,
            'andar' => $andar,
            'matriculas_permitidas' => $matriculas_permitidas,
            'codigo_sala' => $codigo_sala,
            'qr_code_hash' => $qr_code_hash
        ];
    }
}
