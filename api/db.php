<?php
// api/db.php


$host = '127.0.0.1';
$db   = 'controle_chaves';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=3306";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     
     // Carregar configurações globais
     $nome_escola = 'Escola Lumiar';
     $cor_primaria = '#0284c7';
     $cor_secundaria = '#0f172a';
     
     try {
         $stmtConfig = $pdo->query("SELECT chave, valor FROM configuracoes");
         if ($stmtConfig) {
             $configs = $stmtConfig->fetchAll(PDO::FETCH_KEY_PAIR);
             if (isset($configs['nome_escola'])) $nome_escola = $configs['nome_escola'];
             if (isset($configs['cor_primaria'])) $cor_primaria = $configs['cor_primaria'];
             if (isset($configs['cor_secundaria'])) $cor_secundaria = $configs['cor_secundaria'];
         }
     } catch (\Exception $exConfig) {
         // Fallback silencioso se a tabela de configurações não existir
     }
} catch (\PDOException $e) {
     echo json_encode(["status" => "error", "message" => "Erro na conexão com o banco de dados: " . $e->getMessage()]);
     exit;
}
