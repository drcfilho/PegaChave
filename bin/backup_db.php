<?php
/**
 * Script de Backup do Banco de Dados
 * Pode ser rodado via CLI (cron) ou chamado pelo painel de controle (download direto)
 */

if (php_sapi_name() !== 'cli' && !isset($called_from_web)) {
    die('Acesso negado.');
}

require_once __DIR__ . '/../api/config.php';

function generate_mysql_dump($pdo) {
    $dump = "-- Banco de Dados PegaChave\n";
    $dump .= "-- Gerado em: " . date('Y-m-d H:i:s') . "\n\n";
    $dump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

    // Pegar todas as tabelas
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        $dump .= "-- Estrutura da tabela `$table`\n";
        $dump .= "DROP TABLE IF EXISTS `$table`;\n";
        
        $createStmt = $pdo->query("SHOW CREATE TABLE `$table`");
        $createRow = $createStmt->fetch(PDO::FETCH_ASSOC);
        $dump .= $createRow['Create Table'] . ";\n\n";

        $dump .= "-- Dados da tabela `$table`\n";
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $cols = array_keys($row);
                $vals = array_values($row);
                
                // Tratar valores
                $vals_escaped = array_map(function($v) use ($pdo) {
                    if ($v === null) return 'NULL';
                    return $pdo->quote($v);
                }, $vals);
                
                $dump .= "INSERT INTO `$table` (`" . implode("`, `", $cols) . "`) VALUES (" . implode(", ", $vals_escaped) . ");\n";
            }
        }
        $dump .= "\n\n";
    }

    $dump .= "SET FOREIGN_KEY_CHECKS=1;\n";
    return $dump;
}

if (php_sapi_name() === 'cli') {
    $dump = generate_mysql_dump($pdo);
    $backupDir = __DIR__ . '/../backups';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    $filename = $backupDir . '/backup_pegachave_' . date('Y_m_d_His') . '.sql';
    file_put_contents($filename, $dump);
    
    // Rotação de logs: apagar backups com mais de 7 dias
    $files = glob($backupDir . '/backup_pegachave_*.sql');
    $now   = time();
    foreach ($files as $file) {
        if (is_file($file)) {
            if ($now - filemtime($file) >= 60 * 60 * 24 * 7) { 
                unlink($file);
            }
        }
    }
    
    echo "Backup salvo com sucesso em: $filename\n";
}
