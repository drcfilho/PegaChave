<?php
namespace App\Models;

use PDO;

class RestricaoRepository extends BaseRepository {
    public function verificarRestricao($perfilId, $chaveId) {
        $stmt = $this->pdo->prepare("
            SELECT p.nome AS perfil_nome 
            FROM restricoes_acesso r
            JOIN perfis p ON r.perfil_id = p.id
            WHERE r.perfil_id = ? AND r.chave_id = ?
        ");
        $stmt->execute([$perfilId, $chaveId]);
        return $stmt->fetch();
    }

    public function buscarPerfis() {
        $stmt = $this->pdo->query("SELECT id, nome FROM perfis ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    public function limparRestricoes() {
        return $this->pdo->exec("DELETE FROM restricoes_acesso");
    }

    public function adicionarRestricao($perfilId, $chaveId) {
        $stmt = $this->pdo->prepare("INSERT INTO restricoes_acesso (perfil_id, chave_id) VALUES (?, ?)");
        return $stmt->execute([(int)$perfilId, (int)$chaveId]);
    }

    public function buscarTodasRestricoes() {
        $stmt = $this->pdo->query("SELECT perfil_id, chave_id FROM restricoes_acesso");
        if ($stmt) {
            return $stmt->fetchAll();
        }
        return [];
    }
}
