<?php
namespace App\Controllers;

use App\Models\RelatorioRepository;

class AdminRelatorioController extends AdminBaseController {
    public function index() {
        $pdo = $this->pdo;
        extract($this->config);
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

    // Exportação para PDF se solicitado
    if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
        if (ob_get_level()) {
            ob_end_clean();
        }

        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $dompdf = new \Dompdf\Dompdf();
        
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
            body { font-family: Helvetica, Arial, sans-serif; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px; }
            th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
            th { background-color: #f8fafc; color: #475569; }
            h2 { text-align: center; color: #0f172a; }
            .badge { font-weight: bold; }
            .devolvida { color: #22c55e; }
            .retirada { color: #ef4444; }
        </style></head><body>';
        $html .= '<h2>Relatório de Movimentações - ' . htmlspecialchars($nome_escola) . '</h2>';
        $html .= '<table>';
        $html .= '<thead><tr><th>Chave/Sala</th><th>Cód</th><th>Usuário</th><th>Matrícula</th><th>Retirada</th><th>Devolução</th><th>Status</th></tr></thead><tbody>';
        
        foreach ($movimentacoes as $row) {
            $dataRetirada = $row['data_retirada'] ? date('d/m/Y H:i', strtotime($row['data_retirada'])) : '';
            $dataDevolucao = $row['data_devolucao'] ? date('d/m/Y H:i', strtotime($row['data_devolucao'])) : '-';
            $status = $row['data_devolucao'] ? '<span class="badge devolvida">Devolvida</span>' : '<span class="badge retirada">Em Uso</span>';
            
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($row['nome_sala']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['codigo_sala']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['usuario_nome']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['usuario_matricula']) . '</td>';
            $html .= '<td>' . $dataRetirada . '</td>';
            $html .= '<td>' . $dataDevolucao . '</td>';
            $html .= '<td>' . $status . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table></body></html>';
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("relatorio_movimentacoes_" . date('Ymd_His') . ".pdf", ["Attachment" => true]);
        exit;
    }

} catch (\PDOException $e) {
    echo "Erro de Banco de Dados: " . $e->getMessage();
    exit;
}

        $this->render('admin_relatorio', get_defined_vars());
    }
}
