<?php
// api/movimentacoes.php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/db.php';

$usuario_id = $_GET['usuario_id'] ?? null;
$chave_id = $_GET['chave_id'] ?? null;
$data_inicio = $_GET['data_inicio'] ?? null;
$data_fim = $_GET['data_fim'] ?? null;

try {
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

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $movimentacoes = $stmt->fetchAll();

    echo json_encode([
        "status" => "success",
        "data" => $movimentacoes
    ]);

} catch (\PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Erro ao obter histórico de movimentações: " . $e->getMessage()
    ]);
}
