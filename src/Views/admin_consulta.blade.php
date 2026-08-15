<?php
// View para admin_consulta.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Chaves - Administrador</title>
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
    @include('partials.sidebar')

    <!-- Main Content -->
    <main class="flex-1 ml-0 lg:ml-[260px] p-6 md:p-10 transition-all duration-300">
        <div class="flex justify-between items-center mb-8 flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 mb-1">Consulta de Disponibilidade</h1>
                <p class="text-sm text-slate-500">Verifique o status de chaves e salas em tempo real de forma veloz.</p>
            </div>
            
            <div class="w-full md:w-auto relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">🔍</span>
                <input type="text" id="search" class="w-full md:w-auto border border-slate-300 rounded-lg py-2 pr-4 pl-10 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 min-w-[300px] shadow-sm" placeholder="Pesquisar por Sala, Código ou Nome..." onkeyup="filterCards()">
            </div>
        </div>

        <!-- Lista de Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5" id="keys-grid">
            <?php foreach ($chaves as $c): ?>
                <div class="card-chave bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex flex-col justify-between" data-name="<?php echo strtolower(htmlspecialchars($c['nome_sala'] . ' ' . $c['codigo_sala'] . ' ' . ($c['reservado_por_nome'] ?? ''))); ?>">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 mb-0.5"><?php echo htmlspecialchars($c['nome_sala']); ?></h3>
                                <span class="text-[11px] font-semibold text-slate-500 uppercase">Código: <?php echo htmlspecialchars($c['codigo_sala']); ?></span>
                            </div>
                            <span class="text-[10px] font-extrabold px-2 py-1 rounded-full uppercase <?php echo $c['status_disponivel'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                <?php echo $c['status_disponivel'] ? 'Disponível' : 'Em Uso'; ?>
                            </span>
                        </div>

                        <?php if ($c['descricao']): ?>
                            <p class="text-xs text-slate-500 italic mb-3 -mt-1">
                                <?php echo htmlspecialchars($c['descricao']); ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <?php if (!$c['status_disponivel']): ?>
                        <div class="border-t border-slate-100 pt-3 mt-3 text-[13px] text-slate-600 space-y-1.5">
                            <div class="flex items-center gap-1.5">
                                <span>👤</span> <span><strong class="text-slate-900">Com:</strong> <?php echo htmlspecialchars($c['reservado_por_nome']); ?> (<?php echo htmlspecialchars($c['reservado_por_perfil']); ?>)</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span>⏰</span> <span><strong class="text-slate-900">Retirada:</strong> <?php echo htmlspecialchars($c['data_retirada_formatada']); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
        function filterCards() {
            const query = document.getElementById('search').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.card-chave');
            
            cards.forEach(card => {
                const searchData = card.getAttribute('data-name');
                if (searchData.includes(query)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>

    <script src="<?= BASE_URL ?>/api/admin_responsive.js"></script>
</body>
</html>
