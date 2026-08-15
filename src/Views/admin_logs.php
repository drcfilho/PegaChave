<?php
// View para admin_logs.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs de Auditoria - <?php echo htmlspecialchars($nome_escola); ?></title>
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

        .content-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 24px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }

        th {
            padding: 14px 16px;
            background-color: #f8fafc;
            color: #475569;
            font-weight: 700;
            border-bottom: 2px solid var(--border-color);
        }

        td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-color);
            color: #334155;
        }

        tr:hover td {
            background-color: #f8fafc;
        }

        .badge-acao {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 6px;
            background-color: #e2e8f0;
            color: #475569;
            display: inline-block;
        }
    </style>
    <link rel="stylesheet" href="<?= BASE_URL ?>/api/admin_responsive.css">
</head>
<body>

    <!-- Sidebar -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <main>
        <div class="page-header">
            <div class="page-title">
                <h1>Logs de Auditoria</h1>
                <p>Histórico completo de ações administrativas e alterações realizadas no sistema.</p>
            </div>
            <button onclick="exportarCSV()" style="background-color: var(--primary); border: 1px solid var(--primary); color: white; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px; transition: opacity 0.2s;">
                📊 Exportar CSV
            </button>
        </div>

        <div class="content-card">
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Administrador</th>
                            <th>Ação</th>
                            <th>Detalhes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: #64748b; padding: 20px;">
                                    Nenhum log registrado ainda.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td style="white-space: nowrap; color: #64748b; font-size: 13px;">
                                        <?php echo $log['data_formatada']; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($log['admin_nome'] ?? 'Sistema/Público'); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge-acao"><?php echo htmlspecialchars($log['acao']); ?></span>
                                    </td>
                                    <td style="color: #475569;">
                                        <?php echo htmlspecialchars($log['detalhes']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        function exportarCSV() {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('export', 'csv');
            window.location.href = window.location.pathname + '?' + urlParams.toString();
        }
    </script>
    <script src="<?= BASE_URL ?>/api/admin_responsive.js"></script>
</body>
</html>
