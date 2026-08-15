<?php
namespace App\Controllers;

use App\Models\LogRepository;

class AdminLogsController extends AdminBaseController {
    public function index() {
        $pdo = $this->pdo;
        extract($this->config);
        // admin_logs.php

try {
    // Buscar logs de auditoria ordenados por data decrescente
    $logRepo = new LogRepository($pdo);
    $logs = $logRepo->buscarLogsRecentes(100);

    // Exportação para CSV se solicitado
    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="logs_auditoria_' . date('Ymd_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeçalhos
        fputcsv($output, ['ID', 'Ação', 'Administrador', 'Data/Hora', 'Detalhes'], ';');
        
        foreach ($logs as $row) {
            fputcsv($output, [
                $row['id'],
                $row['acao'],
                $row['admin_nome'] ?: 'Sistema/Desconhecido',
                $row['data_formatada'],
                $row['detalhes']
            ], ';');
        }
        
        fclose($output);
        exit;
    }
} catch (\PDOException $e) {
    echo "Erro de Banco de Dados: " . $e->getMessage();
    exit;
}

        require_once __DIR__ . '/../Views/admin_logs.php';
    }
}
