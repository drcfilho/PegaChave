<?php
// api/processar_scan.php
header("Content-Type: application/json; charset=UTF-8");
session_start();
require_once __DIR__ . '/db.php';

// Receber JSON input
$input = json_decode(file_get_contents('php://input'), true);
$qr_code = $input['qr_code'] ?? $_POST['qr_code'] ?? null;

if (!$qr_code) {
    echo json_encode(["status" => "error", "message" => "Nenhum código QR fornecido."]);
    exit;
}

try {
    // 1. Verificar se o QR Code pertence a uma Chave
    $stmt = $pdo->prepare("SELECT * FROM chaves WHERE qr_code_hash = ?");
    $stmt->execute([$qr_code]);
    $chave = $stmt->fetch();

    if ($chave) {
        $chave_id = $chave['id'];
        $nome_sala = $chave['nome_sala'];

        // Caso A: Chave Indisponível -> Devolução Automática
        if (!$chave['status_disponivel']) {
            // Achar a movimentação em aberto para essa chave
            $stmtMov = $pdo->prepare("SELECT id, usuario_id FROM movimentacoes WHERE chave_id = ? AND data_devolucao IS NULL ORDER BY data_retirada DESC LIMIT 1");
            $stmtMov->execute([$chave_id]);
            $mov = $stmtMov->fetch();

            if ($mov) {
                // Registrar devolução
                $stmtUpdateMov = $pdo->prepare("UPDATE movimentacoes SET data_devolucao = NOW() WHERE id = ?");
                $stmtUpdateMov->execute([$mov['id']]);

                // Atualizar status da chave
                $stmtUpdateChave = $pdo->prepare("UPDATE chaves SET status_disponivel = TRUE WHERE id = ?");
                $stmtUpdateChave->execute([$chave_id]);

                // Buscar nome do usuário para retornar na resposta
                $stmtUser = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
                $stmtUser->execute([$mov['usuario_id']]);
                $user = $stmtUser->fetch();

                // Limpar qualquer sessão pendente para segurança
                unset($_SESSION['usuario_pendente']);

                echo json_encode([
                    "status" => "success",
                    "tipo" => "devolucao",
                    "sala" => $nome_sala,
                    "usuario" => $user ? $user['nome'] : "Desconhecido",
                    "message" => "Chave da sala $nome_sala devolvida com sucesso!"
                ]);
            } else {
                // Inconsistência: chave indisponível no cadastro, mas sem registro de movimentação aberta
                // Força ficar disponível para limpar estado
                $pdo->prepare("UPDATE chaves SET status_disponivel = TRUE WHERE id = ?")->execute([$chave_id]);
                echo json_encode([
                    "status" => "error",
                    "message" => "Inconsistência corrigida: Chave estava marcada como ocupada mas não havia registro aberto."
                ]);
            }
            exit;
        }

        // Caso B: Chave Disponível -> Retirada
        // Precisa ter um usuário pendente em sessão
        if (isset($_SESSION['usuario_pendente'])) {
            $usuario = $_SESSION['usuario_pendente'];
            $usuario_id = $usuario['id'];

            // Registrar nova movimentação
            $stmtInsert = $pdo->prepare("INSERT INTO movimentacoes (usuario_id, chave_id, data_retirada) VALUES (?, ?, NOW())");
            $stmtInsert->execute([$usuario_id, $chave_id]);

            // Atualizar status da chave para indisponível
            $stmtUpdateChave = $pdo->prepare("UPDATE chaves SET status_disponivel = FALSE WHERE id = ?");
            $stmtUpdateChave->execute([$chave_id]);

            // Limpa a sessão pendente
            unset($_SESSION['usuario_pendente']);

            echo json_encode([
                "status" => "success",
                "tipo" => "retirada",
                "sala" => $nome_sala,
                "usuario" => $usuario['nome'],
                "message" => "Chave da sala $nome_sala retirada com sucesso por " . $usuario['nome'] . "!"
            ]);
        } else {
            // Chave está disponível, mas não tem usuário na sessão
            echo json_encode([
                "status" => "pending_user",
                "message" => "Por favor, aproxime o crachá do usuário antes de registrar a retirada da chave da sala $nome_sala."
            ]);
        }
        exit;
    }

    // 2. Verificar se o QR Code pertence a um Usuário
    $stmt = $pdo->prepare("SELECT id, nome, matricula, ativo FROM usuarios WHERE qr_code_hash = ?");
    $stmt->execute([$qr_code]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        if (!$usuario['ativo']) {
            echo json_encode(["status" => "error", "message" => "Usuário inativo no sistema."]);
            exit;
        }

        // Salvar usuário na sessão temporária
        $_SESSION['usuario_pendente'] = $usuario;

        echo json_encode([
            "status" => "success",
            "tipo" => "usuario",
            "usuario" => $usuario['nome'],
            "message" => "Olá, " . $usuario['nome'] . "! Agora aproxime o QR Code da chave que deseja retirar."
        ]);
        exit;
    }

    // 3. Caso não pertença a nenhum
    echo json_encode(["status" => "error", "message" => "Código QR não identificado."]);

} catch (\PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erro de banco de dados: " . $e->getMessage()]);
}
