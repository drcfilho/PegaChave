<?php
// api/processar_scan.php
header("Content-Type: application/json; charset=UTF-8");
session_start();

// Controle de Rate Limiting (Máximo de 10 requisições a cada 30 segundos)
$currentTime = time();
if (!isset($_SESSION['scan_attempts'])) {
    $_SESSION['scan_attempts'] = [];
}
$_SESSION['scan_attempts'] = array_filter($_SESSION['scan_attempts'], function($timestamp) use ($currentTime) {
    return ($currentTime - $timestamp) < 30;
});
if (count($_SESSION['scan_attempts']) >= 10) {
    echo json_encode(["status" => "error", "message" => "Muitas tentativas rápidas. Por favor, aguarde alguns segundos antes de tentar novamente."]);
    exit;
}
$_SESSION['scan_attempts'][] = $currentTime;

require_once __DIR__ . '/db.php';

// Receber JSON input
$input = json_decode(file_get_contents('php://input'), true);
$qr_code = $input['qr_code'] ?? $_POST['qr_code'] ?? null;

if (!$qr_code) {
    echo json_encode(["status" => "error", "message" => "Nenhum código QR fornecido."]);
    exit;
}

try {
    // Verificar se o input contiver vírgula, tratamos como múltiplas chaves (códigos ou hashes)
    $contem_virgula = (strpos($qr_code, ',') !== false);

    if ($contem_virgula) {
        $codigos = array_map('trim', explode(',', $qr_code));
        $sucessos = [];
        $erros = [];

        // Buscar a primeira chave para inferir a operação (retirada ou devolução)
        $primeiro_cod = $codigos[0];
        $stmtCh = $pdo->prepare("SELECT * FROM chaves WHERE qr_code_hash = ? OR codigo_sala = ?");
        $stmtCh->execute([$primeiro_cod, $primeiro_cod]);
        $primeira_chave = $stmtCh->fetch();

        if (!$primeira_chave) {
            echo json_encode(["status" => "error", "message" => "A primeira chave do lote ('$primeiro_cod') não foi encontrada."]);
            exit;
        }

        $operacao = $primeira_chave['status_disponivel'] ? 'retirada' : 'devolucao';

        if ($operacao === 'retirada' && !isset($_SESSION['usuario_pendente'])) {
            echo json_encode([
                "status" => "pending_user",
                "message" => "Por favor, insira a matrícula do usuário antes de registrar a retirada múltipla das chaves: " . implode(', ', $codigos)
            ]);
            exit;
        }

        $usuario_atual = $_SESSION['usuario_pendente'] ?? null;

        // Processar cada código de chave
        foreach ($codigos as $cod) {
            if (empty($cod)) continue;

            $stmtCh = $pdo->prepare("SELECT * FROM chaves WHERE qr_code_hash = ? OR codigo_sala = ?");
            $stmtCh->execute([$cod, $cod]);
            $ch = $stmtCh->fetch();

            if (!$ch) {
                $erros[] = "Código '$cod' não encontrado";
                continue;
            }

            $ch_id = $ch['id'];
            $nom_sala = $ch['nome_sala'];

            if ($operacao === 'devolucao') {
                // Devolução da chave
                $stmtMov = $pdo->prepare("SELECT id, usuario_id FROM movimentacoes WHERE chave_id = ? AND data_devolucao IS NULL ORDER BY data_retirada DESC LIMIT 1");
                $stmtMov->execute([$ch_id]);
                $mov = $stmtMov->fetch();

                if ($mov) {
                    $observacao = null;
                    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
                        $admin_nome = $_SESSION['admin_name'] ?? 'Administrador';
                        $observacao = "Devolvida manualmente por: " . $admin_nome;
                    }
                    $pdo->prepare("UPDATE movimentacoes SET data_devolucao = NOW(), observacao = ? WHERE id = ?")->execute([$observacao, $mov['id']]);
                    $pdo->prepare("UPDATE chaves SET status_disponivel = TRUE WHERE id = ?")->execute([$ch_id]);
                    $sucessos[] = $nom_sala;
                } else {
                    $pdo->prepare("UPDATE chaves SET status_disponivel = TRUE WHERE id = ?")->execute([$ch_id]);
                    $sucessos[] = $nom_sala . " (Corrigido)";
                }
            } else {
                // Retirada da chave
                // 1. Restrições de perfil
                $perfil_id = $usuario_atual['perfil_id'] ?? null;
                if ($perfil_id) {
                    $stmtRestricao = $pdo->prepare("SELECT COUNT(*) FROM restricoes_acesso WHERE perfil_id = ? AND chave_id = ?");
                    $stmtRestricao->execute([$perfil_id, $ch_id]);
                    if ($stmtRestricao->fetchColumn() > 0) {
                        $erros[] = "Acesso Bloqueado para perfil no item $nom_sala";
                        continue;
                    }
                }

                // 2. Restrições de matrícula
                if (!empty($ch['matriculas_permitidas'])) {
                    $matriculas_permitidas = array_map('trim', explode(',', $ch['matriculas_permitidas']));
                    if (!in_array($usuario_atual['matricula'], $matriculas_permitidas)) {
                        $erros[] = "Matrícula não autorizada no item $nom_sala";
                        continue;
                    }
                }

                // 3. Reservas
                $stmtReserva = $pdo->prepare("
                    SELECT r.*, u.nome AS reservado_nome 
                    FROM reservas r
                    JOIN usuarios u ON r.usuario_id = u.id
                    WHERE r.chave_id = ?
                      AND r.data_reserva = CURDATE()
                      AND (
                          (CURTIME() BETWEEN r.hora_inicio AND r.hora_fim)
                          OR (r.hora_inicio BETWEEN CURTIME() AND ADDTIME(CURTIME(), '00:15:00'))
                      )
                      AND r.usuario_id <> ?
                    LIMIT 1
                ");
                $stmtReserva->execute([$ch_id, $usuario_atual['id']]);
                if ($stmtReserva->fetch()) {
                    $erros[] = "Sala $nom_sala reservada para terceiros";
                    continue;
                }

                // 4. Limite de chaves
                if (isset($limite_chaves) && $limite_chaves > 0) {
                    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM movimentacoes WHERE usuario_id = ? AND data_devolucao IS NULL");
                    $stmtCount->execute([$usuario_atual['id']]);
                    $totalPosse = $stmtCount->fetchColumn();

                    if ($totalPosse >= $limite_chaves) {
                        $erros[] = "Limite excedido para retirar $nom_sala";
                        continue;
                    }
                }

                // Inserir movimentação
                $pdo->prepare("INSERT INTO movimentacoes (usuario_id, chave_id, data_retirada) VALUES (?, ?, NOW())")->execute([$usuario_atual['id'], $ch_id]);
                $pdo->prepare("UPDATE chaves SET status_disponivel = FALSE WHERE id = ?")->execute([$ch_id]);
                $sucessos[] = $nom_sala;
            }
        }

        unset($_SESSION['usuario_pendente']);

        if (count($sucessos) > 0) {
            $msg = ($operacao === 'retirada' ? 'Chaves retiradas: ' : 'Chaves devolvidas: ') . implode(', ', $sucessos);
            if (count($erros) > 0) {
                $msg .= ". Alertas: " . implode('; ', $erros);
            }
            echo json_encode([
                "status" => "success",
                "tipo" => $operacao,
                "sala" => implode(', ', $sucessos),
                "usuario" => $usuario_atual ? $usuario_atual['nome'] : "Desconhecido",
                "message" => $msg
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Bloqueado: " . implode('; ', $erros)
            ]);
        }
        exit;
    }

    // 1. Verificar se o QR Code pertence a uma Chave (por hash ou código)
    $stmt = $pdo->prepare("SELECT * FROM chaves WHERE qr_code_hash = ? OR codigo_sala = ?");
    $stmt->execute([$qr_code, $qr_code]);
    $chave = $stmt->fetch();

    if ($chave) {
        $chave_id = $chave['id'];
        $nome_sala = $chave['nome_sala'];

        // Caso A: Chave Indisponível -> Devolução Automática
        if (!$chave['status_disponivel']) {
            $stmtMov = $pdo->prepare("SELECT id, usuario_id FROM movimentacoes WHERE chave_id = ? AND data_devolucao IS NULL ORDER BY data_retirada DESC LIMIT 1");
            $stmtMov->execute([$chave_id]);
            $mov = $stmtMov->fetch();

            if ($mov) {
                $observacao = null;
                if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
                    $admin_nome = $_SESSION['admin_name'] ?? 'Administrador';
                    $observacao = "Devolvida manualmente por: " . $admin_nome;
                }

                $stmtUpdateMov = $pdo->prepare("UPDATE movimentacoes SET data_devolucao = NOW(), observacao = ? WHERE id = ?");
                $stmtUpdateMov->execute([$observacao, $mov['id']]);

                $stmtUpdateChave = $pdo->prepare("UPDATE chaves SET status_disponivel = TRUE WHERE id = ?");
                $stmtUpdateChave->execute([$chave_id]);

                $stmtUser = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
                $stmtUser->execute([$mov['usuario_id']]);
                $user = $stmtUser->fetch();

                unset($_SESSION['usuario_pendente']);

                echo json_encode([
                    "status" => "success",
                    "tipo" => "devolucao",
                    "sala" => $nome_sala,
                    "usuario" => $user ? $user['nome'] : "Desconhecido",
                    "message" => "Chave da sala $nome_sala devolvida com sucesso!"
                ]);
            } else {
                $pdo->prepare("UPDATE chaves SET status_disponivel = TRUE WHERE id = ?")->execute([$chave_id]);
                echo json_encode([
                    "status" => "error",
                    "message" => "Inconsistência corrigida: Chave marcada como ocupada mas sem movimentação."
                ]);
            }
            exit;
        }

        // Caso B: Chave Disponível -> Retirada
        if (isset($_SESSION['usuario_pendente'])) {
            $usuario = $_SESSION['usuario_pendente'];
            $usuario_id = $usuario['id'];

            // Verificar restrições de perfil
            $perfil_id = $usuario['perfil_id'] ?? null;
            if ($perfil_id) {
                $stmtRestricao = $pdo->prepare("
                    SELECT p.nome AS perfil_nome 
                    FROM restricoes_acesso r
                    JOIN perfis p ON r.perfil_id = p.id
                    WHERE r.perfil_id = ? AND r.chave_id = ?
                ");
                $stmtRestricao->execute([$perfil_id, $chave_id]);
                $restrito = $stmtRestricao->fetch();

                if ($restrito) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Bloqueado: Perfil '{$restrito['perfil_nome']}' não tem permissão para retirar esta chave!"
                    ]);
                    exit;
                }
            }

            // Verificar restrições de matrícula
            if (!empty($chave['matriculas_permitidas'])) {
                $matriculas_permitidas = array_map('trim', explode(',', $chave['matriculas_permitidas']));
                if (!in_array($usuario['matricula'], $matriculas_permitidas)) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Bloqueado: Acesso restrito! Sua matrícula '{$usuario['matricula']}' não está autorizada a retirar esta chave."
                    ]);
                    exit;
                }
            }

            // Reservas
            $stmtReserva = $pdo->prepare("
                SELECT r.*, u.nome AS reservado_nome 
                FROM reservas r
                JOIN usuarios u ON r.usuario_id = u.id
                WHERE r.chave_id = ?
                  AND r.data_reserva = CURDATE()
                  AND (
                      (CURTIME() BETWEEN r.hora_inicio AND r.hora_fim)
                      OR (r.hora_inicio BETWEEN CURTIME() AND ADDTIME(CURTIME(), '00:15:00'))
                  )
                  AND r.usuario_id <> ?
                LIMIT 1
            ");
            $stmtReserva->execute([$chave_id, $usuario_id]);
            $reservaAtiva = $stmtReserva->fetch();

            if ($reservaAtiva) {
                $inicio = date('H:i', strtotime($reservaAtiva['hora_inicio']));
                $fim = date('H:i', strtotime($reservaAtiva['hora_fim']));
                echo json_encode([
                    "status" => "error",
                    "message" => "Bloqueado: Sala reservada para {$reservaAtiva['reservado_nome']} das $inicio às $fim!"
                ]);
                exit;
            }

            // Limite de chaves
            if (isset($limite_chaves) && $limite_chaves > 0) {
                $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM movimentacoes WHERE usuario_id = ? AND data_devolucao IS NULL");
                $stmtCount->execute([$usuario_id]);
                $totalPosse = $stmtCount->fetchColumn();

                if ($totalPosse >= $limite_chaves) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Bloqueado: Limite atingido! Você só pode ter $limite_chaves chave(s) retirada(s) simultaneamente."
                    ]);
                    exit;
                }
            }

            $stmtInsert = $pdo->prepare("INSERT INTO movimentacoes (usuario_id, chave_id, data_retirada) VALUES (?, ?, NOW())");
            $stmtInsert->execute([$usuario_id, $chave_id]);

            $stmtUpdateChave = $pdo->prepare("UPDATE chaves SET status_disponivel = FALSE WHERE id = ?");
            $stmtUpdateChave->execute([$chave_id]);

            unset($_SESSION['usuario_pendente']);

            echo json_encode([
                "status" => "success",
                "tipo" => "retirada",
                "sala" => $nome_sala,
                "usuario" => $usuario['nome'],
                "message" => "Chave da sala $nome_sala retirada com sucesso por " . $usuario['nome'] . "!"
            ]);
        } else {
            echo json_encode([
                "status" => "pending_user",
                "message" => "Por favor, insira a matrícula do usuário antes de registrar a retirada da chave da sala $nome_sala."
            ]);
        }
        exit;
    }

    // 2. Verificar se o QR Code pertence a um Usuário (por hash ou matrícula)
    $stmt = $pdo->prepare("SELECT id, nome, matricula, perfil_id, ativo FROM usuarios WHERE qr_code_hash = ? OR matricula = ?");
    $stmt->execute([$qr_code, $qr_code]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        if (!$usuario['ativo']) {
            echo json_encode(["status" => "error", "message" => "Usuário inativo no sistema."]);
            exit;
        }

        $_SESSION['usuario_pendente'] = $usuario;

        echo json_encode([
            "status" => "success",
            "tipo" => "usuario",
            "usuario" => $usuario['nome'],
            "message" => "Olá, " . $usuario['nome'] . "! Agora insira o código da chave que deseja retirar."
        ]);
        exit;
    }

    echo json_encode(["status" => "error", "message" => "Código não identificado no sistema (Matrícula ou Código da Sala inválido)."]);

} catch (\PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erro de banco de dados: " . $e->getMessage()]);
}
