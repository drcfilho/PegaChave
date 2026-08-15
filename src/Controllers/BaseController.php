<?php
namespace App\Controllers;

class BaseController {
    protected $pdo;
    protected $config;
    protected $blade;

    public function __construct($pdo, $config, $blade = null) {
        $this->pdo = $pdo;
        $this->config = $config;
        $this->blade = $blade;
    }

    protected function render($view, $data = []) {
        if (!$this->blade) {
            throw new \Exception("Template Engine não configurado.");
        }
        
        $globalData = [
            'BASE_URL' => defined('BASE_URL') ? BASE_URL : '',
            'nome_escola' => $this->config['nome_escola'] ?? '',
            'cor_primaria' => $this->config['cor_primaria'] ?? '',
            'cor_secundaria' => $this->config['cor_secundaria'] ?? '',
        ];
        
        echo $this->blade->run($view, array_merge($globalData, $data));
        exit;
    }
}
