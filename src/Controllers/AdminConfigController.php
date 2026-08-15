<?php
namespace App\Controllers;

use App\Models\ConfigRepository;

class AdminConfigController extends AdminBaseController {
    public function index() {
        $pdo = $this->pdo;
        extract($this->config);
        $message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (false) {
        // Redundant block removed since CSRF is handled via middleware
    } elseif (isset($_POST['action']) && $_POST['action'] === 'restore_backup') {
        if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['backup_file']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['backup_file']['name'], PATHINFO_EXTENSION));
            
            $sqlContent = '';
            
            if ($ext === 'zip') {
                if (class_exists('ZipArchive')) {
                    $zip = new \ZipArchive();
                    if ($zip->open($tmpName) === TRUE) {
                        for ($i = 0; $i < $zip->numFiles; $i++) {
                            $filename = $zip->getNameIndex($i);
                            if (pathinfo($filename, PATHINFO_EXTENSION) === 'sql') {
                                $sqlContent = $zip->getFromIndex($i);
                                break;
                            }
                        }
                        $zip->close();
                        if (empty($sqlContent)) {
                            $message = "Nenhum arquivo SQL encontrado dentro do arquivo ZIP.";
                            $messageType = "error";
                        }
                    } else {
                        $message = "Erro ao abrir o arquivo ZIP.";
                        $messageType = "error";
                    }
                } else {
                    $message = "A extensão ZipArchive não está habilitada no servidor. Por favor, extraia o arquivo e envie o arquivo .sql diretamente.";
                    $messageType = "error";
                }
            } else {
                $sqlContent = file_get_contents($tmpName);
            }

            if (!empty($sqlContent)) {
                try {
                    // Executa todas as queries do arquivo SQL
                    $pdo->exec($sqlContent);
                    registrar_log($pdo, 'Restauração de Backup', "O banco de dados foi restaurado com sucesso a partir de um backup.");
                    $message = "Banco de dados restaurado com sucesso!";
                    $messageType = "success";
                } catch (\PDOException $e) {
                    $message = "Erro ao restaurar o banco: " . $e->getMessage();
                    $messageType = "error";
                }
            } elseif (empty($message)) {
                $message = "Arquivo SQL vazio ou inválido.";
                $messageType = "error";
            }
        } else {
            $message = "Erro no upload do arquivo de backup.";
            $messageType = "error";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'download_backup') {
        $called_from_web = true;
        require_once __DIR__ . '/../../bin/backup_db.php';
        $dump = generate_mysql_dump($pdo);
        
        if (class_exists('ZipArchive')) {
            $zip = new \ZipArchive();
            $zipFilename = sys_get_temp_dir() . '/backup_pegachave_' . date('Ymd_His') . '.zip';
            
            if ($zip->open($zipFilename, \ZipArchive::CREATE) === TRUE) {
                $zip->addFromString('backup_pegachave_' . date('Ymd_His') . '.sql', $dump);
                $zip->close();
                
                if (ob_get_level()) {
                    ob_end_clean();
                }
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="backup_pegachave_' . date('Ymd_His') . '.zip"');
                header('Content-Length: ' . filesize($zipFilename));
                readfile($zipFilename);
                unlink($zipFilename);
                exit;
            }
        }
        
        // Fallback para arquivo SQL simples (quando ZipArchive falha ou não existe)
        if (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/sql; charset=UTF-8');
        header('Content-Disposition: attachment; filename="backup_pegachave_' . date('Ymd_His') . '.sql"');
        echo $dump;
        exit;
    } elseif (isset($_POST['action']) && $_POST['action'] === 'update_config') {
    $nome_input = $_POST['nome_escola'] ?? 'Escola Lumiar';
    $cor_p_input = $_POST['cor_primaria'] ?? '#0284c7';
    $cor_s_input = $_POST['cor_secundaria'] ?? '#0f172a';
    $modo_portaria_input = $_POST['modo_portaria'] ?? 'geral';
    $limite_input = filter_var($_POST['limite_chaves'] ?? '0', FILTER_VALIDATE_INT);
    if ($limite_input === false || $limite_input < 0) {
        $limite_input = 0;
    }
    
    $limite_atraso_input = filter_var($_POST['limite_atraso_horas'] ?? '0', FILTER_VALIDATE_INT);
    if ($limite_atraso_input === false || $limite_atraso_input < 0) {
        $limite_atraso_input = 0;
    }

    try {
        $configRepo = new ConfigRepository($pdo);
        $configRepo->salvarOuAtualizar('nome_escola', $nome_input);
        $configRepo->salvarOuAtualizar('cor_primaria', $cor_p_input);
        $configRepo->salvarOuAtualizar('cor_secundaria', $cor_s_input);
        $configRepo->salvarOuAtualizar('limite_chaves', $limite_input);
        $configRepo->salvarOuAtualizar('modo_portaria', $modo_portaria_input);
        $configRepo->salvarOuAtualizar('limite_atraso_horas', $limite_atraso_input);

        registrar_log($pdo, 'Alteração de Configuração', "Nome: '$nome_input', Primária: '$cor_p_input', Secundária: '$cor_s_input', Limite Chaves: $limite_input, Modo Portaria: '$modo_portaria_input', Limite Atraso Horas: $limite_atraso_input.");

        $message = "Configurações atualizadas com sucesso!";
        $messageType = "success";

        // Recarregar variáveis locais após alteração
        $nome_escola = $nome_input;
        $cor_primaria = $cor_p_input;
        $cor_secundaria = $cor_s_input;
        $limite_chaves = $limite_input;
        $modo_portaria = $modo_portaria_input;
        $limite_atraso_horas = $limite_atraso_input;

    } catch (\PDOException $e) {
        $message = "Erro ao salvar configurações: " . $e->getMessage();
        $messageType = "error";
    }
  }
}

        $this->render('admin_config', get_defined_vars());
    }
}
