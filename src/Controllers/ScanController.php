<?php
namespace App\Controllers;

use App\Models\ChaveRepository;
use App\Models\UsuarioRepository;
use App\Models\MovimentacaoRepository;
use App\Models\ReservaRepository;
use App\Models\RestricaoRepository;

class ScanController extends BaseController {
    protected $pdo;


    public function processar() {
        $pdo = $this->pdo;
        extract($this->config);
        header("Content-Type: application/json; charset=UTF-8");
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Controle de Rate Limiting
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

        
        // Instanciar repositórios
        $chaveRepo = new ChaveRepository($pdo);
        $usuarioRepo = new UsuarioRepository($pdo);
        $movRepo = new MovimentacaoRepository($pdo);
        $reservaRepo = new ReservaRepository($pdo);
        $restricaoRepo = new RestricaoRepository($pdo);

        // Receber JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        $qr_code = $input['qr_code'] ?? $_POST['qr_code'] ?? null;

        if (!$qr_code) {
            echo json_encode(["status" => "error", "message" => "Nenhum código QR fornecido."]);
            exit;
        }

        try {
            $contem_virgula = (strpos($qr_code, ',') !== false);

            if ($contem_virgula) {
                $codigos = array_map('trim', explode(',', $qr_code));
                $sucessos = [];
                $erros = [];

                $primeiro_cod = $codigos[0];
                $primeira_chave = $chaveRepo->buscarPorCodigoOuHash($primeiro_cod);

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

                foreach ($codigos as $cod) {
                    if (empty($cod)) continue;

                    $ch = $chaveRepo->buscarPorCodigoOuHash($cod);

                    if (!$ch) {
                        $erros[] = "Código '$cod' não encontrado";
                        continue;
                    }

                    $ch_id = $ch['id'];
                    $nom_sala = $ch['nome_sala'];

                    if ($operacao === 'devolucao') {
                        $mov = $movRepo->buscarAtivaPorChave($ch_id);

                        if ($mov) {
                            $observacao = null;
                            if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
                                $admin_nome = $_SESSION['admin_name'] ?? 'Administrador';
                                $observacao = "Devolvida manualmente por: " . $admin_nome;
                            }
                            $movRepo->registrarDevolucao($mov['id'], $observacao);
                            $chaveRepo->atualizarStatus($ch_id, 1);
                            $sucessos[] = $nom_sala;
                        } else {
                            $chaveRepo->atualizarStatus($ch_id, 1);
                            $sucessos[] = $nom_sala . " (Corrigido)";
                        }
                    } else {
                        // Retirada da chave
                        $perfil_id = $usuario_atual['perfil_id'] ?? null;
                        if ($perfil_id) {
                            $restrito = $restricaoRepo->verificarRestricao($perfil_id, $ch_id);
                            if ($restrito) {
                                $erros[] = "Acesso Bloqueado para perfil no item $nom_sala";
                                continue;
                            }
                        }

                        if (!empty($ch['matriculas_permitidas'])) {
                            $matriculas_permitidas = array_map('trim', explode(',', $ch['matriculas_permitidas']));
                            if (!in_array($usuario_atual['matricula'], $matriculas_permitidas)) {
                                $erros[] = "Matrícula não autorizada no item $nom_sala";
                                continue;
                            }
                        }

                        $reservaAtiva = $reservaRepo->buscarReservaAtiva($ch_id, $usuario_atual['id']);
                        if ($reservaAtiva) {
                            $erros[] = "Sala $nom_sala reservada para terceiros";
                            continue;
                        }

                        if (isset($limite_chaves) && $limite_chaves > 0) {
                            $totalPosse = $movRepo->contarPossePorUsuario($usuario_atual['id']);
                            if ($totalPosse >= $limite_chaves) {
                                $erros[] = "Limite excedido para retirar $nom_sala";
                                continue;
                            }
                        }

                        $movRepo->registrarRetirada($usuario_atual['id'], $ch_id);
                        $chaveRepo->atualizarStatus($ch_id, 0);
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

            // 1. Verificar se o QR Code pertence a uma Chave
            $chave = $chaveRepo->buscarPorCodigoOuHash($qr_code);

            if ($chave) {
                $chave_id = $chave['id'];
                $nome_sala = $chave['nome_sala'];

                // Devolução Automática
                if (!$chave['status_disponivel']) {
                    $mov = $movRepo->buscarAtivaPorChave($chave_id);

                    if ($mov) {
                        $observacao = null;
                        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
                            $admin_nome = $_SESSION['admin_name'] ?? 'Administrador';
                            $observacao = "Devolvida manualmente por: " . $admin_nome;
                        }

                        $movRepo->registrarDevolucao($mov['id'], $observacao);
                        $chaveRepo->atualizarStatus($chave_id, 1);
                        
                        $user = $usuarioRepo->buscarPorId($mov['usuario_id']);

                        unset($_SESSION['usuario_pendente']);

                        echo json_encode([
                            "status" => "success",
                            "tipo" => "devolucao",
                            "sala" => $nome_sala,
                            "usuario" => $user ? $user['nome'] : "Desconhecido",
                            "message" => "Chave da sala $nome_sala devolvida com sucesso!"
                        ]);
                    } else {
                        $chaveRepo->atualizarStatus($chave_id, 1);
                        echo json_encode([
                            "status" => "error",
                            "message" => "Inconsistência corrigida: Chave marcada como ocupada mas sem movimentação."
                        ]);
                    }
                    exit;
                }

                // Retirada
                if (isset($_SESSION['usuario_pendente'])) {
                    $usuario = $_SESSION['usuario_pendente'];
                    $usuario_id = $usuario['id'];

                    $perfil_id = $usuario['perfil_id'] ?? null;
                    if ($perfil_id) {
                        $restrito = $restricaoRepo->verificarRestricao($perfil_id, $chave_id);
                        if ($restrito) {
                            echo json_encode([
                                "status" => "error",
                                "message" => "Bloqueado: Perfil '{$restrito['perfil_nome']}' não tem permissão para retirar esta chave!"
                            ]);
                            exit;
                        }
                    }

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

                    $reservaAtiva = $reservaRepo->buscarReservaAtiva($chave_id, $usuario_id);
                    if ($reservaAtiva) {
                        $inicio = date('H:i', strtotime($reservaAtiva['hora_inicio']));
                        $fim = date('H:i', strtotime($reservaAtiva['hora_fim']));
                        echo json_encode([
                            "status" => "error",
                            "message" => "Bloqueado: Sala reservada para {$reservaAtiva['reservado_nome']} das $inicio às $fim!"
                        ]);
                        exit;
                    }

                    if (isset($limite_chaves) && $limite_chaves > 0) {
                        $totalPosse = $movRepo->contarPossePorUsuario($usuario_id);
                        if ($totalPosse >= $limite_chaves) {
                            echo json_encode([
                                "status" => "error",
                                "message" => "Bloqueado: Limite atingido! Você só pode ter $limite_chaves chave(s) retirada(s) simultaneamente."
                            ]);
                            exit;
                        }
                    }

                    $movRepo->registrarRetirada($usuario_id, $chave_id);
                    $chaveRepo->atualizarStatus($chave_id, 0);

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

            // 2. Verificar se o QR Code pertence a um Usuário
            $usuario = $usuarioRepo->buscarPorMatriculaOuHash($qr_code);

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
    }
}
