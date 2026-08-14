<?php
namespace App\Models;

class ReservaRepository extends BaseRepository {
    public function checkConflitoReserva($chave_id, $data_reserva, $hora_inicio, $hora_fim) {
        $stmt = $this->pdo->prepare("
            SELECT r.*, u.nome AS reservado_nome 
            FROM reservas r
            JOIN usuarios u ON r.usuario_id = u.id
            WHERE r.chave_id = ?
              AND r.data_reserva = ?
              AND (
                  (? BETWEEN r.hora_inicio AND r.hora_fim)
                  OR (? BETWEEN r.hora_inicio AND r.hora_fim)
                  OR (r.hora_inicio BETWEEN ? AND ?)
              )
        ");
        $stmt->execute([$chave_id, $data_reserva, $hora_inicio, $hora_fim, $hora_inicio, $hora_fim]);
        return $stmt->fetch();
    }

    public function addReserva($chave_id, $usuario_id, $data_reserva, $hora_inicio, $hora_fim) {
        $stmt = $this->pdo->prepare("
            INSERT INTO reservas (chave_id, usuario_id, data_reserva, hora_inicio, hora_fim) 
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$chave_id, $usuario_id, $data_reserva, $hora_inicio, $hora_fim]);
    }

    public function getNomeSala($chave_id) {
        $stmt = $this->pdo->prepare("SELECT nome_sala FROM chaves WHERE id = ?");
        $stmt->execute([$chave_id]);
        return $stmt->fetchColumn();
    }

    public function getNomeUsuario($usuario_id) {
        $stmt = $this->pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchColumn();
    }

    public function getReservaInfo($id) {
        $stmt = $this->pdo->prepare("
            SELECT c.nome_sala, u.nome, r.data_reserva 
            FROM reservas r
            JOIN chaves c ON r.chave_id = c.id
            JOIN usuarios u ON r.usuario_id = u.id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function deleteReserva($id) {
        $stmt = $this->pdo->prepare("DELETE FROM reservas WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAllChaves() {
        return $this->pdo->query("SELECT id, nome_sala, codigo_sala FROM chaves ORDER BY nome_sala ASC")->fetchAll();
    }

    public function getAllUsuariosAtivos() {
        return $this->pdo->query("SELECT id, nome FROM usuarios WHERE ativo = TRUE AND excluido = FALSE ORDER BY nome ASC")->fetchAll();
    }

    public function getReservasFuturas() {
        return $this->pdo->query("
            SELECT 
                r.id,
                r.data_reserva,
                DATE_FORMAT(r.hora_inicio, '%H:%i') AS inicio,
                DATE_FORMAT(r.hora_fim, '%H:%i') AS fim,
                c.nome_sala,
                c.codigo_sala,
                u.nome AS usuario_nome
            FROM reservas r
            JOIN chaves c ON r.chave_id = c.id
            JOIN usuarios u ON r.usuario_id = u.id
            WHERE r.data_reserva >= CURDATE()
            ORDER BY r.data_reserva ASC, r.hora_inicio ASC
        ")->fetchAll();
    }

    public function getReservasPassadas() {
        return $this->pdo->query("
            SELECT 
                r.id,
                r.data_reserva,
                DATE_FORMAT(r.hora_inicio, '%H:%i') AS inicio,
                DATE_FORMAT(r.hora_fim, '%H:%i') AS fim,
                c.nome_sala,
                c.codigo_sala,
                u.nome AS usuario_nome
            FROM reservas r
            JOIN chaves c ON r.chave_id = c.id
            JOIN usuarios u ON r.usuario_id = u.id
            WHERE r.data_reserva < CURDATE()
            ORDER BY r.data_reserva DESC, r.hora_inicio DESC
            LIMIT 100
        ")->fetchAll();
    }
}
