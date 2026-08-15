<?php
namespace App\Models;

use PDO;

class MovimentacaoRepository extends BaseRepository {
    public function registrarRetirada($usuarioId, $chaveId) {
        $stmt = $this->pdo->prepare("INSERT INTO movimentacoes (usuario_id, chave_id, data_retirada) VALUES (?, ?, NOW())");
        return $stmt->execute([$usuarioId, $chaveId]);
    }

    public function registrarDevolucao($movimentacaoId, $observacao = null) {
        $stmt = $this->pdo->prepare("UPDATE movimentacoes SET data_devolucao = NOW(), observacao = ? WHERE id = ?");
        return $stmt->execute([$observacao, $movimentacaoId]);
    }

    public function buscarAtivaPorChave($chaveId) {
        $stmt = $this->pdo->prepare("SELECT * FROM movimentacoes WHERE chave_id = ? AND data_devolucao IS NULL ORDER BY data_retirada DESC LIMIT 1");
        $stmt->execute([$chaveId]);
        return $stmt->fetch();
    }
    
    public function contarAtivas() {
        return $this->pdo->query("SELECT COUNT(*) FROM movimentacoes WHERE data_devolucao IS NULL")->fetchColumn();
    }

    public function buscarAtrasadas($horasLimite) {
        $stmt = $this->pdo->prepare("
            SELECT m.id, m.data_retirada, c.nome_sala, u.nome AS usuario_nome, u.email AS usuario_email
            FROM movimentacoes m
            JOIN chaves c ON m.chave_id = c.id
            JOIN usuarios u ON m.usuario_id = u.id
            WHERE m.data_devolucao IS NULL 
            AND u.email IS NOT NULL 
            AND u.email != ''
            AND TIMESTAMPDIFF(HOUR, m.data_retirada, NOW()) >= ?
        ");
        $stmt->execute([$horasLimite]);
        return $stmt->fetchAll();
    }

    public function contarPossePorUsuario($usuarioId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM movimentacoes WHERE usuario_id = ? AND data_devolucao IS NULL");
        $stmt->execute([$usuarioId]);
        return $stmt->fetchColumn();
    }
}
