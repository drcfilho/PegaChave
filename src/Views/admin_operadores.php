<?php
// src/Views/admin_operadores.php
function has_permission($perm) {
    if (($_SESSION['admin_role'] ?? '') === 'admin_master') return true;
    $perms = $_SESSION['admin_permissoes'] ?? [];
    return is_array($perms) && (in_array($perm, $perms) || in_array('all', $perms));
}
$isMaster = ($_SESSION['admin_role'] ?? '') === 'admin_master';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Operadores - PegaChave</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/quiosque.css">
    <style>
        /* Mesmos estilos globais do admin_config (reduzidos para espaço) */
        :root {
            --bg-color: #f1f5f9;
            --text-color: #1e293b;
            --sidebar-bg: #0f172a;
            --sidebar-active: #0284c7;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --primary: <?php echo $cor_primaria; ?>;
            --success: #22c55e;
            --error: #ef4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-color); display: flex; min-height: 100vh; }
        
        aside { width: 260px; background-color: var(--sidebar-bg); color: #ffffff; display: flex; flex-direction: column; border-right: 1px solid rgba(255, 255, 255, 0.05); position: fixed; top: 0; bottom: 0; left: 0; z-index: 10; overflow-y: auto; }
        .sidebar-header { padding: 24px; font-size: 20px; font-weight: 800; border-bottom: 1px solid rgba(255, 255, 255, 0.05); display: flex; align-items: center; gap: 10px; }
        .sidebar-menu { list-style: none; padding: 20px 0; flex: 1; }
        .sidebar-item a { display: flex; align-items: center; gap: 12px; padding: 14px 24px; color: #94a3b8; text-decoration: none; font-weight: 600; font-size: 15px; transition: all 0.2s; border-left: 4px solid transparent; }
        .sidebar-item a:hover { color: #ffffff; background: rgba(255, 255, 255, 0.02); }
        .sidebar-item.active a { color: #ffffff; background: rgba(2, 132, 199, 0.1); border-left-color: var(--sidebar-active); }
        .sidebar-footer { padding: 20px 24px; border-top: 1px solid rgba(255, 255, 255, 0.05); }
        .btn-kiosk { display: flex; align-items: center; justify-content: center; gap: 8px; background-color: rgba(255,255,255,0.05); color: #fff; text-decoration: none; padding: 10px; border-radius: 8px; font-size: 14px; font-weight: 700; transition: background 0.2s; }
        .btn-kiosk:hover { background-color: var(--sidebar-active); }

        main { margin-left: 260px; flex: 1; padding: 40px; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-title h1 { font-size: 24px; font-weight: 800; color: #0f172a; }
        
        .content-card { background-color: var(--card-bg); border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); padding: 28px; margin-bottom: 30px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { text-align: left; padding: 14px 16px; border-bottom: 1px solid var(--border-color); }
        th { background-color: #f8fafc; font-weight: 700; color: #475569; font-size: 13px; text-transform: uppercase; }
        td { font-size: 14px; color: #1e293b; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .badge-master { background-color: #fef08a; color: #854d0e; }
        .badge-op { background-color: #e0f2fe; color: #0369a1; }

        .btn { padding: 10px 16px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; border: none; }
        .btn-primary { background-color: var(--primary); color: white; }
        .btn-primary:hover { filter: brightness(0.9); }
        .btn-danger { background-color: var(--error); color: white; }
        .btn-sm { padding: 6px 12px; font-size: 13px; }

        .modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); display: none; align-items: center; justify-content: center; z-index: 100; }
        .modal-overlay.active { display: flex; }
        .modal-content { background: var(--card-bg); border-radius: 16px; width: 100%; max-width: 500px; padding: 30px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: #475569; margin-bottom: 6px; }
        .form-control { width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; outline: none; }
        
        .perm-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 10px; }
        .perm-item { display: flex; align-items: center; gap: 8px; font-size: 14px; }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        corePlugins: {
          preflight: false,
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
<body>

    <!-- Sidebar Dinâmica -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <main>
        <div class="page-header">
            <div class="page-title">
                <h1>Gestão de Acessos (Operadores)</h1>
                <p>Crie logins e defina permissões específicas para recepcionistas ou seguranças.</p>
            </div>
            <button class="btn btn-primary" onclick="openModal()">+ Novo Operador</button>
        </div>

        <div class="content-card">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Usuário (Login)</th>
                        <th>Perfil</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($operadores as $op): 
                        $isThisMaster = $op['role'] === 'admin_master';
                        $badgeClass = $isThisMaster ? 'badge-master' : 'badge-op';
                        $roleName = $isThisMaster ? 'Master' : 'Operador';
                        $permsJson = htmlspecialchars($op['permissoes'] ?? '[]');
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($op['nome']) ?></strong></td>
                        <td><?= htmlspecialchars($op['usuario']) ?></td>
                        <td><span class="badge <?= $badgeClass ?>"><?= $roleName ?></span></td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="editOp(<?= $op['id'] ?>, '<?= htmlspecialchars($op['usuario']) ?>', '<?= htmlspecialchars($op['nome']) ?>', '<?= $op['role'] ?>', <?= htmlspecialchars($permsJson, ENT_QUOTES) ?>)">Editar</button>
                            <?php if ($_SESSION['admin_user_id'] != $op['id']): ?>
                            <form method="POST" action="<?= BASE_URL ?>/admin/operadores/delete" style="display:inline;">
                                <?php renderizar_csrf_input(); ?>
                                <input type="hidden" name="id" value="<?= $op['id'] ?>">
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Excluir este operador?');">Apagar</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal Cadastro/Edição -->
    <div class="modal-overlay" id="opModal">
        <div class="modal-content">
            <h2 id="modalTitle" style="margin-bottom: 20px;">Novo Operador</h2>
            <form id="opForm" method="POST" action="<?= BASE_URL ?>/admin/operadores/create">
                <?php renderizar_csrf_input(); ?>
                <input type="hidden" name="id" id="op_id">
                
                <div class="form-group">
                    <label>Nome Completo</label>
                    <div class="tooltip-container" style="display: block;">
                        <input type="text" name="nome" id="op_nome" class="form-control" required>
                        <span class="tooltip-text">Nome real do administrador/operador.</span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Usuário de Login</label>
                    <div class="tooltip-container" style="display: block;">
                        <input type="text" name="usuario" id="op_usuario" class="form-control" required>
                        <span class="tooltip-text">Apelido usado para acessar o painel (sem espaços).</span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Senha <span id="senhaHint" style="font-weight:normal; font-size:12px; color:#94a3b8; display:none;">(Deixe em branco para não alterar)</span></label>
                    <div class="tooltip-container" style="display: block;">
                        <input type="password" name="senha" id="op_senha" class="form-control" required>
                        <span class="tooltip-text">Senha de acesso ao painel (criptografada no banco).</span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Nível de Acesso</label>
                    <div class="tooltip-container" style="display: block;">
                        <select name="role" id="op_role" class="form-control" onchange="togglePerms()">
                            <option value="operador">Operador (Restrito)</option>
                            <option value="admin_master">Administrador Master (Poder Total)</option>
                        </select>
                        <span class="tooltip-text">Master pode tudo. Operador precisa de permissões específicas marcadas abaixo.</span>
                    </div>
                </div>

                <div class="form-group" id="permsSection">
                    <label>Permissões (Apenas para Operador)</label>
                    <div class="tooltip-container" style="display: block;">
                        <div class="perm-grid">
                            <label class="perm-item"><input type="checkbox" name="permissoes[]" value="gerenciar_chaves" class="perm-cb"> Gerenciar Chaves e Reservas</label>
                            <label class="perm-item"><input type="checkbox" name="permissoes[]" value="gerenciar_usuarios" class="perm-cb"> Gerenciar Usuários</label>
                            <label class="perm-item"><input type="checkbox" name="permissoes[]" value="ver_relatorios" class="perm-cb"> Relatórios e Logs</label>
                            <label class="perm-item"><input type="checkbox" name="permissoes[]" value="gerenciar_configuracoes" class="perm-cb"> Configurações do Sistema</label>
                        </div>
                        <span class="tooltip-text">As áreas que o operador pode acessar no menu lateral.</span>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="button" class="btn" style="background:#e2e8f0; color:#334155; flex:1;" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary" style="flex:1;">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('opForm').action = "<?= BASE_URL ?>/admin/operadores/create";
            document.getElementById('modalTitle').textContent = "Novo Operador";
            document.getElementById('op_id').value = "";
            document.getElementById('op_nome').value = "";
            document.getElementById('op_usuario').value = "";
            document.getElementById('op_senha').required = true;
            document.getElementById('senhaHint').style.display = 'none';
            document.getElementById('op_role').value = "operador";
            document.querySelectorAll('.perm-cb').forEach(cb => cb.checked = false);
            togglePerms();
            document.getElementById('opModal').classList.add('active');
        }

        function editOp(id, usuario, nome, role, permissoes) {
            document.getElementById('opForm').action = "<?= BASE_URL ?>/admin/operadores/update";
            document.getElementById('modalTitle').textContent = "Editar Operador";
            document.getElementById('op_id').value = id;
            document.getElementById('op_nome').value = nome;
            document.getElementById('op_usuario').value = usuario;
            document.getElementById('op_senha').required = false;
            document.getElementById('senhaHint').style.display = 'inline';
            document.getElementById('op_role').value = role;
            
            document.querySelectorAll('.perm-cb').forEach(cb => {
                cb.checked = permissoes.includes(cb.value);
            });
            
            togglePerms();
            document.getElementById('opModal').classList.add('active');
        }

        function togglePerms() {
            const role = document.getElementById('op_role').value;
            document.getElementById('permsSection').style.opacity = role === 'admin_master' ? '0.4' : '1';
            document.getElementById('permsSection').style.pointerEvents = role === 'admin_master' ? 'none' : 'auto';
            if (role === 'admin_master') {
                document.querySelectorAll('.perm-cb').forEach(cb => cb.checked = true);
            }
        }

        function closeModal() {
            document.getElementById('opModal').classList.remove('active');
        }
    </script>
</body>
</html>
