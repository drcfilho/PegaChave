<?php
// admin_usuarios_arquivados.php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}
require_once __DIR__ . '/api/db.php';

$message = '';
$messageType = '';
$autenticado = $_SESSION['arquivados_autenticado'] ?? false;

// 1. Processar autenticação para ver a aba
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'auth_arquivados') {
    if (!validar_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = "Token de segurança inválido.";
        $messageType = "error";
    } else {
        $senhaInput = $_POST['senha'] ?? '';
        $adminId = $_SESSION['admin_user_id'];
        
        try {
            $stmt = $pdo->prepare("SELECT senha FROM administradores WHERE id = ?");
            $stmt->execute([$adminId]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($senhaInput, $admin['senha'])) {
                $_SESSION['arquivados_autenticado'] = true;
                $autenticado = true;
                registrar_log($pdo, 'Acesso Autorizado', "Acessou a aba de usuários arquivados.");
            } else {
                $message = "Senha incorreta. Acesso negado.";
                $messageType = "error";
                registrar_log($pdo, 'Tentativa de Acesso Falha', "Tentativa falha de acessar a aba de usuários arquivados.");
            }
        } catch (\PDOException $e) {
            $message = "Erro de banco de dados: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// 2. Processar restauração de usuário
if ($autenticado && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'restore_usuario') {
    if (!validar_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = "Token de segurança inválido.";
        $messageType = "error";
    } else {
        $id = $_POST['id'] ?? '';
        try {
            $stmtUser = $pdo->prepare("SELECT nome, matricula FROM usuarios WHERE id = ?");
            $stmtUser->execute([$id]);
            $userInfo = $stmtUser->fetch();
            $nomeUsuario = $userInfo ? $userInfo['nome'] : "ID $id";

            $stmt = $pdo->prepare("UPDATE usuarios SET excluido = FALSE, ativo = TRUE WHERE id = ?");
            $stmt->execute([$id]);
            
            registrar_log($pdo, 'Restauração de Usuário', "Usuário '$nomeUsuario' foi restaurado do arquivo.");
            $message = "Usuário '$nomeUsuario' restaurado com sucesso!";
            $messageType = "success";
        } catch (\PDOException $e) {
            $message = "Erro ao restaurar usuário: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// 3. Buscar usuários arquivados
$usuariosArquivados = [];
if ($autenticado) {
    try {
        $usuariosArquivados = $pdo->query("
            SELECT u.*, p.nome AS funcao 
            FROM usuarios u 
            JOIN perfis p ON u.perfil_id = p.id 
            WHERE u.excluido = TRUE
            ORDER BY u.nome ASC
        ")->fetchAll();
    } catch (\PDOException $e) {
        $message = "Erro ao buscar dados: " . $e->getMessage();
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arquivados - PegaChave</title>
    <!-- Google Fonts -->
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

        body.dark-theme {
            --bg-color: #0f172a;
            --text-color: #f8fafc;
            --card-bg: #1e293b;
            --border-color: #334155;
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
            max-width: 1200px;
            width: calc(100% - 260px);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title h1 {
            font-size: 26px;
            font-weight: 800;
            color: var(--text-color);
            margin-bottom: 6px;
        }

        .page-title p {
            color: #64748b;
            font-size: 14px;
        }

        /* Content Card */
        .content-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            margin-bottom: 25px;
        }

        /* Forms styling */
        .auth-form {
            max-width: 400px;
            margin: 40px auto;
            text-align: center;
        }

        .auth-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-color);
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            font-size: 14px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: var(--bg-color);
            color: var(--text-color);
            outline: none;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary);
        }

        .btn-submit {
            width: 100%;
            background-color: var(--primary);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
        }

        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }

        th, td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            font-weight: 600;
            color: #64748b;
        }

        .badge {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
        }

        .badge-funcao {
            background-color: rgba(2, 132, 199, 0.1);
            color: #0284c7;
        }

        .btn-action-restore {
            background-color: var(--success);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 12px;
            transition: opacity 0.2s;
        }

        .btn-action-restore:hover {
            opacity: 0.9;
        }

        /* Toast notifications */
        .toast-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: var(--sidebar-bg);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            z-index: 1000;
            font-size: 14px;
            font-weight: 600;
            border-left: 4px solid var(--primary);
            animation: slideIn 0.3s ease-out forwards;
        }

        .toast-message.error {
            border-left-color: var(--error);
        }

        @keyframes slideIn {
            from { transform: translateX(120%); }
            to { transform: translateX(0); }
        }
    </style>
    <link rel="stylesheet" href="/api/admin_responsive.css">
</head>
<body>

    <?php if ($message): ?>
        <div class="toast-message <?php echo $messageType === 'error' ? 'error' : ''; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <script>
            setTimeout(() => {
                const toast = document.querySelector('.toast-message');
                if (toast) toast.remove();
            }, 4000);
        </script>
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
            <li class="sidebar-item">
                <a href="/admin_usuarios.php">👤 Usuários</a>
            </li>
            <li class="sidebar-item active">
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
                <h1>Usuários Arquivados</h1>
                <p>Audite e gerencie os registros históricos de servidores desativados.</p>
            </div>
        </div>

        <?php if (!$autenticado): ?>
            <!-- Formulário de Autenticação -->
            <div class="content-card">
                <div class="auth-form">
                    <div class="auth-icon">🔒</div>
                    <h2 style="margin-bottom: 10px; font-weight: 800;">Acesso Restrito</h2>
                    <p style="color: #64748b; font-size: 14px; margin-bottom: 25px;">Por questões de privacidade, digite sua senha de administrador para visualizar os dados históricos arquivados.</p>
                    
                    <form method="POST" action="admin_usuarios_arquivados.php">
                        <?php renderizar_csrf_input(); ?>
                        <input type="hidden" name="action" value="auth_arquivados">
                        
                        <div class="form-group">
                            <label for="senha">Senha Administrativa</label>
                            <input type="password" name="senha" id="senha" class="form-control" required autofocus placeholder="Digite sua senha">
                        </div>
                        
                        <button type="submit" class="btn-submit">Confirmar Senha</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Lista de Arquivados -->
            <div class="content-card">
                <?php if (empty($usuariosArquivados)): ?>
                    <p style="text-align: center; color: #64748b; padding: 25px 0;">Nenhum usuário arquivado no sistema.</p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>E-mail</th>
                                    <th>Matrícula (ID)</th>
                                    <th>Função</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuariosArquivados as $user): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($user['nome']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><code><?php echo htmlspecialchars($user['matricula']); ?></code></td>
                                        <td><span class="badge badge-funcao"><?php echo htmlspecialchars($user['funcao']); ?></span></td>
                                        <td>
                                            <form method="POST" action="admin_usuarios_arquivados.php" style="display:inline;">
                                                <?php renderizar_csrf_input(); ?>
                                                <input type="hidden" name="action" value="restore_usuario">
                                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn-action-restore">Restaurar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

    <script>
        // Inicializar Tema
        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.body.classList.add('dark-theme');
            }
        })();
    </script>
    <script src="/api/admin_responsive.js"></script>
</body>
</html>
