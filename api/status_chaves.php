<?php
// api/status_chaves.php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/db.php';

try {
    $query = "
        SELECT 
            c.id, 
            c.nome_sala, 
            c.codigo_sala, 
            c.status_disponivel, 
            c.descricao,
            c.qr_code_hash,
            u.nome AS reservado_por_nome, 
            m.data_retirada
        FROM chaves c
        LEFT JOIN movimentacoes m ON c.id = m.chave_id AND m.data_devolucao IS NULL
        LEFT JOIN usuarios u ON m.usuario_id = u.id
        ORDER BY c.nome_sala ASC
    ";

    $stmt = $pdo->query($query);
    $chaves = $stmt->fetchAll();

    echo json_encode([
        "status" => "success",
        "data" => $chaves
    ]);

} catch (\PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Erro ao obter status das chaves: " . $e->getMessage()
    ]);
}
