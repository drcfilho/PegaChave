<?php
namespace App\Models;

use PDO;

class ChaveRepository extends BaseRepository {
    public function buscarTodas() {
        $stmt = $this->pdo->query("SELECT * FROM chaves ORDER BY nome_sala ASC");
        return $stmt->fetchAll();
    }

    public function buscarPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM chaves WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function buscarPorQrCode($qrCodeHash) {
        $stmt = $this->pdo->prepare("SELECT * FROM chaves WHERE qr_code_hash = ?");
        $stmt->execute([$qrCodeHash]);
        return $stmt->fetch();
    }

    public function buscarPorCodigoOuHash($codigo) {
        $stmt = $this->pdo->prepare("SELECT * FROM chaves WHERE qr_code_hash = ? OR codigo_sala = ?");
        $stmt->execute([$codigo, $codigo]);
        return $stmt->fetch();
    }

    public function atualizarStatus($id, $statusDisponivel) {
        $stmt = $this->pdo->prepare("UPDATE chaves SET status_disponivel = ? WHERE id = ?");
        return $stmt->execute([$statusDisponivel, $id]);
    }

    public function contarTotal() {
        return $this->pdo->query("SELECT COUNT(*) FROM chaves")->fetchColumn();
    }

    public function buscarParaQrCode() {
        return $this->pdo->query("SELECT id, nome_sala AS nome, bloco, andar, codigo_sala AS identificador, qr_code_hash, 'Chave' AS categoria FROM chaves ORDER BY nome_sala ASC")->fetchAll();
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
        $stmt = $this->pdo->prepare("DELETE FROM chaves WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function excluirMovimentacoesPorChaveId($id) {
        $stmt = $this->pdo->prepare("DELETE FROM movimentacoes WHERE chave_id = ?");
        return $stmt->execute([$id]);
    }

    public function buscarTodasOrdenadasPorCodigo() {
        $stmt = $this->pdo->query("SELECT * FROM chaves ORDER BY codigo_sala ASC");
        return $stmt->fetchAll();
    }
}
