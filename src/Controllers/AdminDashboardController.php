<?php
namespace App\Controllers;

use App\Models\DashboardRepository;

class AdminDashboardController extends AdminBaseController {
    public function index() {
        $pdo = $this->pdo;
        extract($this->config);
        $dashboardRepo = new DashboardRepository($pdo);

        try {
            $limite_horas = $limite_atraso_horas ?? 6;

            // 1. Obter Chaves em Uso (Atuais)
            $chavesEmUso = $dashboardRepo->getChavesEmUso($limite_horas);
        
            // 2. Estatísticas
            $totalChaves = $dashboardRepo->getTotalChaves();
            $chavesEmUsoCount = count($chavesEmUso);
            $chavesDisponiveis = $totalChaves - $chavesEmUsoCount;
        
            // Contar devoluções de hoje
            $devolucoesHoje = $dashboardRepo->getDevolucoesHoje();
        
            // Contar retiradas de hoje
            $retiradasHoje = $dashboardRepo->getRetiradasHoje();
        
            // Contar pendentes
            $pendentesCount = $dashboardRepo->getPendentesCount($limite_horas);
        
            // 3. Analytics - Top 5 salas mais utilizadas
            $topSalas = $dashboardRepo->getTopSalas();
        
            // 4. Analytics - Fluxo de retiradas nos últimos 7 dias
            $fluxoSemanal = $dashboardRepo->getFluxoSemanal();
        
        } catch (\PDOException $e) {
            echo "Erro de Banco de Dados: " . $e->getMessage();
            exit;
        }

        $this->render('admin_dashboard', get_defined_vars());
    }

    public function data() {
        $pdo = $this->pdo;
        extract($this->config);
        $dashboardRepo = new DashboardRepository($pdo);
        
        header('Content-Type: application/json');
        
        try {
            $limite_horas = $limite_atraso_horas ?? 6;
            
            $data = [
                'chavesEmUso' => $dashboardRepo->getChavesEmUso($limite_horas),
                'chavesDisponiveis' => $dashboardRepo->getTotalChaves() - count($dashboardRepo->getChavesEmUso($limite_horas)),
                'chavesEmUsoCount' => count($dashboardRepo->getChavesEmUso($limite_horas)),
                'pendentesCount' => $dashboardRepo->getPendentesCount($limite_horas),
                'retiradasHoje' => $dashboardRepo->getRetiradasHoje(),
                'devolucoesHoje' => $dashboardRepo->getDevolucoesHoje(),
                'fluxoSemanal' => $dashboardRepo->getFluxoSemanal(),
                'topSalas' => $dashboardRepo->getTopSalas()
            ];
            
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }
}
