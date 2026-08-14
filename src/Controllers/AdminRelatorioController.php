<?php
namespace App\Controllers;

use App\Models\RelatorioRepository;

class AdminRelatorioController extends AdminBaseController {
    public function index() {
        global $pdo, $nome_escola, $cor_primaria, $cor_secundaria;
        
        $relatorioRepo = new RelatorioRepository($pdo);

$usuario_id = $_GET['usuario_id'] ?? null;
$chave_id = $_GET['chave_id'] ?? null;
$data_inicio = $_GET['data_inicio'] ?? null;
$data_fim = $_GET['data_fim'] ?? null;

try {
    // Carregar filtros dinâmicos
    $chaves = $relatorioRepo->getAllChaves();
    $usuarios = $relatorioRepo->getAllUsuarios();

    // Buscar Movimentações com filtros
    $movimentacoes = $relatorioRepo->getMovimentacoes($usuario_id, $chave_id, $data_inicio, $data_fim);

    // Exportação para CSV se solicitado
    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="relatorio_movimentacoes_' . date('Ymd_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeçalhos
        fputcsv($output, ['ID', 'Sala', 'Código Sala', 'Usuário', 'Matrícula', 'Data Retirada', 'Data Devolução', 'Observação'], ';');
        
        foreach ($movimentacoes as $row) {
            $dataRetirada = $row['data_retirada'] ? date('d/m/Y H:i:s', strtotime($row['data_retirada'])) : '';
            $dataDevolucao = $row['data_devolucao'] ? date('d/m/Y H:i:s', strtotime($row['data_devolucao'])) : 'Em aberto';
            
            fputcsv($output, [
                $row['id'],
                $row['nome_sala'],
                $row['codigo_sala'],
                $row['usuario_nome'],
                $row['usuario_matricula'],
                $dataRetirada,
                $dataDevolucao,
                $row['observacao']
            ], ';');
        }
        
        fclose($output);
        exit;
    }

} catch (\PDOException $e) {
    echo "Erro de Banco de Dados: " . $e->getMessage();
    exit;
}

        require_once __DIR__ . '/../Views/admin_relatorio.php';
    }
}
