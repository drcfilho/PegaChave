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

        .page-title p {
            color: #64748b;
            font-size: 14px;
            margin-top: 4px;
        }

        .btn-add {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: opacity 0.2s;
        }

        .btn-add:hover {
            opacity: 0.9;
        }

        /* Card container */
        .content-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #0f172a;
        }

        /* Message Banner */
        .message-banner {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 14px;
        }

        .message-banner.success {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .message-banner.error {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            padding: 12px 16px;
            border-bottom: 2px solid #e2e8f0;
            text-transform: uppercase;
        }

        td {
            font-size: 14px;
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .action-link {
            color: var(--error);
            text-decoration: none;
            font-weight: 700;
            cursor: pointer;
        }

        .action-link:hover {
            text-decoration: underline;
        }

        /* Modal popup */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 100;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--card-bg);
            border-radius: 16px;
            width: 100%;
            max-width: 500px;
            padding: 32px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .modal-header h2 {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
        }

        .btn-close {
            background: transparent;
            border: none;
            font-size: 24px;
            color: #64748b;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            background: var(--card-bg);
            color: var(--text-color);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
        }

        .btn-modal {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-cancel {
            background: #e2e8f0;
            border: none;
            color: #475569;
        }

        .btn-save {
            background: var(--primary);
            border: none;
            color: white;
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
                <h1>Agendamentos de Salas</h1>
                <p>Gerencie reservas futuras de chaves e previna conflitos no quiosque.</p>
            </div>
            <button class="btn-add" onclick="abrirModal()">
                <span>➕</span> Novo Agendamento
            </button>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message-banner <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Listagem de Reservas -->
        <div class="content-card">
            <h2 class="card-title">Reservas Futuras e de Hoje</h2>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Horário</th>
                            <th>Chave/Sala</th>
                            <th>Reservado Para</th>
                            <th>Ações</th>
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
                                <tr>
                                    <td><strong><?php echo date('d/m/Y', strtotime($r['data_reserva'])); ?></strong></td>
                                    <td><?php echo $r['inicio']; ?> - <?php echo $r['fim']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($r['nome_sala']); ?></strong> (<?php echo htmlspecialchars($r['codigo_sala']); ?>)</td>
                                    <td><?php echo htmlspecialchars($r['usuario_nome']); ?></td>
                                    <td>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/reservas" onsubmit="return confirm('Deseja cancelar este agendamento?');" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo gerador_csrf_token(); ?>">
                                            <input type="hidden" name="action" value="delete_reserva">
                                            <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                            <button type="submit" class="action-link" style="background:none; border:none; padding:0; font-size:inherit; font-family:inherit;">Cancelar</button>
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
        <div class="content-card">
            <h2 class="card-title">Histórico de Reservas Passadas (Auditoria)</h2>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Horário</th>
                            <th>Chave/Sala</th>
                            <th>Reservado Para</th>
                            <th>Status</th>
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
                                <tr>
                                    <td><strong><?php echo date('d/m/Y', strtotime($rp['data_reserva'])); ?></strong></td>
                                    <td><?php echo $rp['inicio']; ?> - <?php echo $rp['fim']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($rp['nome_sala']); ?></strong> (<?php echo htmlspecialchars($rp['codigo_sala']); ?>)</td>
                                    <td><?php echo htmlspecialchars($rp['usuario_nome']); ?></td>
                                    <td style="color: #64748b; font-style: italic;">Concluído / Expirado</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Form -->
    <div class="modal" id="modalAgendamento">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Novo Agendamento</h2>
                <button class="btn-close" onclick="fecharModal()">&times;</button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/reservas">
                <input type="hidden" name="csrf_token" value="<?php echo gerador_csrf_token(); ?>">
                <input type="hidden" name="action" value="add_reserva">

                <div class="form-group">
                    <label for="chave_id">Selecione a Sala</label>
                    <select name="chave_id" id="chave_id" class="form-control" required>
                        <option value="">-- Escolha a Sala --</option>
                        <?php foreach ($chaves as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nome_sala']); ?> (<?php echo htmlspecialchars($c['codigo_sala']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="usuario_id">Selecione o Usuário</label>
                    <select name="usuario_id" id="usuario_id" class="form-control" required>
                        <option value="">-- Escolha o Usuário --</option>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="data_reserva">Data da Reserva</label>
                    <input type="date" name="data_reserva" id="data_reserva" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group" style="display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label for="hora_inicio">Hora de Início</label>
                        <input type="time" name="hora_inicio" id="hora_inicio" class="form-control" required>
                    </div>
                    <div style="flex: 1;">
                        <label for="hora_fim">Hora de Término</label>
                        <input type="time" name="hora_fim" id="hora_fim" class="form-control" required>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-modal btn-cancel" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn-modal btn-save">Confirmar Reserva</button>
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
