<?php
namespace App\Models;

class DashboardRepository extends BaseRepository {
    public function getChavesEmUso() {
        return $this->pdo->query("
            SELECT 
                c.id AS chave_id,
                c.nome_sala,
                c.codigo_sala,
                u.nome AS usuario_nome,
                p.nome AS usuario_perfil,
                DATE_FORMAT(m.data_retirada, '%H:%i') AS desde,
                IF(m.data_retirada < DATE_SUB(NOW(), INTERVAL 8 HOUR), 1, 0) AS atrasada,
                m.id AS movimentacao_id
            FROM chaves c
            JOIN movimentacoes m ON c.id = m.chave_id AND m.data_devolucao IS NULL
            JOIN usuarios u ON m.usuario_id = u.id
            JOIN perfis p ON u.perfil_id = p.id
            ORDER BY m.data_retirada DESC
        ")->fetchAll();
    }

    public function getTotalChaves() {
        return $this->pdo->query("SELECT COUNT(*) FROM chaves")->fetchColumn();
    }

    public function getDevolucoesHoje() {
        return $this->pdo->query("
            SELECT COUNT(*) FROM movimentacoes 
            WHERE DATE(data_devolucao) = CURDATE()
        ")->fetchColumn();
    }

    public function getRetiradasHoje() {
        return $this->pdo->query("
            SELECT COUNT(*) FROM movimentacoes 
            WHERE DATE(data_retirada) = CURDATE()
        ")->fetchColumn();
    }

    public function getPendentesCount() {
        return $this->pdo->query("
            SELECT COUNT(*) FROM movimentacoes 
            WHERE data_devolucao IS NULL 
            AND data_retirada < DATE_SUB(NOW(), INTERVAL 8 HOUR)
        ")->fetchColumn();
    }

    public function getTopSalas() {
        return $this->pdo->query("
            SELECT c.nome_sala, COUNT(m.id) AS total_retiradas
            FROM movimentacoes m
            JOIN chaves c ON m.chave_id = c.id
            GROUP BY c.id
            ORDER BY total_retiradas DESC
            LIMIT 5
        ")->fetchAll();
    }

    public function getFluxoSemanal() {
        return $this->pdo->query("
            SELECT 
                DATE_FORMAT(d.data, '%d/%m') AS dia_mes,
                COALESCE(COUNT(m.id), 0) AS total
            FROM (
                SELECT CURDATE() - INTERVAL 6 DAY AS data UNION ALL
                SELECT CURDATE() - INTERVAL 5 DAY UNION ALL
                SELECT CURDATE() - INTERVAL 4 DAY UNION ALL
                SELECT CURDATE() - INTERVAL 3 DAY UNION ALL
                SELECT CURDATE() - INTERVAL 2 DAY UNION ALL
                SELECT CURDATE() - INTERVAL 1 DAY UNION ALL
                SELECT CURDATE()
            ) d
            LEFT JOIN movimentacoes m ON DATE(m.data_retirada) = d.data
            GROUP BY d.data
            ORDER BY d.data ASC
        ")->fetchAll();
    }
}
