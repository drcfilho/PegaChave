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

        $pdo = $this->pdo;
        $chaveRepository = new ChaveRepository($pdo);

        try {
            $resultado = $chaveRepository->salvarNovaChave($_POST);
            registrar_log($pdo, 'Cadastro de Chave', "Chave '{$resultado['nome_sala']}' ({$resultado['codigo_sala']}) cadastrada. Bloco: '{$resultado['bloco']}', Andar: '{$resultado['andar']}'. Restrita para: '{$resultado['matriculas_permitidas']}'. Hash automático: {$resultado['qr_code_hash']}");
            
            return $this->index("Chave '{$resultado['nome_sala']}' cadastrada com sucesso!", "success");
        } catch (\PDOException $e) {
            return $this->index("Erro ao salvar dados: " . $e->getMessage(), "error");
        } catch (Exception $e) {
            return $this->index($e->getMessage(), "error");
        }
    }

    public function update() {

        $pdo = $this->pdo;
        $chaveRepository = new ChaveRepository($pdo);

        try {
            $resultado = $chaveRepository->salvarAtualizacaoChave($_POST);
            registrar_log($pdo, 'Edição de Chave', "Chave ID {$resultado['id']} atualizada para '{$resultado['nome_sala']}' ({$resultado['codigo_sala']}). Bloco: '{$resultado['bloco']}', Andar: '{$resultado['andar']}'. Restrita para: '{$resultado['matriculas_permitidas']}'. Hash automático: {$resultado['qr_code_hash']}");
            
            return $this->index("Chave atualizada com sucesso!", "success");
        } catch (\PDOException $e) {
            return $this->index("Erro ao salvar dados: " . $e->getMessage(), "error");
        } catch (Exception $e) {
            return $this->index($e->getMessage(), "error");
        }
    }

    public function destroy() {
        $pdo = $this->pdo;
        $chaveRepository = new ChaveRepository($pdo);

        try {
            $id = $_POST['id'] ?? '';
            $chaveInfo = $chaveRepository->buscarPorId($id);
            $nomeExcluido = $chaveInfo ? $chaveInfo['nome_sala'] . ' (' . $chaveInfo['codigo_sala'] . ')' : "ID $id";

            $chaveRepository->excluirPorId($id);
            
            registrar_log($pdo, 'Remoção de Chave', "Chave '$nomeExcluido' foi removida.");
            
            return $this->index("Chave removida.", "success");
        } catch (\PDOException $e) {
            return $this->index("Erro ao excluir dados: " . $e->getMessage(), "error");
        } catch (Exception $e) {
            return $this->index($e->getMessage(), "error");
        }
    }
}
