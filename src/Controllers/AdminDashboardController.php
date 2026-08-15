<?php
namespace App\Controllers;

use App\Models\DashboardRepository;

class AdminDashboardController extends AdminBaseController {
    public function index() {
        $pdo = $this->pdo;
        extract($this->config);
        $dashboardRepo = new DashboardRepository($pdo);

        try {
            // 1. Obter Chaves em Uso (Atuais)
            $chavesEmUso = $dashboardRepo->getChavesEmUso();
        
            // 2. Estatísticas
            $totalChaves = $dashboardRepo->getTotalChaves();
            $chavesEmUsoCount = count($chavesEmUso);
            $chavesDisponiveis = $totalChaves - $chavesEmUsoCount;
        
            // Contar devoluções de hoje
            $devolucoesHoje = $dashboardRepo->getDevolucoesHoje();
        
            // Contar retiradas de hoje
            $retiradasHoje = $dashboardRepo->getRetiradasHoje();
        
            // Contar pendentes
            $pendentesCount = $dashboardRepo->getPendentesCount();
        
            // 3. Analytics - Top 5 salas mais utilizadas
            $topSalas = $dashboardRepo->getTopSalas();
        
            // 4. Analytics - Fluxo de retiradas nos últimos 7 dias
            $fluxoSemanal = $dashboardRepo->getFluxoSemanal();
        
        } catch (\PDOException $e) {
            echo "Erro de Banco de Dados: " . $e->getMessage();
            exit;
        }

        require_once __DIR__ . '/../Views/admin_dashboard.php';
    }
}
