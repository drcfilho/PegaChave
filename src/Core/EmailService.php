<?php
namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $config;

    public function __construct() {
        // Carrega as variáveis do ambiente ou arquivo .env
        $this->config = [
            'host' => $_ENV['MAIL_HOST'] ?? getenv('MAIL_HOST') ?: '',
            'port' => $_ENV['MAIL_PORT'] ?? getenv('MAIL_PORT') ?: 465,
            'user' => $_ENV['MAIL_USER'] ?? getenv('MAIL_USER') ?: '',
            'pass' => $_ENV['MAIL_PASS'] ?? getenv('MAIL_PASS') ?: '',
            'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? getenv('MAIL_FROM_ADDRESS') ?: 'naoresponda@pegachave.com',
            'from_name' => $_ENV['MAIL_FROM_NAME'] ?? getenv('MAIL_FROM_NAME') ?: 'PegaChave Alertas'
        ];
    }

    public function enviar($destinatarioEmail, $destinatarioNome, $assunto, $corpoHtml) {
        if (empty($this->config['host'])) {
            error_log("EmailService: MAIL_HOST não configurado. E-mail não enviado para $destinatarioEmail.");
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            // Configurações do Servidor SMTP
            $mail->isSMTP();
            $mail->Host       = $this->config['host'];
            $mail->SMTPAuth   = !empty($this->config['user']);
            $mail->Username   = $this->config['user'];
            $mail->Password   = $this->config['pass'];
            
            // Define o tipo de encriptação baseado na porta
            if ($this->config['port'] == 587) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($this->config['port'] == 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            }
            
            $mail->Port       = $this->config['port'];
            $mail->CharSet    = 'UTF-8';

            // Remetente e Destinatário
            $mail->setFrom($this->config['from_address'], $this->config['from_name']);
            $mail->addAddress($destinatarioEmail, $destinatarioNome);

            // Conteúdo do E-mail
            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body    = $corpoHtml;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $corpoHtml));

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("EmailService: Falha ao enviar e-mail para $destinatarioEmail. Erro: {$mail->ErrorInfo}");
            return false;
        }
    }
}
