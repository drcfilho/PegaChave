<?php
namespace App\Controllers;

use App\Models\ChaveRepository;
use Exception;

class AdminChavesController extends AdminBaseController {
    public function index($message = '', $messageType = '') {
        $pdo = $this->pdo;
        extract($this->config);
        $chaveRepository = new ChaveRepository($pdo);

        try {
            $chaves = $chaveRepository->buscarTodasOrdenadasPorCodigo();
        } catch (\PDOException $e) {
            echo "Erro de Banco de Dados: " . $e->getMessage();
            exit;
        }

        require_once __DIR__ . '/../Views/admin_chaves.php';
    }

    public function store() {
        if (!validar_csrf_token($_POST['csrf_token'] ?? '')) {
            return $this->index("Token de segurança inválido. Tente novamente.", "error");
        }

        $pdo = $this->pdo;
        $chaveRepository = new ChaveRepository($pdo);

        try {
            $nome_sala = trim($_POST['nome_sala'] ?? '');
            $bloco = trim($_POST['bloco'] ?? '');
            $andar = trim($_POST['andar'] ?? '');
            $matriculas_permitidas = trim($_POST['matriculas_permitidas'] ?? '');
            $codigo_sala = trim($_POST['codigo_sala'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');

            $nome_sala = htmlspecialchars($nome_sala, ENT_QUOTES, 'UTF-8');
            $bloco = htmlspecialchars($bloco, ENT_QUOTES, 'UTF-8');
            $andar = htmlspecialchars($andar, ENT_QUOTES, 'UTF-8');
            $matriculas_permitidas = htmlspecialchars($matriculas_permitidas, ENT_QUOTES, 'UTF-8');
            $codigo_sala = preg_replace('/[^a-zA-Z0-9_-]/', '', $codigo_sala);
            $descricao = htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8');

            $qr_code_hash = 'chaves_' . $codigo_sala;

            if (empty($nome_sala) || empty($codigo_sala) || empty($qr_code_hash)) {
                throw new Exception("Por favor, preencha todos os campos obrigatórios.");
            }

            $chaveRepository->criar($nome_sala, $bloco, $andar, $matriculas_permitidas, $codigo_sala, $qr_code_hash, $descricao);
            registrar_log($pdo, 'Cadastro de Chave', "Chave '$nome_sala' ($codigo_sala) cadastrada. Bloco: '$bloco', Andar: '$andar'. Restrita para: '$matriculas_permitidas'. Hash automático: $qr_code_hash");
            
            return $this->index("Chave '$nome_sala' cadastrada com sucesso!", "success");
        } catch (\PDOException $e) {
            return $this->index("Erro ao salvar dados: " . $e->getMessage(), "error");
        } catch (Exception $e) {
            return $this->index($e->getMessage(), "error");
        }
    }

    public function update() {
        if (!validar_csrf_token($_POST['csrf_token'] ?? '')) {
            return $this->index("Token de segurança inválido. Tente novamente.", "error");
        }

        $pdo = $this->pdo;
        $chaveRepository = new ChaveRepository($pdo);

        try {
            $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
            $nome_sala = trim($_POST['nome_sala'] ?? '');
            $bloco = trim($_POST['bloco'] ?? '');
            $andar = trim($_POST['andar'] ?? '');
            $matriculas_permitidas = trim($_POST['matriculas_permitidas'] ?? '');
            $codigo_sala = trim($_POST['codigo_sala'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');

            $nome_sala = htmlspecialchars($nome_sala, ENT_QUOTES, 'UTF-8');
            $bloco = htmlspecialchars($bloco, ENT_QUOTES, 'UTF-8');
            $andar = htmlspecialchars($andar, ENT_QUOTES, 'UTF-8');
            $matriculas_permitidas = htmlspecialchars($matriculas_permitidas, ENT_QUOTES, 'UTF-8');
            $codigo_sala = preg_replace('/[^a-zA-Z0-9_-]/', '', $codigo_sala);
            $descricao = htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8');

            $qr_code_hash = 'chaves_' . $codigo_sala;

            if (!$id || empty($nome_sala) || empty($codigo_sala) || empty($qr_code_hash)) {
                throw new Exception("Todos os campos obrigatórios devem ser preenchidos.");
            }

            $chaveRepository->atualizar($nome_sala, $bloco, $andar, $matriculas_permitidas, $codigo_sala, $qr_code_hash, $descricao, $id);
            registrar_log($pdo, 'Edição de Chave', "Chave ID $id atualizada para '$nome_sala' ($codigo_sala). Bloco: '$bloco', Andar: '$andar'. Restrita para: '$matriculas_permitidas'. Hash automático: $qr_code_hash");
            
            return $this->index("Chave atualizada com sucesso!", "success");
        } catch (\PDOException $e) {
            return $this->index("Erro ao salvar dados: " . $e->getMessage(), "error");
        } catch (Exception $e) {
            return $this->index($e->getMessage(), "error");
        }
    }

    public function destroy() {
        if (!validar_csrf_token($_POST['csrf_token'] ?? '')) {
            return $this->index("Token de segurança inválido. Tente novamente.", "error");
        }

        $pdo = $this->pdo;
        $chaveRepository = new ChaveRepository($pdo);

        try {
            $id = $_POST['id'] ?? '';
            $chaveInfo = $chaveRepository->buscarPorId($id);
            $nomeExcluido = $chaveInfo ? $chaveInfo['nome_sala'] . ' (' . $chaveInfo['codigo_sala'] . ')' : "ID $id";

            $chaveRepository->excluirMovimentacoesPorChaveId($id);
            $chaveRepository->excluirPorId($id);
            
            registrar_log($pdo, 'Remoção de Chave', "Chave '$nomeExcluido' e seu histórico foram removidos.");
            
            return $this->index("Chave removida.", "success");
        } catch (\PDOException $e) {
            return $this->index("Erro ao excluir dados: " . $e->getMessage(), "error");
        } catch (Exception $e) {
            return $this->index($e->getMessage(), "error");
        }
    }
}
