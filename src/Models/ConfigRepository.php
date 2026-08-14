<?php
namespace App\Models;

use PDO;

class ConfigRepository extends BaseRepository {
    public function salvarOuAtualizar($chave, $valor) {
        $stmt = $this->pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
        return $stmt->execute([$chave, $valor]);
    }
}
