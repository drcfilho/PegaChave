<?php
// admin_reservas.php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}
require_once __DIR__ . '/api/db.php';

$message = "";
$messageType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validar_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = "Token de segurança inválido. Tente novamente.";
        $messageType = "error";
    } else {
        $action = $_POST['action'] ?? '';

        try {
            if ($action === 'add_reserva') {
                $chave_id = filter_var($_POST['chave_id'] ?? '', FILTER_VALIDATE_INT);
                $usuario_id = filter_var($_POST['usuario_id'] ?? '', FILTER_VALIDATE_INT);
                $data_reserva = $_POST['data_reserva'] ?? '';
                $hora_inicio = $_POST['hora_inicio'] ?? '';
                $hora_fim = $_POST['hora_fim'] ?? '';

                if (!$chave_id || !$usuario_id || empty($data_reserva) || empty($hora_inicio) || empty($hora_fim)) {
                    throw new Exception("Todos os campos de agendamento são obrigatórios.");
                }

                // Saneamento básico e validação de data/hora
                $data_reserva = date('Y-m-d', strtotime($data_reserva));
                $hora_inicio = date('H:i:s', strtotime($hora_inicio));
                $hora_fim = date('H:i:s', strtotime($hora_fim));

                if ($hora_inicio >= $hora_fim) {
                    throw new Exception("A hora de início deve ser menor que a hora de término.");
                }

                // Verificar conflito de horário para a mesma chave/sala na data
                $stmtCheck = $pdo->prepare("
                    SELECT r.*, u.nome AS reservado_nome 
                    FROM reservas r
                    JOIN usuarios u ON r.usuario_id = u.id
                    WHERE r.chave_id = ?
                      AND r.data_reserva = ?
                      AND (
                          (? BETWEEN r.hora_inicio AND r.hora_fim)
                          OR (? BETWEEN r.hora_inicio AND r.hora_fim)
                          OR (r.hora_inicio BETWEEN ? AND ?)
                      )
                ");
                $stmtCheck->execute([$chave_id, $data_reserva, $hora_inicio, $hora_fim, $hora_inicio, $hora_fim]);
                $conflito = $stmtCheck->fetch();

                if ($conflito) {
                    throw new Exception("Conflito: A sala já está reservada por {$conflito['reservado_nome']} neste horário!");
                }

                // Inserir agendamento
                $stmtInsert = $pdo->prepare("
                    INSERT INTO reservas (chave_id, usuario_id, data_reserva, hora_inicio, hora_fim) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmtInsert->execute([$chave_id, $usuario_id, $data_reserva, $hora_inicio, $hora_fim]);

                // Obter nomes para o log
                $nomeSala = $pdo->query("SELECT nome_sala FROM chaves WHERE id = $chave_id")->fetchColumn();
                $nomeUser = $pdo->query("SELECT nome FROM usuarios WHERE id = $usuario_id")->fetchColumn();
                
                registrar_log($pdo, 'Agendamento de Sala', "Sala '$nomeSala' agendada para '$nomeUser' em $data_reserva das " . substr($hora_inicio, 0, 5) . " às " . substr($hora_fim, 0, 5));

                $message = "Sala reservada com sucesso!";
                $messageType = "success";
            } 
            
            elseif ($action === 'delete_reserva') {
                $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
                if (!$id) {
                    throw new Exception("ID de agendamento inválido.");
                }

                // Obter informações para o log
                $stmtInfo = $pdo->prepare("
                    SELECT c.nome_sala, u.nome, r.data_reserva 
                    FROM reservas r
                    JOIN chaves c ON r.chave_id = c.id
                    JOIN usuarios u ON r.usuario_id = u.id
                    WHERE r.id = ?
                ");
                $stmtInfo->execute([$id]);
                $info = $stmtInfo->fetch();

                $stmtDelete = $pdo->prepare("DELETE FROM reservas WHERE id = ?");
                $stmtDelete->execute([$id]);

                if ($info) {
                    registrar_log($pdo, 'Cancelamento de Reserva', "Reserva da sala '{$info['nome_sala']}' para '{$info['nome']}' no dia {$info['data_reserva']} foi cancelada.");
                }

                $message = "Reserva cancelada com sucesso.";
                $messageType = "success";
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = "error";
        }
    }
}

// Carregar chaves, usuários e reservas
try {
    $chaves = $pdo->query("SELECT id, nome_sala, codigo_sala FROM chaves ORDER BY nome_sala ASC")->fetchAll();
    $usuarios = $pdo->query("SELECT id, nome FROM usuarios WHERE ativo = TRUE AND excluido = FALSE ORDER BY nome ASC")->fetchAll();
    
    // Obter reservas futuras e de hoje
    $reservas = $pdo->query("
        SELECT 
            r.id,
            r.data_reserva,
            DATE_FORMAT(r.hora_inicio, '%H:%i') AS inicio,
            DATE_FORMAT(r.hora_fim, '%H:%i') AS fim,
            c.nome_sala,
            c.codigo_sala,
            u.nome AS usuario_nome
        FROM reservas r
        JOIN chaves c ON r.chave_id = c.id
        JOIN usuarios u ON r.usuario_id = u.id
        WHERE r.data_reserva >= CURDATE()
        ORDER BY r.data_reserva ASC, r.hora_inicio ASC
    ")->fetchAll();
} catch (\PDOException $e) {
    echo "Erro de Banco de Dados: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamentos de Salas - PegaChave</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #f1f5f9;
            --text-color: #1e293b;
            --sidebar-bg: #0f172a;
            --sidebar-active: <?php echo $cor_primaria; ?>;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --primary: <?php echo $cor_primaria; ?>;
            --success: #22c55e;
            --error: #ef4444;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        aside {
            width: 260px;
            background-color: var(--sidebar-bg);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 10;
        }

        .sidebar-header {
            padding: 24px;
            font-size: 20px;
            font-weight: 800;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
            flex: 1;
        }

        .sidebar-item a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 24px;
            color: #94a3b8;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.2s;
            border-left: 4px solid transparent;
        }

        .sidebar-item a:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.02);
        }

        .sidebar-item.active a {
            color: #ffffff;
            background: rgba(2, 132, 199, 0.1);
            border-left-color: var(--sidebar-active);
        }

        .sidebar-footer {
            padding: 20px 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .btn-kiosk {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: rgba(255,255,255,0.05);
            color: #fff;
            text-decoration: none;
            padding: 10px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            transition: background 0.2s;
        }

        .btn-kiosk:hover {
            background-color: var(--sidebar-active);
        }

        /* Main Content */
        main {
            margin-left: 260px;
            flex: 1;
            padding: 40px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title h1 {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
        }

        .page-title p {
            color: #64748b;
            font-size: 14px;
            margin-top: 4px;
        }

        .btn-add {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: opacity 0.2s;
        }

        .btn-add:hover {
            opacity: 0.9;
        }

        /* Card container */
        .content-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #0f172a;
        }

        /* Message Banner */
        .message-banner {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 14px;
        }

        .message-banner.success {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .message-banner.error {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            padding: 12px 16px;
            border-bottom: 2px solid #e2e8f0;
            text-transform: uppercase;
        }

        td {
            font-size: 14px;
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .action-link {
            color: var(--error);
            text-decoration: none;
            font-weight: 700;
            cursor: pointer;
        }

        .action-link:hover {
            text-decoration: underline;
        }

        /* Modal popup */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 100;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--card-bg);
            border-radius: 16px;
            width: 100%;
            max-width: 500px;
            padding: 32px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .modal-header h2 {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
        }

        .btn-close {
            background: transparent;
            border: none;
            font-size: 24px;
            color: #64748b;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            background: var(--card-bg);
            color: var(--text-color);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
        }

        .btn-modal {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-cancel {
            background: #e2e8f0;
            border: none;
            color: #475569;
        }

        .btn-save {
            background: var(--primary);
            border: none;
            color: white;
        }
    </style>
    <link rel="stylesheet" href="/api/admin_responsive.css">
</head>
<body>

    <!-- Sidebar -->
    <aside>
        <div class="sidebar-header">
            <span>🔑</span> PegaChave
        </div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="/admin.php">📊 Dashboard</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_chaves.php">🔑 Chaves/Salas</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_usuarios.php">👤 Usuários</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_usuarios_arquivados.php">🗄️ Arquivados</a>
            </li>
            <li class="sidebar-item active">
                <a href="/admin_reservas.php">📅 Agendamentos</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_gerar_qr.php">🖨️ Gerar QR Codes</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_consulta.php">🔍 Consultar Disponibilidade</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_relatorio.php">📝 Relatório Geral</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_logs.php">📋 Logs de Auditoria</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_config.php">⚙️ Configurações</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_logout.php" style="color: #ef4444;">🚪 Sair</a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <a href="/index.php" class="btn-kiosk">🖥️ Voltar ao Quiosque</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main>
        <div class="page-header">
            <div class="page-title">
                <h1>Agendamentos de Salas</h1>
                <p>Gerencie reservas futuras de chaves e previna conflitos no quiosque.</p>
            </div>
            <button class="btn-add" onclick="abrirModal()">
                <span>➕</span> Novo Agendamento
            </button>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message-banner <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Listagem de Reservas -->
        <div class="content-card">
            <h2 class="card-title">Reservas Futuras e de Hoje</h2>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Horário</th>
                            <th>Chave/Sala</th>
                            <th>Reservado Para</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reservas)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #64748b; padding: 25px;">
                                    Nenhum agendamento futuro encontrado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reservas as $r): ?>
                                <tr>
                                    <td><strong><?php echo date('d/m/Y', strtotime($r['data_reserva'])); ?></strong></td>
                                    <td><?php echo $r['inicio']; ?> - <?php echo $r['fim']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($r['nome_sala']); ?></strong> (<?php echo htmlspecialchars($r['codigo_sala']); ?>)</td>
                                    <td><?php echo htmlspecialchars($r['usuario_nome']); ?></td>
                                    <td>
                                        <form method="POST" action="admin_reservas.php" onsubmit="return confirm('Deseja cancelar este agendamento?');" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo gerador_csrf_token(); ?>">
                                            <input type="hidden" name="action" value="delete_reserva">
                                            <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                            <button type="submit" class="action-link" style="background:none; border:none; padding:0; font-size:inherit; font-family:inherit;">Cancelar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Form -->
    <div class="modal" id="modalAgendamento">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Novo Agendamento</h2>
                <button class="btn-close" onclick="fecharModal()">&times;</button>
            </div>
            <form method="POST" action="admin_reservas.php">
                <input type="hidden" name="csrf_token" value="<?php echo gerador_csrf_token(); ?>">
                <input type="hidden" name="action" value="add_reserva">

                <div class="form-group">
                    <label for="chave_id">Selecione a Sala</label>
                    <select name="chave_id" id="chave_id" class="form-control" required>
                        <option value="">-- Escolha a Sala --</option>
                        <?php foreach ($chaves as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nome_sala']); ?> (<?php echo htmlspecialchars($c['codigo_sala']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="usuario_id">Selecione o Usuário</label>
                    <select name="usuario_id" id="usuario_id" class="form-control" required>
                        <option value="">-- Escolha o Usuário --</option>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="data_reserva">Data da Reserva</label>
                    <input type="date" name="data_reserva" id="data_reserva" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group" style="display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label for="hora_inicio">Hora de Início</label>
                        <input type="time" name="hora_inicio" id="hora_inicio" class="form-control" required>
                    </div>
                    <div style="flex: 1;">
                        <label for="hora_fim">Hora de Término</label>
                        <input type="time" name="hora_fim" id="hora_fim" class="form-control" required>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-modal btn-cancel" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn-modal btn-save">Confirmar Reserva</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModal() {
            document.getElementById('modalAgendamento').classList.add('active');
        }

        function fecharModal() {
            document.getElementById('modalAgendamento').classList.remove('active');
        }
    </script>
    <script src="/api/admin_responsive.js"></script>
</body>
</html>
