<?php
namespace App\Models;

use PDO;

class LogRepository extends BaseRepository {
    public function buscarLogsRecentes($limite = 100) {
        $query = "
            SELECT 
                l.id, 
                l.acao, 
                l.detalhes, 
                DATE_FORMAT(l.criado_em, '%d/%m/%Y %H:%i:%s') AS data_formatada,
                a.nome AS admin_nome
            FROM logs_auditoria l
            LEFT JOIN administradores a ON l.admin_id = a.id
            ORDER BY l.criado_em DESC
            LIMIT " . (int)$limite . "
        ";
        return $this->pdo->query($query)->fetchAll();
    }
}
