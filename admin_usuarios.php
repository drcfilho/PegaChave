<?php
// admin_usuarios.php
require_once __DIR__ . '/api/db.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    try {
        if ($action === 'add_usuario') {
            $nome = $_POST['nome'] ?? '';
            $email = $_POST['email'] ?? '';
            $matricula = $_POST['matricula'] ?? '';
            $perfil_id = $_POST['perfil_id'] ?? '';
            $qr_code_hash = $_POST['qr_code_hash'] ?? '';

            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, matricula, perfil_id, qr_code_hash) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $matricula, $perfil_id, $qr_code_hash]);
            $message = "Usuário '$nome' cadastrado com sucesso!";
            $messageType = "success";
        }

        elseif ($action === 'edit_usuario') {
            $id = $_POST['id'] ?? '';
            $nome = $_POST['nome'] ?? '';
            $email = $_POST['email'] ?? '';
            $matricula = $_POST['matricula'] ?? '';
            $perfil_id = $_POST['perfil_id'] ?? '';
            $qr_code_hash = $_POST['qr_code_hash'] ?? '';
            $ativo = isset($_POST['ativo']) ? 1 : 0;

            $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, matricula = ?, perfil_id = ?, qr_code_hash = ?, ativo = ? WHERE id = ?");
            $stmt->execute([$nome, $email, $matricula, $perfil_id, $qr_code_hash, $ativo, $id]);
            $message = "Usuário '$nome' atualizado com sucesso!";
            $messageType = "success";
        }

        elseif ($action === 'delete_usuario') {
            $id = $_POST['id'] ?? '';
            // Remover dependências de movimentações
            $pdo->prepare("DELETE FROM movimentacoes WHERE usuario_id = ?")->execute([$id]);
            
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Usuário removido.";
            $messageType = "success";
        }
    } catch (\PDOException $e) {
        $message = "Erro ao salvar dados: " . $e->getMessage();
        $messageType = "error";
    }
}

try {
    $usuarios = $pdo->query("
        SELECT u.*, p.nome AS funcao 
        FROM usuarios u 
        JOIN perfis p ON u.perfil_id = p.id 
        ORDER BY u.nome ASC
    ")->fetchAll();

    $perfis = $pdo->query("SELECT * FROM perfis ORDER BY nome ASC")->fetchAll();
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
    <title>Gerenciamento de Usuários - PegaChave</title>
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
            <li class="sidebar-item">
                <a href="/admin_chaves.php">🔑 Chaves/Salas</a>
            </li>
            <li class="sidebar-item active">
                <a href="/admin_usuarios.php">👤 Usuários</a>
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
                <a href="/admin_config.php">⚙️ Configurações</a>
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
                <h1>Gerenciamento de Usuários</h1>
                <p>Cadastre servidores e professores autorizados a realizar a movimentação de chaves.</p>
            </div>
            <button class="btn-add" onclick="openUserModal()">
                ➕ Novo Usuário
            </button>
        </div>

        <div class="content-card">
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>ID (Matrícula)</th>
                            <th>Função</th>
                            <th>E-mail</th>
                            <th>QR Hash</th>
                            <th>Ativo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: #64748b; padding: 25px;">
                                    Nenhum usuário cadastrado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($u['nome']); ?></strong></td>
                                    <td><code><?php echo htmlspecialchars($u['matricula']); ?></code></td>
                                    <td><?php echo htmlspecialchars($u['funcao']); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><small><?php echo htmlspecialchars($u['qr_code_hash']); ?></small></td>
                                    <td>
                                        <span style="color: <?php echo $u['ativo'] ? '#22c55e' : '#ef4444'; ?>; font-weight: bold;">
                                            <?php echo $u['ativo'] ? 'Sim' : 'Não'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a class="action-link" onclick="openEditUserModal(<?php echo htmlspecialchars(json_encode($u)); ?>)">Editar</a>
                                        <span style="color: #cbd5e1;">|</span>
                                        <a class="action-link delete" onclick="confirmDeleteUser(<?php echo $u['id']; ?>)">Excluir</a>
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
    <div class="modal" id="user-modal">
        <div class="modal-content">
            <h3 class="modal-title-text" id="user-modal-title">Cadastrar Novo Usuário</h3>
            <form method="POST" action="admin_usuarios.php">
                <input type="hidden" name="action" id="user-action" value="add_usuario">
                <input type="hidden" name="id" id="user-id">
                
                <div class="form-group">
                    <label for="nome">Nome Completo</label>
                    <input type="text" name="nome" id="nome" class="form-control" placeholder="Ex: Carlos Silva" required>
                </div>
                
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Ex: carlos@escola.edu.br" required>
                </div>

                <div class="form-group">
                    <label for="matricula">Matrícula (ID)</label>
                    <input type="text" name="matricula" id="matricula" class="form-control" placeholder="Ex: 101" required>
                </div>

                <div class="form-group">
                    <label for="perfil_id">Perfil / Função</label>
                    <select name="perfil_id" id="perfil_id" class="form-control" required>
                        <?php foreach ($perfis as $p): ?>
                            <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="user_qr_code_hash">QR Code Hash (Texto codificado)</label>
                    <input type="text" name="qr_code_hash" id="user_qr_code_hash" class="form-control" placeholder="Ex: user_carlos_123" required>
                </div>

                <div class="form-group" id="group-ativo" style="display: none; align-items: center; gap: 8px;">
                    <input type="checkbox" name="ativo" id="ativo" value="1" checked>
                    <label for="ativo" style="margin-bottom: 0;">Usuário Ativo</label>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-modal cancel" onclick="closeUserModal()">Cancelar</button>
                    <button type="submit" class="btn-modal save">Salvar Usuário</button>
                </div>
            </form>
        </div>
    </div>

    <form id="delete-form" method="POST" action="admin_usuarios.php" style="display: none;">
        <input type="hidden" name="action" value="delete_usuario">
        <input type="hidden" name="id" id="delete-id">
    </form>

    <script>
        function openUserModal() {
            document.getElementById('user-modal-title').textContent = "Cadastrar Novo Usuário";
            document.getElementById('user-action').value = "add_usuario";
            document.getElementById('user-id').value = "";
            document.getElementById('nome').value = "";
            document.getElementById('email').value = "";
            document.getElementById('matricula').value = "";
            document.getElementById('perfil_id').value = "2"; // Professor default
            document.getElementById('user_qr_code_hash').value = "";
            document.getElementById('group-ativo').style.display = "none";
            document.getElementById('user-modal').classList.add('active');
        }

        function openEditUserModal(user) {
            document.getElementById('user-modal-title').textContent = "Editar Usuário";
            document.getElementById('user-action').value = "edit_usuario";
            document.getElementById('user-id').value = user.id;
            document.getElementById('nome').value = user.nome;
            document.getElementById('email').value = user.email;
            document.getElementById('matricula').value = user.matricula;
            document.getElementById('perfil_id').value = user.perfil_id;
            document.getElementById('user_qr_code_hash').value = user.qr_code_hash;
            document.getElementById('ativo').checked = user.ativo == 1;
            document.getElementById('group-ativo').style.display = "flex";
            document.getElementById('user-modal').classList.add('active');
        }

        function closeUserModal() {
            document.getElementById('user-modal').classList.remove('active');
        }

        function confirmDeleteUser(id) {
            if (confirm("Deseja realmente deletar este usuário?")) {
                document.getElementById('delete-id').value = id;
                document.getElementById('delete-form').submit();
            }
        }
    </script>
</body>
</html>
