<?php
namespace App\Controllers;

class AdminConsultaController extends AdminBaseController {
    public function index() {
        $pdo = $this->pdo;
        extract($this->config);
        try {
    $consultaRepo = new \App\Models\ConsultaRepository($pdo);
    $chaves = $consultaRepo->buscarChavesAdmin();
} catch (\PDOException $e) {
    echo "Erro ao carregar dados: " . $e->getMessage();
    exit;
}

        require_once __DIR__ . '/../Views/admin_consulta.php';
    }
}
