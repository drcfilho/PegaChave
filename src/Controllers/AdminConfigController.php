<?php
namespace App\Controllers;

use App\Models\ConfigRepository;

class AdminConfigController extends AdminBaseController {
    public function index() {
        global $pdo, $nome_escola, $cor_primaria, $cor_secundaria;
        




$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validar_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = "Token de segurança inválido. Tente novamente.";
        $messageType = "error";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'update_config') {
    $nome_input = $_POST['nome_escola'] ?? 'Escola Lumiar';
    $cor_p_input = $_POST['cor_primaria'] ?? '#0284c7';
    $cor_s_input = $_POST['cor_secundaria'] ?? '#0f172a';
    $limite_input = filter_var($_POST['limite_chaves'] ?? '0', FILTER_VALIDATE_INT);
    if ($limite_input === false || $limite_input < 0) {
        $limite_input = 0;
    }

    try {
        $configRepo = new ConfigRepository($pdo);
        $configRepo->salvarOuAtualizar('nome_escola', $nome_input);
        $configRepo->salvarOuAtualizar('cor_primaria', $cor_p_input);
        $configRepo->salvarOuAtualizar('cor_secundaria', $cor_s_input);
        $configRepo->salvarOuAtualizar('limite_chaves', $limite_input);

        registrar_log($pdo, 'Alteração de Configuração', "Nome da Escola: '$nome_input', Cor Primária: '$cor_p_input', Cor Secundária: '$cor_s_input', Limite de Chaves: $limite_input.");

        $message = "Configurações atualizadas com sucesso!";
        $messageType = "success";

        // Recarregar variáveis locais após alteração
        $nome_escola = $nome_input;
        $cor_primaria = $cor_p_input;
        $cor_secundaria = $cor_s_input;
        $limite_chaves = $limite_input;

    } catch (\PDOException $e) {
        $message = "Erro ao salvar configurações: " . $e->getMessage();
        $messageType = "error";
    }
  }
}

        require_once __DIR__ . '/../Views/admin_config.php';
    }
}
