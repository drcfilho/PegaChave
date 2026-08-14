<?php
// bin/alertar_atrasos.php

require_once dirname(__DIR__) . '/api/db.php';

echo "=== Iniciando Verificação de Chaves em Atraso ===\n";

try {
    // Buscar movimentações sem devolução iniciadas há mais de 8 horas
    $limiteHoras = 8;
    $query = "
        SELECT 
            m.id AS movimentacao_id,
            m.data_retirada,
            u.nome AS usuario_nome,
            u.email AS usuario_email,
            u.matricula AS usuario_matricula,
            c.nome_sala,
            c.codigo_sala
        FROM movimentacoes m
        JOIN usuarios u ON m.usuario_id = u.id
        JOIN chaves c ON m.chave_id = c.id
        WHERE m.data_devolucao IS NULL
          AND m.data_retirada < NOW() - INTERVAL ? HOUR
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$limiteHoras]);
    $atrasos = $stmt->fetchAll();

    if (empty($atrasos)) {
        echo "Nenhuma chave em atraso encontrada (limite: $limiteHoras horas).\n";
        exit(0);
    }

    echo "Encontrada(s) " . count($atrasos) . " chave(s) em atraso.\n";

    // Configurações do remetente
    $mailFrom = $_ENV['MAIL_FROM'] ?? getenv('MAIL_FROM') ?: 'alerta@pegachave.local';
    
    // Pasta de logs de alertas
    $logsDir = dirname(__DIR__) . '/tmp';
    if (!is_dir($logsDir)) {
        mkdir($logsDir, 0777, true);
    }
    $logFile = $logsDir . '/email_alerts.log';

    foreach ($atrasos as $registro) {
        $destinatario = $registro['usuario_email'];
        $nome = $registro['usuario_nome'];
        $sala = $registro['nome_sala'];
        $codigo = $registro['codigo_sala'];
        $retirada = date('d/m/Y H:i', strtotime($registro['data_retirada']));

        if (empty($destinatario)) {
            echo "Aviso: O usuário '$nome' não possui e-mail cadastrado. Pulando envio.\n";
            continue;
        }

        $assunto = "⚠️ PegaChave - Alerta de Chave em Atraso: Sala $sala";
        $mensagem = "Olá, $nome,\n\n";
        $mensagem .= "Identificamos que você retirou a chave da sala $sala ($codigo) no dia $retirada e ela ainda não foi devolvida.\n";
        $mensagem .= "Por favor, realize a devolução da chave no Quiosque o quanto antes para liberar o acesso a outros usuários.\n\n";
        $mensagem .= "Atenciosamente,\nEquipe de Controle de Chaves - PegaChave";

        $headers = "From: $mailFrom\r\n";
        $headers .= "Reply-To: $mailFrom\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        // Tentar enviar e-mail usando a função mail do PHP
        $enviado = false;
        try {
            // Em ambientes de desenvolvimento/local sem servidor SMTP configurado no php.ini,
            // isso pode retornar falso ou gerar avisos. Nós silenciamos com @ e registramos.
            $enviado = @mail($destinatario, $assunto, $mensagem, $headers);
        } catch (Exception $e) {
            $enviado = false;
        }

        // Registrar o resultado da operação no log
        $statusStr = $enviado ? "ENVIADO" : "SIMULADO (Sem SMTP)";
        $logMsg = "[" . date('Y-m-d H:i:s') . "] [$statusStr] Para: $destinatario | Sala: $sala | Retirada: $retirada\n";
        file_put_contents($logFile, $logMsg, FILE_APPEND);

        echo "Alerta de atraso para '$nome' ($destinatario) - Status: $statusStr.\n";
        
        // Registrar log de auditoria no banco
        registrar_log($pdo, 'Alerta de Atraso', "E-mail de alerta enviado para '$nome' referente à chave '$sala' ($statusStr).");
    }

    echo "=== Processamento de Alertas Concluído ===\n";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    exit(1);
}
