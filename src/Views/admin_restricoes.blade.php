<?php
// View para admin_restricoes.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restrições de Acesso - PegaChave</title>
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
    <main class="flex-1 ml-0 lg:ml-[260px] p-6 md:p-10 transition-all duration-300">
        <div class="flex justify-between items-center mb-8 flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 mb-1">Restrições de Acesso</h1>
                <p class="text-sm text-slate-500">Marque os cruzamentos para <strong>bloquear</strong> perfis específicos de retirarem determinadas chaves.</p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="toast-message fixed left-1/2 -translate-x-1/2 <?php echo ($messageType === 'error') ? 'bg-red-500' : 'bg-green-500'; ?> text-white px-6 py-3 rounded-lg font-semibold shadow-lg z-[500] animate-slide-up-down">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden px-6 py-5 mb-8">
            <h2 class="text-lg font-bold mb-5">Matriz de Perfis vs Salas (Marcado = Acesso Bloqueado)</h2>
            
            <form method="POST" action="<?= BASE_URL ?>/admin/restricoes">
                @csrf
                <input type="hidden" name="action" value="save_restrictions">

                <div style="overflow-x: auto;">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Chave / Sala</th>
                                <?php foreach ($perfis as $p): ?>
                                    <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold text-center"><?php echo htmlspecialchars($p['nome']); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($chaves)): ?>
                                <tr>
                                    <td colspan="<?php echo count($perfis) + 1; ?>" style="text-align: center; color: #64748b; padding: 25px;">
                                        Nenhuma chave cadastrada para configurar.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($chaves as $c): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-3 border-b border-slate-100 text-sm"><strong><?php echo htmlspecialchars($c['nome_sala']); ?></strong> (<?php echo htmlspecialchars($c['codigo_sala']); ?>)</td>
                                        <?php foreach ($perfis as $p): ?>
                                            <td class="px-4 py-3 border-b border-slate-100 text-sm text-center">
                                                <!-- Ignora o perfil Administrador (ID 1) para evitar autobloqueio indesejado, pois Admin tem acesso total -->
                                                <?php if ($p['id'] == 1): ?>
                                                    <span class="text-green-500 font-bold text-xs">Livre</span>
                                                <?php else: ?>
                                                    <label class="relative flex items-center justify-center cursor-pointer">
                                                        <input type="checkbox" class="peer sr-only" name="bloqueio[<?php echo $p['id']; ?>][<?php echo $c['id']; ?>]" value="1" <?php echo isset($restricoes[$p['id']][$c['id']]) ? 'checked' : ''; ?>>
                                                        <div class="w-5 h-5 bg-slate-200 rounded flex items-center justify-center peer-checked:bg-red-500 transition-colors">
                                                            <svg class="w-3 h-3 text-white hidden peer-checked:block pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                                                <polyline points="20 6 9 17 4 12"></polyline>
                                                            </svg>
                                                        </div>
                                                    </label>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors mt-5">Salvar Configurações de Acesso</button>
            </form>
        </div>
    </main>

    <script src="<?= BASE_URL ?>/api/admin_responsive.js"></script>
</body>
</html>
