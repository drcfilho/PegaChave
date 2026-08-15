<?php
require_once __DIR__ . '/api/db.php';
try {
    $stmt = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES ('cor_primaria', '#2f9e41') ON DUPLICATE KEY UPDATE valor = '#2f9e41'");
    $stmt->execute();
    echo "Cor primaria atualizada com sucesso para #2f9e41 (IFCE Verde)!\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
