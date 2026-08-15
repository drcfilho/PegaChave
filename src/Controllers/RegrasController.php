<?php
namespace App\Controllers;

class RegrasController extends BaseController {
    protected $pdo;


    public function index() {
        $pdo = $this->pdo;
        extract($this->config);
        $this->render('regras', get_defined_vars());
    }
}
