<?php
// View do Dashboard Admin
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Administrador <?php echo htmlspecialchars($nome_escola); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/api/admin_responsive.css?v=<?= time() ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        corePlugins: {
          preflight: true,
        },
        theme: {
          extend: {
            fontFamily: {
              sans: ['Inter', 'sans-serif'],
            },
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
    @include('partials.sidebar')

    <!-- Main Content -->
    <main class="flex-1 ml-0 lg:ml-[260px] p-6 md:p-10 transition-all duration-300">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Painel Administrativo</h1>
                <p class="text-slate-500 text-sm mt-1 font-medium">Monitoramento e estatísticas rápidas do claviculário digital.</p>
            </div>
        </div>

        <div id="alert-banner-container">
            <?php if ($pendentesCount > 0): ?>
                <div class="bg-red-50 border border-red-200 border-l-4 border-l-red-500 text-red-800 p-4 rounded-lg shadow-sm mb-6 flex items-center justify-between font-semibold text-sm">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">⚠️</span>
                        <div>
                            Atenção: Existem <?php echo $pendentesCount; ?> chaves com atraso de devolução (em uso há mais de 8 horas)!
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Estatísticas Rápidas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-gradient-to-br from-green-100 to-green-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Disponíveis</h3>
                        <div class="text-4xl font-extrabold text-green-600" id="stat-disponiveis"><?php echo $chavesDisponiveis; ?></div>
                    </div>
                    <div class="text-4xl">🟢</div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-gradient-to-br from-red-100 to-red-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Em Uso</h3>
                        <div class="text-4xl font-extrabold text-red-500" id="stat-em-uso"><?php echo $chavesEmUsoCount; ?></div>
                    </div>
                    <div class="text-4xl">🔴</div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-gradient-to-br from-amber-100 to-amber-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Atrasadas</h3>
                        <div class="text-4xl font-extrabold text-amber-500" id="stat-atrasadas"><?php echo $pendentesCount; ?></div>
                    </div>
                    <div class="text-4xl">⚠️</div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-gradient-to-br from-indigo-100 to-indigo-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Retiradas Hoje</h3>
                        <div class="text-4xl font-extrabold text-indigo-500" id="stat-retiradas-hoje"><?php echo $retiradasHoje; ?></div>
                    </div>
                    <div class="text-4xl drop-shadow-sm">🔑</div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-gradient-to-br from-emerald-100 to-emerald-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Devoluções Hoje</h3>
                        <div class="text-4xl font-extrabold text-emerald-500" id="stat-devolucoes-hoje"><?php echo $devolucoesHoje; ?></div>
                    </div>
                    <div class="text-4xl drop-shadow-sm">↩</div>
                </div>
            </div>
        </div>

        <!-- Gráficos Analíticos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-800 mb-6">Fluxo de Retiradas (Últimos 7 dias)</h2>
                <div class="relative h-64 w-full">
                    <canvas id="chartFluxoSemanal"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-800 mb-6">Top 5 Salas Mais Utilizadas</h2>
                <div class="relative h-64 w-full">
                    <canvas id="chartTopSalas"></canvas>
                </div>
            </div>
        </div>

        <!-- Chaves em Uso -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-lg font-bold text-slate-800">Chaves em Uso Atualmente</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider">
                            <th class="px-6 py-4 font-bold">Chave/Sala</th>
                            <th class="px-6 py-4 font-bold">Portador</th>
                            <th class="px-6 py-4 font-bold">Cargo/Função</th>
                            <th class="px-6 py-4 font-bold">Hora da Retirada</th>
                            <th class="px-6 py-4 font-bold">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-chaves-em-uso" class="divide-y divide-slate-100 text-sm">
                        <?php if (empty($chavesEmUso)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-400 font-medium">
                                    Nenhuma chave retirada no momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($chavesEmUso as $c): ?>
                                 <tr class="hover:bg-slate-50 transition-colors <?php echo $c['atrasada'] ? 'bg-red-50 hover:bg-red-100/70' : ''; ?>">
                                     <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-800"><?php echo htmlspecialchars($c['nome_sala']); ?></div>
                                        <div class="text-slate-500 text-xs mt-0.5">Código: <?php echo htmlspecialchars($c['codigo_sala']); ?></div>
                                     </td>
                                     <td class="px-6 py-4 whitespace-nowrap font-medium text-slate-700"><?php echo htmlspecialchars($c['usuario_nome']); ?></td>
                                     <td class="px-6 py-4 whitespace-nowrap text-slate-500"><?php echo htmlspecialchars($c['usuario_perfil']); ?></td>
                                     <td class="px-6 py-4 whitespace-nowrap text-slate-600">
                                         <div class="flex items-center gap-2">
                                             <?php echo htmlspecialchars($c['desde']); ?>
                                             <?php if ($c['atrasada']): ?>
                                                 <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-md shadow-sm">ATRASADA ⚠️</span>
                                             <?php endif; ?>
                                         </div>
                                     </td>
                                     <td class="px-6 py-4 whitespace-nowrap">
                                        <button onclick="devolverChave(<?php echo $c['chave_id']; ?>)" class="text-red-500 font-bold hover:text-red-700 transition-colors cursor-pointer">
                                            Devolver Manual
                                        </button>
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
        let chartFluxoInstance = null;
        let chartSalasInstance = null;

        async function devolverChave(chaveId) {
            if (!confirm("Deseja forçar a devolução manual desta chave?")) return;
            try {
                const response = await fetch('<?= BASE_URL ?>/api/status_chaves.php');
                const result = await response.json();
                const chaveObj = result.data.find(c => c.id == chaveId);
                
                if (chaveObj && chaveObj.qr_code_hash) {
                    const scanRes = await fetch('<?= BASE_URL ?>/api/processar_scan', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ qr_code: chaveObj.qr_code_hash })
                    });
                    const scanData = await scanRes.json();
                    alert(scanData.message);
                    refreshDashboard();
                }
            } catch (err) {
                console.error(err);
                alert("Erro ao processar devolução.");
            }
        }

        async function refreshDashboard() {
            try {
                const response = await fetch('<?= BASE_URL ?>/api/dashboard_data.php');
                const result = await response.json();
                if (result.status !== 'success') return;

                const data = result.data;

                // 1. Atualizar cards de estatísticas
                document.getElementById('stat-disponiveis').textContent = data.chavesDisponiveis;
                document.getElementById('stat-em-uso').textContent = data.chavesEmUsoCount;
                document.getElementById('stat-atrasadas').textContent = data.pendentesCount;
                document.getElementById('stat-retiradas-hoje').textContent = data.retiradasHoje;
                document.getElementById('stat-devolucoes-hoje').textContent = data.devolucoesHoje;

                // 2. Atualizar banner de alerta
                const bannerContainer = document.getElementById('alert-banner-container');
                if (data.pendentesCount > 0) {
                    bannerContainer.innerHTML = `
                        <div class="bg-red-50 border border-red-200 border-l-4 border-l-red-500 text-red-800 p-4 rounded-lg shadow-sm mb-6 flex items-center justify-between font-semibold text-sm">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">⚠️</span>
                                <div>
                                    Atenção: Existem ${data.pendentesCount} chaves com atraso de devolução (em uso há mais de 8 horas)!
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    bannerContainer.innerHTML = '';
                }

                // 3. Atualizar tabela de chaves em uso
                const tbody = document.getElementById('tabela-chaves-em-uso');
                if (data.chavesEmUso.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400 font-medium">
                                Nenhuma chave retirada no momento.
                            </td>
                        </tr>
                    `;
                } else {
                    let html = '';
                    data.chavesEmUso.forEach(c => {
                        const styleTr = c.atrasada == 1 ? 'bg-red-50 hover:bg-red-100/70' : 'hover:bg-slate-50 transition-colors';
                        const tagAtrasada = c.atrasada == 1 ? '<span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-md shadow-sm">ATRASADA ⚠️</span>' : '';
                        html += `
                            <tr class="${styleTr}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-800">${escapeHtml(c.nome_sala)}</div>
                                    <div class="text-slate-500 text-xs mt-0.5">Código: ${escapeHtml(c.codigo_sala)}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-slate-700">${escapeHtml(c.usuario_nome)}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-500">${escapeHtml(c.usuario_perfil)}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600">
                                    <div class="flex items-center gap-2">
                                        ${escapeHtml(c.desde)}
                                        ${tagAtrasada}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button onclick="devolverChave(${c.chave_id})" class="text-red-500 font-bold hover:text-red-700 transition-colors cursor-pointer">
                                        Devolver Manual
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    tbody.innerHTML = html;
                }

                // 4. Atualizar gráficos
                if (chartFluxoInstance) {
                    chartFluxoInstance.data.labels = data.fluxoSemanal.map(f => f.dia_mes);
                    chartFluxoInstance.data.datasets[0].data = data.fluxoSemanal.map(f => f.total);
                    chartFluxoInstance.update();
                }

                if (chartSalasInstance) {
                    chartSalasInstance.data.labels = data.topSalas.map(s => s.nome_sala);
                    chartSalasInstance.data.datasets[0].data = data.topSalas.map(s => s.total_retiradas);
                    chartSalasInstance.update();
                }

            } catch (err) {
                console.error("Erro no polling do dashboard:", err);
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }

        document.addEventListener('DOMContentLoaded', () => {
            // 1. Gráfico de Fluxo Semanal (Linha)
            const ctxFluxo = document.getElementById('chartFluxoSemanal').getContext('2d');
            chartFluxoInstance = new Chart(ctxFluxo, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_column($fluxoSemanal, 'dia_mes')); ?>,
                    datasets: [{
                        label: 'Chaves Retiradas',
                        data: <?php echo json_encode(array_column($fluxoSemanal, 'total')); ?>,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#6366f1',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 },
                            grid: { color: '#f1f5f9' },
                            border: { display: false }
                        },
                        x: {
                            grid: { display: false },
                            border: { display: false }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                }
            });

            // 2. Gráfico de Top 5 Salas (Barras Horizontais)
            const ctxSalas = document.getElementById('chartTopSalas').getContext('2d');
            chartSalasInstance = new Chart(ctxSalas, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($topSalas, 'nome_sala')); ?>,
                    datasets: [{
                        label: 'Total de Retiradas',
                        data: <?php echo json_encode(array_column($topSalas, 'total_retiradas')); ?>,
                        backgroundColor: 'rgba(2, 132, 199, 0.85)',
                        hoverBackgroundColor: 'rgba(2, 132, 199, 1)',
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 },
                            grid: { color: '#f1f5f9' },
                            border: { display: false }
                        },
                        y: {
                            grid: { display: false },
                            border: { display: false }
                        }
                    }
                }
            });

            // Polling rápido a cada 2 segundos para atualizações rápidas
            setInterval(refreshDashboard, 2000);
        });
    </script>
    <script src="<?= BASE_URL ?>/api/admin_responsive.js"></script>
</body>
</html>
