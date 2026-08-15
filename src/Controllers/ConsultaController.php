<?php
namespace App\Controllers;

class ConsultaController extends BaseController {
    protected $pdo;


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

        $this->render('consulta', get_defined_vars());
    }
}
