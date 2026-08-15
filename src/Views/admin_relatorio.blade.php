<?php
// View para admin_relatorio.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios de Movimentação - PegaChave</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/api/admin_responsive.css?v=<?= time() ?>">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/dist/app.min.css?v=<?= time() ?>">
</head>
<body class="bg-slate-50 text-slate-800 font-sans flex min-h-screen">

    <!-- Sidebar -->
    @include('partials.sidebar')

    <!-- Main Content -->
    <main class="flex-1 ml-0 lg:ml-[260px] p-6 md:p-10 transition-all duration-300 print:ml-0 print:p-0">
        <div class="flex justify-between items-center mb-8 flex-wrap gap-4 print:hidden">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 mb-1">Relatório de Movimentação</h1>
                <p class="text-sm text-slate-500">Monitore e audite o histórico completo de retiradas e devoluções.</p>
            </div>
            <div class="flex gap-2.5 print-hidden">
                <button onclick="exportarCSV()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                    📊 Exportar CSV
                </button>
                <button onclick="exportarPDF()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                    📄 Exportar PDF
                </button>
                <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-900 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                    🖨️ Imprimir Página
                </button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-6 py-5 mb-6 print-hidden">
            <form method="GET" action="<?= BASE_URL ?>/admin/relatorio" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-[repeat(auto-fit,minmax(200px,1fr))_auto] gap-4 items-end">
                <div class="flex flex-col gap-1.5">
                    <label for="chave_id" class="text-xs font-bold text-slate-500 uppercase">Chave/Sala</label>
                    <select name="chave_id" id="chave_id" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-slate-50">
                        <option value="">Todas</option>
                        <?php foreach ($chaves as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $chave_id == $c['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['nome_sala']); ?> (<?php echo htmlspecialchars($c['codigo_sala']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="usuario_id" class="text-xs font-bold text-slate-500 uppercase">Usuário</label>
                    <select name="usuario_id" id="usuario_id" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-slate-50">
                        <option value="">Todos</option>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?php echo $u['id']; ?>" <?php echo $usuario_id == $u['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($u['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="data_inicio" class="text-xs font-bold text-slate-500 uppercase">Data Início</label>
                    <input type="date" name="data_inicio" id="data_inicio" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-slate-50" value="<?php echo htmlspecialchars($data_inicio ?? ''); ?>">
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="data_fim" class="text-xs font-bold text-slate-500 uppercase">Data Fim</label>
                    <input type="date" name="data_fim" id="data_fim" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-slate-50" value="<?php echo htmlspecialchars($data_fim ?? ''); ?>">
                </div>

                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold py-2 px-5 rounded-lg transition-colors">Filtrar</button>
            </form>
        </div>

        <!-- Tabela de Resultados -->
        <div class="content-card bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden px-6 py-5 mb-8">
            <div style="overflow-x: auto;">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Chave/Sala</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Usuário (Matrícula)</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Data Retirada</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Data Devolução</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Status</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($movimentacoes)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #64748b; padding: 25px;">
                                    Nenhum registro de movimentação encontrado para os filtros selecionados.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($movimentacoes as $m): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><strong><?php echo htmlspecialchars($m['nome_sala']); ?></strong> (<?php echo htmlspecialchars($m['codigo_sala']); ?>)</td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><?php echo htmlspecialchars($m['usuario_nome']); ?> (<?php echo htmlspecialchars($m['usuario_matricula']); ?>)</td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><?php echo date('d/m/Y H:i', strtotime($m['data_retirada'])); ?></td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><?php echo $m['data_devolucao'] ? date('d/m/Y H:i', strtotime($m['data_devolucao'])) : '-'; ?></td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm">
                                        <?php if($m['data_devolucao']): ?>
                                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full uppercase bg-green-100 text-green-700">Devolvida</span>
                                        <?php else: ?>
                                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full uppercase bg-red-100 text-red-700">Em Uso</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><small><?php echo htmlspecialchars($m['observacao'] ?? '-'); ?></small></td>
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

        function exportarPDF() {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('export', 'pdf');
            window.open(window.location.pathname + '?' + urlParams.toString(), '_blank');
        }
    </script>
    <script src="<?= BASE_URL ?>/api/admin_responsive.js"></script>
</body>
</html>
