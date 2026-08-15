<?php
/**
 * Robô de Verificação de Atrasos
 * Deve ser executado via Cron Job / Tarefa Agendada do Windows
 * Exemplo: php bin/alertar_atrasos.php
 */

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../api/db.php'; // Para ter acesso ao $pdo e outras funções

use App\Models\MovimentacaoRepository;
use App\Core\EmailService;
use eftec\bladeone\BladeOne;

echo "=== Iniciando Verificação de Chaves em Atraso ===\n";

try {
    $movRepo = new MovimentacaoRepository($pdo);
    $emailService = new EmailService();

    // Configuração do BladeOne para renderizar o e-mail
    $views = __DIR__ . '/../src/Views';
    $cache = __DIR__ . '/../cache';
    $blade = new BladeOne($views, $cache, BladeOne::MODE_AUTO);

    // Buscar movimentações sem devolução iniciadas há mais de X horas
    $limiteHoras = $limite_atraso_horas ?? 6;
    
    if ($limiteHoras <= 0) {
        echo "Sistema de alertas de atraso está desativado (limite = 0).\n";
        exit(0);
    }
    
    $atrasadas = $movRepo->buscarAtrasadas($limiteHoras);

    if (empty($atrasadas)) {
        echo "Nenhuma chave em atraso encontrada (limite: $limiteHoras horas).\n";
        exit(0);
    }

    echo "Encontrada(s) " . count($atrasadas) . " chave(s) em atraso com e-mail cadastrado.\n";

    // Pasta de logs de alertas
    $logsDir = dirname(__DIR__) . '/tmp';
    if (!is_dir($logsDir)) {
        mkdir($logsDir, 0777, true);
    }
    $logFile = $logsDir . '/email_alerts.log';

    $enviados = 0;
    $falhas = 0;

    foreach ($atrasadas as $registro) {
        $destinatario = $registro['usuario_email'];
        $nome = $registro['usuario_nome'];
        $sala = $registro['nome_sala'];
        
        $dataRetirada = new DateTime($registro['data_retirada']);
        $agora = new DateTime();
        $diferenca = $dataRetirada->diff($agora);
        $horasTotais = ($diferenca->days * 24) + $diferenca->h;

        // Prepara as variáveis para o Blade
        $dadosTemplate = [
            'nome' => $nome,
            'sala' => $sala,
            'data_retirada' => $dataRetirada->format('d/m/Y H:i'),
            'tempo_posse' => $horasTotais
        ];

        try {
            $corpoHtml = $blade->run("emails.alerta_atraso", $dadosTemplate);
            $assunto = "⚠️ PegaChave - Alerta de Chave Pendente: Sala $sala";
            
            echo "Enviando e-mail para $destinatario... ";
            
            if ($emailService->enviar($destinatario, $nome, $assunto, $corpoHtml)) {
                echo "ENVIADO!\n";
                $enviados++;
                $statusStr = "ENVIADO";
            } else {
                echo "FALHA!\n";
                $falhas++;
                $statusStr = "FALHA_ENVIO";
            }
        } catch (Exception $e) {
            echo "ERRO DE RENDERIZAÇÃO: " . $e->getMessage() . "\n";
            $falhas++;
            $statusStr = "ERRO_SISTEMA";
        }

        // Registrar o resultado da operação no log
        $logMsg = "[" . date('Y-m-d H:i:s') . "] [$statusStr] Para: $destinatario | Sala: $sala | Posse: $horasTotais horas\n";
        file_put_contents($logFile, $logMsg, FILE_APPEND);
        
        // Registrar log de auditoria no banco
        if (function_exists('registrar_log')) {
            registrar_log($pdo, 'Alerta de Atraso', "E-mail de alerta disparado para '$nome' referente à chave '$sala' ($statusStr).");
        }
    }

    echo "=== Processamento de Alertas Concluído ===\n";
    echo "Sucessos: $enviados | Falhas: $falhas\n";

} catch (Exception $e) {
    echo "Erro Fatal: " . $e->getMessage() . "\n";
    exit(1);
}
