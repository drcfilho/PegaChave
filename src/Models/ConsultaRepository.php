<?php
namespace App\Models;

use PDO;

class ConsultaRepository extends BaseRepository {
    public function buscarChavesAdmin() {
        $query = "
            SELECT 
                c.id, 
                c.nome_sala, 
                c.codigo_sala, 
                c.status_disponivel, 
                c.descricao,
                u.nome AS reservado_por_nome, 
                p.nome AS reservado_por_perfil,
                DATE_FORMAT(m.data_retirada, '%d/%m/%Y às %H:%i') AS data_retirada_formatada
            FROM chaves c
            LEFT JOIN movimentacoes m ON c.id = m.chave_id AND m.data_devolucao IS NULL
            LEFT JOIN usuarios u ON m.usuario_id = u.id
            LEFT JOIN perfis p ON u.perfil_id = p.id
            ORDER BY c.nome_sala ASC
        ";
        return $this->pdo->query($query)->fetchAll();
    }

    public function buscarChavesPublico() {
        $query = "
            SELECT 
                c.id, 
                c.nome_sala, 
                c.bloco,
                c.andar,
                c.codigo_sala, 
                c.status_disponivel, 
                c.descricao,
                u.nome AS reservado_por_nome, 
                p.nome AS reservado_por_perfil,
                DATE_FORMAT(m.data_retirada, '%d/%m/%Y às %H:%i') AS data_retirada_formatada
            FROM chaves c
            LEFT JOIN movimentacoes m ON c.id = m.chave_id AND m.data_devolucao IS NULL
            LEFT JOIN usuarios u ON m.usuario_id = u.id
            LEFT JOIN perfis p ON u.perfil_id = p.id
            ORDER BY c.nome_sala ASC
        ";
        return $this->pdo->query($query)->fetchAll();
    }

    public function buscarReservasHoje() {
        $query = "
            SELECT r.chave_id, r.hora_inicio, r.hora_fim, u.nome AS reservado_nome
            FROM reservas r
            JOIN usuarios u ON r.usuario_id = u.id
            WHERE r.data_reserva = CURDATE()
            ORDER BY r.hora_inicio ASC
        ";
        return $this->pdo->query($query)->fetchAll();
    }
}
