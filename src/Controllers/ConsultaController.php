<?php
namespace App\Controllers;

class ConsultaController {
    public function index() {
        global $pdo, $nome_escola, $cor_primaria, $cor_secundaria;
        require_once __DIR__ . '/../../api/db.php';
        
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
