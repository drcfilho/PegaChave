<?php
// View para admin_reservas.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamentos de Salas - PegaChave</title>
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
                <h1 class="text-2xl font-extrabold text-slate-900 mb-1">Agendamentos de Salas</h1>
                <p class="text-sm text-slate-500">Gerencie reservas futuras de chaves e previna conflitos no quiosque.</p>
            </div>
            <button class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors flex items-center gap-2" onclick="abrirModal()">
                ➕ Novo Agendamento
            </button>
        </div>

        <?php if (!empty($message)): ?>
            <div class="px-4 py-3 rounded-lg mb-6 font-semibold text-sm border <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700 border-green-200' : 'bg-red-100 text-red-700 border-red-200'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Listagem de Reservas -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden px-6 py-5 mb-8">
            <h2 class="text-lg font-bold text-slate-900 mb-5">Reservas Futuras e de Hoje</h2>
            <div style="overflow-x: auto;">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Data</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Horário</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Chave/Sala</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Reservado Para</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reservas)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #64748b; padding: 25px;">
                                    Nenhum agendamento futuro encontrado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reservas as $r): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><strong><?php echo date('d/m/Y', strtotime($r['data_reserva'])); ?></strong></td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><?php echo $r['inicio']; ?> - <?php echo $r['fim']; ?></td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><strong><?php echo htmlspecialchars($r['nome_sala']); ?></strong> (<?php echo htmlspecialchars($r['codigo_sala']); ?>)</td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><?php echo htmlspecialchars($r['usuario_nome']); ?></td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm">
                                        <form method="POST" action="<?= BASE_URL ?>/admin/reservas" onsubmit="return confirm('Deseja cancelar este agendamento?');" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo gerador_csrf_token(); ?>">
                                            <input type="hidden" name="action" value="delete_reserva">
                                            <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                            <button type="submit" class="text-red-500 font-bold cursor-pointer hover:underline bg-transparent border-none p-0 text-sm">Cancelar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Histórico de Reservas Passadas -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden px-6 py-5 mb-8">
            <h2 class="text-lg font-bold text-slate-900 mb-5">Histórico de Reservas Passadas (Auditoria)</h2>
            <div style="overflow-x: auto;">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Data</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Horário</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Chave/Sala</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Reservado Para</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reservasPassadas)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #64748b; padding: 25px;">
                                    Nenhum histórico de agendamento encontrado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reservasPassadas as $rp): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><strong><?php echo date('d/m/Y', strtotime($rp['data_reserva'])); ?></strong></td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><?php echo $rp['inicio']; ?> - <?php echo $rp['fim']; ?></td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><strong><?php echo htmlspecialchars($rp['nome_sala']); ?></strong> (<?php echo htmlspecialchars($rp['codigo_sala']); ?>)</td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><?php echo htmlspecialchars($rp['usuario_nome']); ?></td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm" style="color: #64748b; font-style: italic;">Concluído / Expirado</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Form -->
    <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-[200] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-250 [&.active]:opacity-100 [&.active]:pointer-events-auto" id="modalAgendamento">
        <div class="bg-white rounded-2xl p-6 w-[95%] max-w-[500px] shadow-2xl transform scale-95 transition-all duration-250 [.active_&]:scale-100">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-slate-900">Novo Agendamento</h2>
                <button class="bg-transparent border-none text-2xl text-slate-500 cursor-pointer hover:text-slate-700" onclick="fecharModal()">&times;</button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/reservas">
                <input type="hidden" name="csrf_token" value="<?php echo gerador_csrf_token(); ?>">
                <input type="hidden" name="action" value="add_reserva">

                <div class="mb-5">
                    <label for="chave_id" class="block text-sm font-semibold text-slate-700 mb-2">Selecione a Sala</label>
                    <select name="chave_id" id="chave_id" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                        <option value="">-- Escolha a Sala --</option>
                        <?php foreach ($chaves as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nome_sala']); ?> (<?php echo htmlspecialchars($c['codigo_sala']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-5">
                    <label for="usuario_id" class="block text-sm font-semibold text-slate-700 mb-2">Selecione o Usuário</label>
                    <select name="usuario_id" id="usuario_id" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                        <option value="">-- Escolha o Usuário --</option>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-5">
                    <label for="data_reserva" class="block text-sm font-semibold text-slate-700 mb-2">Data da Reserva</label>
                    <input type="date" name="data_reserva" id="data_reserva" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" min="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="flex gap-4 mb-5">
                    <div class="flex-1">
                        <label for="hora_inicio" class="block text-sm font-semibold text-slate-700 mb-2">Hora de Início</label>
                        <input type="time" name="hora_inicio" id="hora_inicio" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                    </div>
                    <div class="flex-1">
                        <label for="hora_fim" class="block text-sm font-semibold text-slate-700 mb-2">Hora de Término</label>
                        <input type="time" name="hora_fim" id="hora_fim" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                    </div>
                </div>

                <div class="flex gap-3 justify-end mt-7">
                    <button type="button" class="px-5 py-2.5 text-sm font-bold rounded-lg cursor-pointer bg-slate-200 text-slate-700 hover:bg-slate-300 transition-colors" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-bold rounded-lg cursor-pointer bg-green-600 text-white hover:bg-green-700 transition-colors shadow-sm">Confirmar Reserva</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModal() {
            document.getElementById('modalAgendamento').classList.add('active');
        }

        function fecharModal() {
            document.getElementById('modalAgendamento').classList.remove('active');
        }
    </script>
    <script src="<?= BASE_URL ?>/api/admin_responsive.js"></script>
</body>
</html>
