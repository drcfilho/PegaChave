<?php
namespace App\Controllers;

use Phinx\Console\PhinxApplication;
use Phinx\Wrapper\TextWrapper;

class InstallController extends BaseController {
    public function index() {
        $pdo = $this->pdo;
        
        // Verificar se já está instalado
        $isInstalled = false;
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM phinxlog");
            if ($stmt !== false) {
                $isInstalled = true; // A tabela do Phinx já existe
            }
        } catch (\Exception $e) {
            $isInstalled = false;
        }

        if ($isInstalled) {
            // Se já tem log do phinx, verifica se há administradores
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM administradores");
                $count = $stmt->fetchColumn();
                if ($count > 0) {
                    die("Sistema já está instalado! Acesse <a href='" . BASE_URL . "/admin'>/admin</a>");
                }
            } catch (\Exception $e) {}
        }
        
        $this->render('install', ['isInstalled' => $isInstalled]);
    }

    public function runInstall() {
        // Roda a migração
        $app = new PhinxApplication();
        $wrap = new TextWrapper($app, [
            'configuration' => __DIR__ . '/../../phinx.php',
            'parser' => 'php'
        ]);

        $output = $wrap->getMigrate();
        
        // Verifica admin padrão
        $pdo = $this->pdo;
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM administradores");
            $count = $stmt->fetchColumn();
            
            if ($count == 0) {
                $senhaHash = password_hash('admin', PASSWORD_DEFAULT);
                $stmtInsert = $pdo->prepare("INSERT INTO administradores (usuario, senha, nome, role, permissoes) VALUES (?, ?, ?, ?, ?)");
                $stmtInsert->execute(['admin', $senhaHash, 'Administrador Geral', 'admin_master', '["all"]']);
                $output .= "\nAdministrador padrão cadastrado: Usuário 'admin' / Senha 'admin'";
            }
        } catch (\Exception $e) {
            $output .= "\nErro ao cadastrar administrador: " . $e->getMessage();
        }

        echo json_encode(["status" => "success", "log" => $output]);
        exit;
    }
}
