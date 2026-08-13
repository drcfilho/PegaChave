<?php
// scratch/register_admin.php
require_once __DIR__ . '/../api/db.php';

$usuario = 'admin';
$senha = 'admin';
$nome = 'Administrador Geral';

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO administradores (usuario, senha, nome) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE senha = VALUES(senha)");
    $stmt->execute([$usuario, $senhaHash, $nome]);
    echo "Usuário 'admin' registrado/atualizado com sucesso com a senha 'admin'!\n";
} catch (\Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
