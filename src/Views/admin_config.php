<?php
// View para admin_config.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações do Sistema - PegaChave</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #f1f5f9;
            --text-color: #1e293b;
            --sidebar-bg: #0f172a;
            --sidebar-active: #0284c7;
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

        .content-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 28px;
            max-width: 600px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .form-control {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 14px;
            outline: none;
            background-color: #f8fafc;
        }

        .form-control:focus {
            border-color: var(--primary);
        }

        .color-pickers-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .color-input-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .color-picker {
            width: 50px;
            height: 50px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            cursor: pointer;
            padding: 0;
            background: none;
        }

        .btn-save {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 15px;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.2s;
        }

        .btn-save:hover {
            filter: brightness(0.9);
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
    <link rel="stylesheet" href="api/admin_responsive.css">
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
                <a href="<?= BASE_URL ?>/admin">📊 Dashboard</a>
            </li>
            <li class="sidebar-item">
                <a href="<?= BASE_URL ?>/admin/chaves">🔑 Chaves/Salas</a>
            </li>
            <li class="sidebar-item">
                <a href="<?= BASE_URL ?>/admin/usuarios">👤 Usuários</a>
            </li>
            <li class="sidebar-item">
                <a href="<?= BASE_URL ?>/admin/usuarios_arquivados">🗄️ Arquivados</a>
            </li>
            <li class="sidebar-item">
                <a href="<?= BASE_URL ?>/admin/reservas">📅 Agendamentos</a>
            </li>
            <li class="sidebar-item">
                <a href="<?= BASE_URL ?>/admin/restricoes">🔒 Restrições</a>
            </li>
            <li class="sidebar-item">
                <a href="<?= BASE_URL ?>/admin/gerar_qr">🖨️ Gerar QR Codes</a>
            </li>
            <li class="sidebar-item">
                <a href="<?= BASE_URL ?>/admin/consulta">🔍 Consultar Disponibilidade</a>
            </li>
            <li class="sidebar-item">
                <a href="<?= BASE_URL ?>/admin/relatorio">📝 Relatório Geral</a>
            </li>
            <li class="sidebar-item">
                <a href="<?= BASE_URL ?>/admin/logs">📋 Logs de Auditoria</a>
            </li>
            <li class="sidebar-item active">
                <a href="<?= BASE_URL ?>/admin/config">⚙️ Configurações</a>
            </li>
            <li class="sidebar-item">
                <a href="<?= BASE_URL ?>/admin_logout.php" style="color: #ef4444;">🚪 Sair</a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <a href="<?= BASE_URL ?>/" class="btn-kiosk">🖥️ Voltar ao Quiosque</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main>
        <div class="page-header">
            <div class="page-title">
                <h1>Configurações do Sistema</h1>
                <p>Personalize o nome da instituição e altere a identidade visual das telas do quiosque e do painel.</p>
            </div>
        </div>

        <div class="content-card">
            <form method="POST" action="<?= BASE_URL ?>/admin/config">
                <?php renderizar_csrf_input(); ?>
                <input type="hidden" name="action" value="update_config">
                
                <div class="form-group">
                    <label for="nome_escola">Nome da Escola / Instituição</label>
                    <input type="text" name="nome_escola" id="nome_escola" class="form-control" value="<?php echo htmlspecialchars($nome_escola); ?>" required>
                </div>

                <div class="color-pickers-row">
                    <div class="form-group">
                        <label for="cor_primaria">Cor Primária (Destaques/Botões)</label>
                        <div class="color-input-wrapper">
                            <input type="color" name="cor_primaria" id="cor_primaria" class="color-picker" value="<?php echo htmlspecialchars($cor_primaria); ?>">
                            <span style="font-family: monospace; font-size: 14px;"><?php echo htmlspecialchars($cor_primaria); ?></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cor_secundaria">Cor Secundária (Cabeçalho/Menu)</label>
                        <div class="color-input-wrapper">
                            <input type="color" name="cor_secundaria" id="cor_secundaria" class="color-picker" value="<?php echo htmlspecialchars($cor_secundaria); ?>">
                            <span style="font-family: monospace; font-size: 14px;"><?php echo htmlspecialchars($cor_secundaria); ?></span>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <label for="limite_chaves">Limite Máximo de Chaves sob Posse Simultânea (por Usuário)</label>
                    <input type="number" name="limite_chaves" id="limite_chaves" class="form-control" min="0" value="<?php echo htmlspecialchars($limite_chaves ?? 0); ?>" style="max-width: 150px;" required>
                    <p style="font-size: 12px; color: #64748b; margin-top: 6px;">Defina como 0 para permitir retiradas ilimitadas.</p>
                </div>

                <button type="submit" class="btn-save">Salvar Alterações</button>
            </form>
        </div>
    </main>

    <script>
        // Atualizar os códigos hexadecimais na tela ao escolher cores
        document.getElementById('cor_primaria').addEventListener('input', function(e) {
            e.target.nextElementSibling.textContent = e.target.value.toUpperCase();
        });
        document.getElementById('cor_secundaria').addEventListener('input', function(e) {
            e.target.nextElementSibling.textContent = e.target.value.toUpperCase();
        });
    </script>
    <script src="/api/admin_responsive.js"></script>
</body>
</html>
