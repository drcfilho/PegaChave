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
    <link rel="stylesheet" href="<?= BASE_URL ?>/api/admin_responsive.css?v=<?= time() ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        corePlugins: {
          preflight: true,
        },
        theme: {
          extend: {
            colors: {
              primary: 'var(--primary)',
              secondary: 'var(--sidebar-bg)'
            }
          }
        }
      }
    </script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans flex min-h-screen">

    <!-- Sidebar -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 ml-0 lg:ml-[260px] p-6 md:p-10 transition-all duration-300">
        <div class="flex justify-between items-center mb-8 flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 mb-1">Logs de Auditoria</h1>
                <p class="text-sm text-slate-500">Histórico completo de ações administrativas e alterações realizadas no sistema.</p>
            </div>
            <button onclick="exportarCSV()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                📊 Exportar CSV
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden px-6 py-5 mb-8">
            <div style="overflow-x: auto;">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Data/Hora</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Administrador</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Ação</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Detalhes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: #64748b; padding: 25px;">
                                    Nenhum log registrado ainda.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm" style="white-space: nowrap; color: #64748b; font-size: 13px;">
                                        <?php echo $log['data_formatada']; ?>
                                    </td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm">
                                        <strong><?php echo htmlspecialchars($log['admin_nome'] ?? 'Sistema/Público'); ?></strong>
                                    </td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm">
                                        <span class="text-[11px] font-bold px-2 py-1 rounded-md bg-slate-200 text-slate-700"><?php echo htmlspecialchars($log['acao']); ?></span>
                                    </td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm" style="color: #475569;">
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
