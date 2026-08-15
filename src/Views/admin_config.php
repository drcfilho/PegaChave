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
            },
            keyframes: {
              slideUpDown: {
                '0%, 100%': { top: '-60px', opacity: '0' },
                '10%, 90%': { top: '20px', opacity: '1' }
              }
            },
            animation: {
              'slide-up-down': 'slideUpDown 4s forwards'
            }
          }
        }
      }
    </script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans flex min-h-screen">

    <?php if ($message): ?>
        <div class="fixed left-1/2 -translate-x-1/2 bg-green-500 text-white px-6 py-3 rounded-lg font-semibold shadow-lg z-[500] animate-slide-up-down">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Sidebar -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 ml-0 lg:ml-[260px] p-6 md:p-10 transition-all duration-300">
        <div class="flex justify-between items-center mb-8 flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 mb-1">Configurações do Sistema</h1>
                <p class="text-sm text-slate-500">Personalize o nome da instituição e altere a identidade visual das telas do quiosque e do painel.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-7 py-7 max-w-2xl mb-8">
            <form method="POST" action="<?= BASE_URL ?>/admin/config">
                <?php renderizar_csrf_input(); ?>
                <input type="hidden" name="action" value="update_config">
                
                <div class="mb-5">
                    <label for="nome_escola" class="block text-[13px] font-bold text-slate-600 uppercase mb-2">Nome da Escola / Instituição</label>
                    <div class="tooltip-container" style="display: block;">
                        <input type="text" name="nome_escola" id="nome_escola" class="w-full border border-slate-300 bg-slate-50 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:border-[var(--primary)]" value="<?php echo htmlspecialchars($nome_escola); ?>" required>
                        <span class="tooltip-text">Este nome será exibido no topo do quiosque e na aba do navegador.</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label for="cor_primaria" class="block text-[13px] font-bold text-slate-600 uppercase mb-2">Cor Primária (Destaques/Botões)</label>
                        <div class="flex items-center gap-3 tooltip-container">
                            <input type="color" name="cor_primaria" id="cor_primaria" class="w-12 h-12 border border-slate-300 rounded-lg cursor-pointer bg-transparent p-0" value="<?php echo htmlspecialchars($cor_primaria); ?>">
                            <span class="font-mono text-sm font-semibold text-slate-700"><?php echo htmlspecialchars($cor_primaria); ?></span>
                            <span class="tooltip-text" style="bottom: 150%;">Usada nos botões principais e elementos de destaque do sistema.</span>
                        </div>
                    </div>

                    <div>
                        <label for="cor_secundaria" class="block text-[13px] font-bold text-slate-600 uppercase mb-2">Cor Secundária (Cabeçalho/Menu)</label>
                        <div class="flex items-center gap-3 tooltip-container">
                            <input type="color" name="cor_secundaria" id="cor_secundaria" class="w-12 h-12 border border-slate-300 rounded-lg cursor-pointer bg-transparent p-0" value="<?php echo htmlspecialchars($cor_secundaria); ?>">
                            <span class="font-mono text-sm font-semibold text-slate-700"><?php echo htmlspecialchars($cor_secundaria); ?></span>
                            <span class="tooltip-text" style="bottom: 150%;">Define a cor do menu lateral e do cabeçalho superior.</span>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="limite_chaves" class="block text-[13px] font-bold text-slate-600 uppercase mb-2">Limite Máximo de Chaves sob Posse Simultânea (por Usuário)</label>
                    <div class="tooltip-container inline-block w-full">
                        <input type="number" name="limite_chaves" id="limite_chaves" class="w-full max-w-[150px] border border-slate-300 bg-slate-50 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:border-[var(--primary)]" min="0" value="<?php echo htmlspecialchars($limite_chaves ?? 0); ?>" required>
                        <span class="tooltip-text">Quantas chaves um único crachá pode retirar ao mesmo tempo.</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-2 font-medium">Defina como 0 para permitir retiradas ilimitadas.</p>
                </div>

                <button type="submit" class="bg-[var(--primary)] hover:brightness-90 text-white font-bold py-3 px-6 rounded-lg transition-all shadow-sm">Salvar Alterações</button>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-7 py-7 max-w-2xl">
            <h2 class="text-lg font-bold text-slate-900 mb-2">💾 Backup do Banco de Dados</h2>
            <p class="text-sm text-slate-500 mb-5">
                Gere um arquivo de dump SQL (compactado em ZIP) com todas as tabelas e dados atuais do sistema para guardar com segurança.
            </p>
            <form method="POST" action="<?= BASE_URL ?>/admin/config" target="_blank" class="mb-5">
                <?php renderizar_csrf_input(); ?>
                <input type="hidden" name="action" value="download_backup">
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-lg transition-all shadow-sm flex items-center gap-2">
                    📥 Baixar Backup (.zip)
                </button>
            </form>

            <hr class="border-t border-slate-200 my-6">

            <h2 class="text-lg font-bold text-slate-900 mb-2">♻️ Restaurar Backup</h2>
            <p class="text-sm text-red-500 font-semibold mb-5">
                Atenção: Restaurar um backup irá apagar todos os dados atuais e substituí-los pelas informações do arquivo de backup. Faça isso apenas se tiver certeza!
            </p>
            <form method="POST" action="<?= BASE_URL ?>/admin/config" enctype="multipart/form-data" onsubmit="return confirm('Tem certeza absoluta que deseja sobreescrever o banco de dados atual? Esta ação é irreversível!');">
                <?php renderizar_csrf_input(); ?>
                <input type="hidden" name="action" value="restore_backup">
                
                <div class="mb-5">
                    <label for="backup_file" class="block text-[13px] font-bold text-slate-600 uppercase mb-2">Arquivo de Backup (.sql ou .zip)</label>
                    <input type="file" name="backup_file" id="backup_file" class="w-full border border-slate-300 bg-slate-50 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100" accept=".sql,.zip" required>
                </div>

                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-6 rounded-lg transition-all shadow-sm flex items-center gap-2">
                    ⚠️ Sobreescrever e Restaurar Banco
                </button>
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
    <script src="<?= BASE_URL ?>/api/admin_responsive.js"></script>
</body>
</html>
