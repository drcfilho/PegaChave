<?php
namespace App\Controllers;

use eftec\bladeone\BladeOne;
use App\Models\MovimentacaoRepository;
use App\Core\EmailService;
use Exception;
use DateTime;

class CronController {
    private $blade;
    private $pdo;

    public function __construct($blade, $pdo) {
        $this->blade = $blade;
        $this->pdo = $pdo;
    }

    public function processarAlertas() {
        // Proteção opcional: exigir uma secret key via GET para evitar chamadas abusivas
        // Exemplo: /api/cron/alertas?secret=minha_senha_secreta
        $secretEnv = $_ENV['CRON_SECRET'] ?? getenv('CRON_SECRET');
        $secretReq = $_GET['secret'] ?? '';

        if (!empty($secretEnv) && $secretReq !== $secretEnv) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Acesso Negado. Token cron inválido."]);
            return;
        }

        try {
            $movRepo = new MovimentacaoRepository($this->pdo);
            $emailService = new EmailService();

            $limiteHoras = $_ENV['LIMITE_HORAS_ATRASO'] ?? getenv('LIMITE_HORAS_ATRASO') ?: 6;
            $atrasadas = $movRepo->buscarAtrasadas($limiteHoras);

            if (empty($atrasadas)) {
                echo json_encode([
                    "status" => "success", 
                    "message" => "Nenhuma chave em atraso encontrada.",
                    "enviados" => 0,
                    "falhas" => 0
                ]);
                return;
            }

            $logsDir = __DIR__ . '/../../tmp';
            if (!is_dir($logsDir)) {
                mkdir($logsDir, 0777, true);
            }
            $logFile = $logsDir . '/email_alerts.log';

            $enviados = 0;
            $falhas = 0;
            $resultados = [];

            foreach ($atrasadas as $registro) {
                $destinatario = $registro['usuario_email'];
                $nome = $registro['usuario_nome'];
                $sala = $registro['nome_sala'];
                
                $dataRetirada = new DateTime($registro['data_retirada']);
                $agora = new DateTime();
                $diferenca = $dataRetirada->diff($agora);
                $horasTotais = ($diferenca->days * 24) + $diferenca->h;

                $dadosTemplate = [
                    'nome' => $nome,
                    'sala' => $sala,
                    'data_retirada' => $dataRetirada->format('d/m/Y H:i'),
                    'tempo_posse' => $horasTotais
                ];

                try {
                    $corpoHtml = $this->blade->run("emails.alerta_atraso", $dadosTemplate);
                    $assunto = "⚠️ PegaChave - Alerta de Chave Pendente: Sala $sala";
                    
                    if ($emailService->enviar($destinatario, $nome, $assunto, $corpoHtml)) {
                        $enviados++;
                        $statusStr = "ENVIADO";
                    } else {
                        $falhas++;
                        $statusStr = "FALHA_ENVIO";
                    }
                } catch (Exception $e) {
                    $falhas++;
                    $statusStr = "ERRO_SISTEMA: " . $e->getMessage();
                }

                $logMsg = "[" . date('Y-m-d H:i:s') . "] [$statusStr] Para: $destinatario | Sala: $sala | Posse: $horasTotais horas\n";
                file_put_contents($logFile, $logMsg, FILE_APPEND);
                
                $resultados[] = [
                    "usuario" => $nome,
                    "email" => $destinatario,
                    "sala" => $sala,
                    "status" => $statusStr
                ];

                if (function_exists('registrar_log')) {
                    registrar_log($this->pdo, 'Alerta de Atraso (Web)', "E-mail disparado para '$nome' (Chave '$sala') - Status: $statusStr");
                }
            }

            echo json_encode([
                "status" => "success", 
                "message" => "Processamento concluído.",
                "enviados" => $enviados,
                "falhas" => $falhas,
                "detalhes" => $resultados
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Erro Fatal: " . $e->getMessage()]);
        }
    }
}
