<?php
namespace App\Models;

class RelatorioRepository extends BaseRepository {
    public function getAllChaves() {
        return $this->pdo->query("SELECT id, nome_sala, codigo_sala FROM chaves ORDER BY nome_sala ASC")->fetchAll();
    }

    public function getAllUsuarios() {
        return $this->pdo->query("SELECT id, nome FROM usuarios ORDER BY nome ASC")->fetchAll();
    }

    public function getMovimentacoes($usuario_id, $chave_id, $data_inicio, $data_fim) {
        $sql = "
            SELECT 
                m.id, 
                m.data_retirada, 
                m.data_devolucao, 
                m.observacao,
                u.nome AS usuario_nome, 
                u.matricula AS usuario_matricula,
                c.nome_sala, 
                c.codigo_sala
            FROM movimentacoes m
            JOIN usuarios u ON m.usuario_id = u.id
            JOIN chaves c ON m.chave_id = c.id
            WHERE 1=1
        ";
        
        $params = [];

        if ($usuario_id) {
            $sql .= " AND m.usuario_id = ?";
            $params[] = $usuario_id;
        }

        if ($chave_id) {
            $sql .= " AND m.chave_id = ?";
            $params[] = $chave_id;
        }

        if ($data_inicio) {
            $sql .= " AND m.data_retirada >= ?";
            $params[] = $data_inicio . " 00:00:00";
        }

        if ($data_fim) {
            $sql .= " AND m.data_retirada <= ?";
            $params[] = $data_fim . " 23:59:59";
        }

        $sql .= " ORDER BY m.data_retirada DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
