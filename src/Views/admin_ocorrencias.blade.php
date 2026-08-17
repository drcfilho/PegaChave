<?php
// View para admin_ocorrencias.blade.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ocorrências e Avarias - PegaChave</title>
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
                <h1 class="text-2xl font-extrabold text-slate-900 mb-1">Ocorrências e Avarias</h1>
                <p class="text-sm text-slate-500">Veja e audite danos, ocorrências e chaves não devolvidas em conformidade.</p>
            </div>
            <div class="flex gap-2.5 print-hidden">
                <button onclick="exportarCSV()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                    📊 Exportar CSV
                </button>
                <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-900 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                    🖨️ Imprimir Página
                </button>
            </div>
        </div>

        <!-- Alerta de Escopo de Registros -->
        <?php if (!empty($limite)): ?>
            <div class="bg-blue-50 border border-blue-200 border-l-4 border-l-blue-500 text-blue-800 p-4 rounded-lg shadow-sm mb-6 flex items-center gap-3 text-sm font-semibold">
                <span class="text-xl">ℹ️</span>
                <div>
                    Mostrando as <strong>últimas 30 ocorrências registradas</strong> por padrão. Utilize os filtros abaixo para realizar buscas por períodos completos ou alvos específicos.
                </div>
            </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-6 py-5 mb-6 print-hidden">
            <form method="GET" action="<?= BASE_URL ?>/admin/ocorrencias" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-[repeat(auto-fit,minmax(200px,1fr))_auto] gap-4 items-end">
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
                    <label for="usuario_id" class="text-xs font-bold text-slate-500 uppercase">Possuidor da Chave</label>
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
                    <label for="data_inicio" class="text-xs font-bold text-slate-500 uppercase">Período Devolução Início</label>
                    <input type="date" name="data_inicio" id="data_inicio" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-slate-50" value="<?php echo htmlspecialchars($data_inicio ?? ''); ?>">
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="data_fim" class="text-xs font-bold text-slate-500 uppercase">Período Devolução Fim</label>
                    <input type="date" name="data_fim" id="data_fim" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-slate-50" value="<?php echo htmlspecialchars($data_fim ?? ''); ?>">
                </div>

                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold py-2 px-5 rounded-lg transition-colors">Filtrar</button>
            </form>
        </div>

        <!-- Tabela de Ocorrências -->
        <div class="content-card bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden px-6 py-5 mb-8">
            <div style="overflow-x: auto;">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold w-[150px]">Chave/Sala</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold w-[180px]">Usuário da Posse</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold w-[150px]">Retirada / Devolução</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold w-[150px]">Quem Registrou</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Mensagem / Ocorrência</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ocorrencias)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-slate-400 py-10 font-medium">
                                    Nenhuma ocorrência encontrada com os filtros selecionados.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ocorrencias as $o): ?>
                                <?php 
                                    // Parsear quem registrou o log
                                    // PegaChave registra como: "🚨 OCORRÊNCIA (NomeAdmin): Detalhes da ocorrência"
                                    $registrou = 'Sistema / Quiosque';
                                    $detalhes = $o['observacao'];
                                    
                                    if (preg_match('/🚨 OCORRÊNCIA \((.*?)\):\s*(.*)/is', $o['observacao'], $matches)) {
                                        $registrou = $matches[1];
                                        $detalhes = $matches[2];
                                    } elseif (preg_match('/Devolvida manualmente por:\s*(.*)/i', $o['observacao'], $matches)) {
                                        $registrou = $matches[1];
                                        $detalhes = 'Devolução Manual (Normal)';
                                    }
                                ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm">
                                        <div class="font-bold text-slate-800"><?php echo htmlspecialchars($o['nome_sala']); ?></div>
                                        <div class="text-xs text-slate-400">Cód: <?php echo htmlspecialchars($o['codigo_sala']); ?></div>
                                    </td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm">
                                        <div class="font-semibold text-slate-850"><?php echo htmlspecialchars($o['usuario_nome']); ?></div>
                                        <div class="text-xs text-slate-400">Matrícula: <?php echo htmlspecialchars($o['usuario_matricula']); ?></div>
                                    </td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm text-slate-500">
                                        <div class="text-xs font-semibold text-emerald-600">📥 <?php echo date('d/m/H:i', strtotime($o['data_retirada'])); ?></div>
                                        <div class="text-xs font-semibold text-blue-600 mt-1">📤 <?php echo $o['data_devolucao'] ? date('d/m/H:i', strtotime($o['data_devolucao'])) : '-'; ?></div>
                                    </td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm font-semibold text-slate-700">
                                        🛡️ <?php echo htmlspecialchars($registrou); ?>
                                    </td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm">
                                        <span class="inline-block bg-red-100 text-red-800 text-xs px-2.5 py-1 rounded-full font-bold shadow-sm max-w-full break-words">
                                            ⚠️ <?php echo htmlspecialchars($detalhes); ?>
                                        </span>
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
            window.location.href = `<?= BASE_URL ?>/admin/ocorrencias?${urlParams.toString()}`;
        }
    </script>
</body>
</html>
