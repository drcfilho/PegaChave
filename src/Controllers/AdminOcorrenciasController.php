<?php
namespace App\Controllers;

use App\Models\RelatorioRepository;

class AdminOcorrenciasController extends AdminBaseController {
    public function index() {
        $pdo = $this->pdo;
        extract($this->config);
        $relatorioRepo = new RelatorioRepository($pdo);

        $usuario_id = $_GET['usuario_id'] ?? null;
        $chave_id = $_GET['chave_id'] ?? null;
        $data_inicio = $_GET['data_inicio'] ?? null;
        $data_fim = $_GET['data_fim'] ?? null;

        // Se nenhum filtro foi enviado, limita a visualização às últimas 30 ocorrências
        $limite = (empty($usuario_id) && empty($chave_id) && empty($data_inicio) && empty($data_fim)) ? 30 : null;

        try {
            // Carregar filtros dinâmicos
            $chaves = $relatorioRepo->getAllChaves();
            $usuarios = $relatorioRepo->getAllUsuarios();

            // Buscar Ocorrências com filtros
            $ocorrencias = $relatorioRepo->getOcorrencias($usuario_id, $chave_id, $data_inicio, $data_fim, $limite);

            // Exportação para CSV se solicitado
            if (isset($_GET['export']) && $_GET['export'] === 'csv') {
                if (ob_get_level()) {
                    ob_end_clean();
                }
                
                header('Content-Type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename="relatorio_ocorrencias_' . date('Ymd_His') . '.csv"');
                
                $output = fopen('php://output', 'w');
                
                // UTF-8 BOM
                fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // Cabeçalhos
                fputcsv($output, ['ID', 'Sala', 'Código Sala', 'Possuidor da Chave', 'Matrícula', 'Data Retirada', 'Data Devolução', 'Ocorrência'], ';');
                
                foreach ($ocorrencias as $row) {
                    $dataRetirada = $row['data_retirada'] ? date('d/m/Y H:i:s', strtotime($row['data_retirada'])) : '';
                    $dataDevolucao = $row['data_devolucao'] ? date('d/m/Y H:i:s', strtotime($row['data_devolucao'])) : '';
                    
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

        $this->render('admin_ocorrencias', get_defined_vars());
    }
}
