<?php
namespace App\Controllers;

class ConsultaController {
    protected $pdo;
    protected $config;

    public function __construct($pdo, $config) {
        $this->pdo = $pdo;
        $this->config = $config;
    }

    public function index() {
        $pdo = $this->pdo;
        extract($this->config);
        try {
            $consultaRepo = new \App\Models\ConsultaRepository($pdo);
            $chaves = $consultaRepo->buscarChavesPublico();
            
            $reservas = $consultaRepo->buscarReservasHoje();
            $reservasHoje = [];
            foreach ($reservas as $res) {
                $reservasHoje[$res['chave_id']][] = $res;
            }
        
        } catch (\PDOException $e) {
            echo "Erro ao carregar dados: " . $e->getMessage();
            exit;
        }

        require_once __DIR__ . '/../Views/consulta.php';
    }
}
