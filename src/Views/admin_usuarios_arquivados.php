<?php
// View para admin_usuarios_arquivados.php
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
              slideIn: { from: { transform: 'translateX(120%)' }, to: { transform: 'translateX(0)' } }
            },
            animation: {
              'slide-in': 'slideIn 0.3s ease-out forwards'
            }
          }
        }
      }
    </script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans flex min-h-screen">

    <?php if ($message): ?>
        <div class="toast-message fixed top-5 right-5 <?php echo $messageType === 'error' ? 'border-red-500' : 'border-primary'; ?> bg-slate-900 text-white px-6 py-3 rounded-lg shadow-lg z-[1000] text-sm font-semibold border-l-4 animate-slide-in">
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
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 ml-0 lg:ml-[260px] p-6 md:p-10 transition-all duration-300">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 mb-1">Usuários Arquivados</h1>
                <p class="text-sm text-slate-500">Audite e gerencie os registros históricos de servidores desativados.</p>
            </div>
        </div>

        <?php if (!$autenticado): ?>
            <!-- Formulário de Autenticação -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden px-6 py-5 mb-8">
                <div class="max-w-[400px] mx-auto text-center py-8">
                    <div class="text-5xl mb-5">🔒</div>
                    <h2 class="mb-3 font-extrabold text-2xl">Acesso Restrito</h2>
                    <p class="text-slate-500 text-sm mb-6">Por questões de privacidade, digite sua senha de administrador para visualizar os dados históricos arquivados.</p>
                    
                    <form method="POST" action="<?= BASE_URL ?>/admin/usuarios_arquivados">
                        <?php renderizar_csrf_input(); ?>
                        <input type="hidden" name="action" value="auth_arquivados">
                        
                        <div class="mb-4 text-left">
                            <label for="senha" class="block text-[13px] font-semibold text-slate-600 mb-1.5">Senha Administrativa</label>
                            <input type="password" name="senha" id="senha" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" required autofocus placeholder="Digite sua senha">
                        </div>
                        
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg shadow-sm transition-colors mt-2">Confirmar Senha</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Lista de Arquivados -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden px-6 py-5 mb-8">
                <?php if (empty($usuariosArquivados)): ?>
                    <p style="text-align: center; color: #64748b; padding: 25px 0;">Nenhum usuário arquivado no sistema.</p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr>
                                    <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Nome</th>
                                    <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">E-mail</th>
                                    <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Matrícula (ID)</th>
                                    <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Função</th>
                                    <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuariosArquivados as $user): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-3 border-b border-slate-100 text-sm"><strong><?php echo htmlspecialchars($user['nome']); ?></strong></td>
                                        <td class="px-4 py-3 border-b border-slate-100 text-sm"><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td class="px-4 py-3 border-b border-slate-100 text-sm"><code><?php echo htmlspecialchars($user['matricula']); ?></code></td>
                                        <td class="px-4 py-3 border-b border-slate-100 text-sm"><span class="text-[11px] font-semibold px-2 py-1 rounded-md bg-blue-100 text-blue-700"><?php echo htmlspecialchars($user['funcao']); ?></span></td>
                                        <td class="px-4 py-3 border-b border-slate-100 text-sm">
                                            <form method="POST" action="<?= BASE_URL ?>/admin/usuarios_arquivados" style="display:inline;">
                                                <?php renderizar_csrf_input(); ?>
                                                <input type="hidden" name="action" value="restore_usuario">
                                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-1.5 px-3 rounded-md shadow-sm transition-colors text-xs">Restaurar</button>
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

    <script src="<?= BASE_URL ?>/api/admin_responsive.js"></script>
</body>
</html>
