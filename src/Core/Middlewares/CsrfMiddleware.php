<?php
namespace App\Core\Middlewares;

class CsrfMiddleware {
    public function handle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!function_exists('validar_csrf_token')) {
                require_once __DIR__ . '/../../../api/db.php';
            }
            if (!validar_csrf_token($_POST['csrf_token'] ?? '')) {
                // Optionally redirect or show a specific error view
                die("Falha de segurança: Token CSRF inválido. Por favor, volte e tente novamente.");
            }
        }
        return true;
    }
}
