<?php
namespace App\Core\Middlewares;

class AuthMiddleware {
    public function handle($perm = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header("Location: " . BASE_URL . "/admin_login.php");
            exit;
        }

        if ($perm) {
            $role = $_SESSION['admin_role'] ?? 'admin_master';
            if ($role !== 'admin_master') {
                $perms = $_SESSION['admin_permissoes'] ?? [];
                if (!is_array($perms) || !in_array($perm, $perms)) {
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        http_response_code(403);
                        echo json_encode(['status' => 'error', 'message' => 'Acesso negado. Você não tem permissão para esta ação.']);
                        exit;
                    }
                    
                    echo "<div style='font-family:sans-serif; text-align:center; padding:50px; background:#f8fafc; height:100vh;'>";
                    echo "<h1 style='color:#ef4444;'>⛔ Acesso Negado</h1>";
                    echo "<p>Você não tem permissão para acessar esta área do painel.</p>";
                    echo "<a href='" . BASE_URL . "/admin' style='display:inline-block; margin-top:20px; padding:10px 20px; background:#0284c7; color:white; text-decoration:none; border-radius:8px;'>Voltar ao Início</a>";
                    echo "</div>";
                    exit;
                }
            }
        }

        return true;
    }
}
