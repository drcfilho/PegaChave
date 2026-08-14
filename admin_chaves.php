<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}
require_once __DIR__ . '/api/db.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validar_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = "Token de segurança inválido. Tente novamente.";
        $messageType = "error";
    } else {
        $action = $_POST['action'];

    try {
        if ($action === 'add_chave') {
            $nome_sala = $_POST['nome_sala'] ?? '';
            $codigo_sala = $_POST['codigo_sala'] ?? '';
            $qr_code_hash = $_POST['qr_code_hash'] ?? '';
            $descricao = $_POST['descricao'] ?? '';

            $stmt = $pdo->prepare("INSERT INTO chaves (nome_sala, codigo_sala, qr_code_hash, descricao) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome_sala, $codigo_sala, $qr_code_hash, $descricao]);
            registrar_log($pdo, 'Cadastro de Chave', "Chave '$nome_sala' ($codigo_sala) cadastrada.");
            $message = "Chave '$nome_sala' cadastrada com sucesso!";
            $messageType = "success";
        } 
        
        elseif ($action === 'edit_chave') {
            $id = $_POST['id'] ?? '';
            $nome_sala = $_POST['nome_sala'] ?? '';
            $codigo_sala = $_POST['codigo_sala'] ?? '';
            $qr_code_hash = $_POST['qr_code_hash'] ?? '';
            $descricao = $_POST['descricao'] ?? '';

            $stmt = $pdo->prepare("UPDATE chaves SET nome_sala = ?, codigo_sala = ?, qr_code_hash = ?, descricao = ? WHERE id = ?");
            $stmt->execute([$nome_sala, $codigo_sala, $qr_code_hash, $descricao, $id]);
            registrar_log($pdo, 'Edição de Chave', "Chave ID $id atualizada para '$nome_sala' ($codigo_sala).");
            $message = "Chave atualizada com sucesso!";
            $messageType = "success";
        } 
        
        elseif ($action === 'delete_chave') {
            $id = $_POST['id'] ?? '';
            // Obter nome antes de deletar
            $stmtChave = $pdo->prepare("SELECT nome_sala, codigo_sala FROM chaves WHERE id = ?");
            $stmtChave->execute([$id]);
            $chaveInfo = $stmtChave->fetch();
            $nomeExcluido = $chaveInfo ? $chaveInfo['nome_sala'] . ' (' . $chaveInfo['codigo_sala'] . ')' : "ID $id";

            // Limpar movimentações associadas primeiro
            $pdo->prepare("DELETE FROM movimentacoes WHERE chave_id = ?")->execute([$id]);
            
            $stmt = $pdo->prepare("DELETE FROM chaves WHERE id = ?");
            $stmt->execute([$id]);
            registrar_log($pdo, 'Remoção de Chave', "Chave '$nomeExcluido' e seu histórico foram removidos.");
            $message = "Chave removida.";
            $messageType = "success";
        }
    } catch (\PDOException $e) {
        $message = "Erro ao salvar dados: " . $e->getMessage();
        $messageType = "error";
    }
  }
}

try {
    $chaves = $pdo->query("SELECT * FROM chaves ORDER BY nome_sala ASC")->fetchAll();
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
    <title>Gerenciamento de Chaves - PegaChave</title>
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

        .btn-add {
            background-color: var(--success);
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-add:hover {
            background-color: var(--primary-green-hover);
        }

        .content-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 24px;
        }

        /* Tables */
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
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            cursor: pointer;
        }

        .action-link:hover {
            text-decoration: underline;
        }

        .action-link.delete {
            color: var(--error);
        }

        /* Modais */
        .modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: all 0.25s ease;
            z-index: 200;
        }

        .modal.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-content {
            background: #ffffff;
            border-radius: 12px;
            padding: 24px;
            width: 95%;
            max-width: 480px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            transform: scale(0.95);
            transition: all 0.25s ease;
        }

        .modal.active .modal-content {
            transform: scale(1);
        }

        .modal-title-text {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 18px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 14px;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary);
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 24px;
        }

        .btn-modal {
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }

        .btn-modal.cancel {
            background-color: transparent;
            color: #64748b;
        }

        .btn-modal.save {
            background-color: var(--primary);
            color: white;
        }

        .toast-message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #22c55e;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            z-index: 500;
            animation: slide-up-down 4s forwards;
        }

        @keyframes slide-up-down {
            0% { top: -60px; opacity: 0; }
            10% { top: 20px; opacity: 1; }
            90% { top: 20px; opacity: 1; }
            100% { top: -60px; opacity: 0; }
        }
    </style>
    <link rel="stylesheet" href="/api/admin_responsive.css">
</head>
<body>

    <?php if ($message): ?>
        <div class="toast-message">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Sidebar -->
    <aside>
        <div class="sidebar-header">
            <span>🔑</span> PegaChave
        </div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="/admin.php">📊 Dashboard</a>
            </li>
            <li class="sidebar-item active">
                <a href="/admin_chaves.php">🔑 Chaves/Salas</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_usuarios.php">👤 Usuários</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_usuarios_arquivados.php">🗄️ Arquivados</a>
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
                <h1>Gerenciamento de Chaves</h1>
                <p>Cadastre salas, edite informações e gerencie os códigos QR associados.</p>
            </div>
            <button class="btn-add" onclick="openKeyModal()">
                ➕ Nova Chave
            </button>
        </div>

        <div class="content-card">
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Sala/Ambiente</th>
                            <th>Código</th>
                            <th>QR Hash</th>
                            <th>Descrição</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($chaves)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #64748b; padding: 25px;">
                                    Nenhuma chave cadastrada.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($chaves as $c): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($c['nome_sala']); ?></strong></td>
                                    <td><code><?php echo htmlspecialchars($c['codigo_sala']); ?></code></td>
                                    <td><small><?php echo htmlspecialchars($c['qr_code_hash']); ?></small></td>
                                    <td><?php echo htmlspecialchars($c['descricao']); ?></td>
                                    <td>
                                        <span style="color: <?php echo $c['status_disponivel'] ? '#22c55e' : '#ef4444'; ?>; font-weight: bold;">
                                            <?php echo $c['status_disponivel'] ? 'Disponível' : 'Retirada'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a class="action-link" onclick="openEditKeyModal(<?php echo htmlspecialchars(json_encode($c)); ?>)">Editar</a>
                                        <span style="color: #cbd5e1;">|</span>
                                        <a class="action-link delete" onclick="confirmDeleteChave(<?php echo $c['id']; ?>)">Excluir</a>
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
    <div class="modal" id="key-modal">
        <div class="modal-content">
            <h3 class="modal-title-text" id="key-modal-title">Cadastrar Nova Chave</h3>
            <form method="POST" action="admin_chaves.php">
                <?php renderizar_csrf_input(); ?>
                <input type="hidden" name="action" id="key-action" value="add_chave">
                <input type="hidden" name="id" id="key-id">
                
                <div class="form-group">
                    <label for="nome_sala">Nome da Sala</label>
                    <input type="text" name="nome_sala" id="nome_sala" class="form-control" placeholder="Ex: Sala 12 - Lab. Biologia" required>
                </div>
                
                <div class="form-group">
                    <label for="codigo_sala">Código da Sala</label>
                    <input type="text" name="codigo_sala" id="codigo_sala" class="form-control" placeholder="Ex: SALA-12" required>
                </div>
                
                <div class="form-group">
                    <label for="key_qr_code_hash">QR Code Hash (Texto codificado)</label>
                    <input type="text" name="qr_code_hash" id="key_qr_code_hash" class="form-control" placeholder="Ex: chave_sala12_456" required>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea name="descricao" id="descricao" class="form-control" rows="3" placeholder="Informações adicionais..."></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-modal cancel" onclick="closeKeyModal()">Cancelar</button>
                    <button type="submit" class="btn-modal save">Salvar Chave</button>
                </div>
            </form>
        </div>
    </div>

    <form id="delete-form" method="POST" action="admin_chaves.php" style="display: none;">
        <?php renderizar_csrf_input(); ?>
        <input type="hidden" name="action" value="delete_chave">
        <input type="hidden" name="id" id="delete-id">
    </form>

    <script>
        function openKeyModal() {
            document.getElementById('key-modal-title').textContent = "Cadastrar Nova Chave";
            document.getElementById('key-action').value = "add_chave";
            document.getElementById('key-id').value = "";
            document.getElementById('nome_sala').value = "";
            document.getElementById('codigo_sala').value = "";
            document.getElementById('key_qr_code_hash').value = "";
            document.getElementById('descricao').value = "";
            document.getElementById('key-modal').classList.add('active');
        }

        function openEditKeyModal(chave) {
            document.getElementById('key-modal-title').textContent = "Editar Chave";
            document.getElementById('key-action').value = "edit_chave";
            document.getElementById('key-id').value = chave.id;
            document.getElementById('nome_sala').value = chave.nome_sala;
            document.getElementById('codigo_sala').value = chave.codigo_sala;
            document.getElementById('key_qr_code_hash').value = chave.qr_code_hash;
            document.getElementById('descricao').value = chave.descricao;
            document.getElementById('key-modal').classList.add('active');
        }

        function closeKeyModal() {
            document.getElementById('key-modal').classList.remove('active');
        }

        function confirmDeleteChave(id) {
            if (confirm("Deseja realmente deletar esta chave? Todas as movimentações dela serão deletadas.")) {
                document.getElementById('delete-id').value = id;
                document.getElementById('delete-form').submit();
            }
        }
    </script>
    <script src="/api/admin_responsive.js"></script>
</body>
</html>
