<?php
namespace App\Controllers;

use App\Models\ChaveRepository;
use App\Models\RestricaoRepository;
use Exception;

class AdminRestricoesController extends AdminBaseController {
    public function index() {
        $pdo = $this->pdo;
        extract($this->config);
        $chaveRepository = new ChaveRepository($pdo);
        $restricaoRepository = new RestricaoRepository($pdo);

$message = '';
$messageType = '';

// Buscar perfis e chaves
try {
    $perfis = $restricaoRepository->buscarPerfis();
    $chaves = $chaveRepository->buscarTodas();
} catch (\PDOException $e) {
    echo "Erro ao carregar dados: " . $e->getMessage();
    exit;
}

// Processar formulário de atualização de restrições
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validar_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = "Token de segurança inválido. Tente novamente.";
        $messageType = "error";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'save_restrictions') {
        try {
            $pdo->beginTransaction();

            // Limpar restrições anteriores
            $restricaoRepository->limparRestricoes();

            // Inserir novas restrições enviadas
            $bloqueios = $_POST['bloqueio'] ?? []; // Array bidimensional [perfil_id][chave_id]

            $count = 0;
            foreach ($bloqueios as $perfil_id => $chaves_bloqueadas) {
                foreach ($chaves_bloqueadas as $chave_id => $val) {
                    $restricaoRepository->adicionarRestricao((int)$perfil_id, (int)$chave_id);
                    $count++;
                }
            }

            $pdo->commit();
            registrar_log($pdo, 'Configuração de Restrições', "Matriz de restrições de acesso atualizada. Total de bloqueios: $count.");
            
            $message = "Restrições de acesso salvas com sucesso!";
            $messageType = "success";
        } catch (\PDOException $e) {
            $pdo->rollBack();
            $message = "Erro ao salvar restrições: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Carregar restrições atuais para preencher a matriz
$restricoes = [];
try {
    $restricoesDB = $restricaoRepository->buscarTodasRestricoes();
    foreach ($restricoesDB as $row) {
        $restricoes[$row['perfil_id']][$row['chave_id']] = true;
    }
} catch (\Exception $e) {
    // Silencioso se tabela não existir
}

        require_once __DIR__ . '/../Views/admin_restricoes.php';
    }
}
