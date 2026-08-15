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

    <!-- Sidebar Dinâmica -->
    @include('partials.sidebar')

    <main class="flex-1 ml-0 lg:ml-[260px] p-6 md:p-10 transition-all duration-300">
        <div class="flex justify-between items-center mb-8 flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 mb-1">Gestão de Acessos (Operadores)</h1>
                <p class="text-sm text-slate-500">Crie logins e defina permissões específicas para recepcionistas ou seguranças.</p>
            </div>
            <button class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors" onclick="openModal()">+ Novo Operador</button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden px-6 py-5 mb-8">
            <div style="overflow-x: auto;">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Nome</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Usuário (Login)</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Perfil</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($operadores as $op): 
                            $isThisMaster = $op['role'] === 'admin_master';
                            $badgeClass = $isThisMaster ? 'bg-yellow-200 text-yellow-800' : 'bg-sky-100 text-sky-800';
                            $roleName = $isThisMaster ? 'Master' : 'Operador';
                            $permsJson = htmlspecialchars($op['permissoes'] ?? '[]');
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 border-b border-slate-100 text-sm"><strong><?= htmlspecialchars($op['nome']) ?></strong></td>
                            <td class="px-4 py-3 border-b border-slate-100 text-sm"><?= htmlspecialchars($op['usuario']) ?></td>
                            <td class="px-4 py-3 border-b border-slate-100 text-sm"><span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $badgeClass ?>"><?= $roleName ?></span></td>
                            <td class="px-4 py-3 border-b border-slate-100 text-sm">
                                <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-1.5 px-3 rounded-md shadow-sm transition-colors text-xs" onclick="editOp(<?= $op['id'] ?>, '<?= htmlspecialchars($op['usuario']) ?>', '<?= htmlspecialchars($op['nome']) ?>', '<?= $op['role'] ?>', <?= htmlspecialchars($permsJson, ENT_QUOTES) ?>)">Editar</button>
                                <?php if ($_SESSION['admin_user_id'] != $op['id']): ?>
                                <form method="POST" action="<?= BASE_URL ?>/admin/operadores/delete" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="id" value="<?= $op['id'] ?>">
                                    <button class="bg-red-500 hover:bg-red-600 text-white font-bold py-1.5 px-3 rounded-md shadow-sm transition-colors text-xs" onclick="return confirm('Excluir este operador?');">Apagar</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Cadastro/Edição -->
    <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-[200] items-center justify-center hidden [&.active]:flex" id="opModal">
        <div class="bg-white rounded-2xl p-6 w-[95%] max-w-[500px] shadow-2xl">
            <h2 id="modalTitle" class="text-xl font-bold mb-5">Novo Operador</h2>
            <form id="opForm" method="POST" action="<?= BASE_URL ?>/admin/operadores/create">
                @csrf
                <input type="hidden" name="id" id="op_id">
                
                <div class="mb-4">
                    <label class="block text-[13px] font-semibold text-slate-600 mb-1.5">Nome Completo</label>
                    <div class="tooltip-container" style="display: block;">
                        <input type="text" name="nome" id="op_nome" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                        <span class="tooltip-text">Nome real do administrador/operador.</span>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-[13px] font-semibold text-slate-600 mb-1.5">Usuário de Login</label>
                    <div class="tooltip-container" style="display: block;">
                        <input type="text" name="usuario" id="op_usuario" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                        <span class="tooltip-text">Apelido usado para acessar o painel (sem espaços).</span>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-[13px] font-semibold text-slate-600 mb-1.5">Senha <span id="senhaHint" style="font-weight:normal; font-size:12px; color:#94a3b8; display:none;">(Deixe em branco para não alterar)</span></label>
                    <div class="tooltip-container" style="display: block;">
                        <input type="password" name="senha" id="op_senha" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                        <span class="tooltip-text">Senha de acesso ao painel (criptografada no banco).</span>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-[13px] font-semibold text-slate-600 mb-1.5">Nível de Acesso</label>
                    <div class="tooltip-container" style="display: block;">
                        <select name="role" id="op_role" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" onchange="togglePerms()">
                            <option value="operador">Operador (Restrito)</option>
                            <option value="admin_master">Administrador Master (Poder Total)</option>
                        </select>
                        <span class="tooltip-text">Master pode tudo. Operador precisa de permissões específicas marcadas abaixo.</span>
                    </div>
                </div>

                <div class="mb-4" id="permsSection">
                    <label class="block text-[13px] font-semibold text-slate-600 mb-1.5">Permissões (Apenas para Operador)</label>
                    <div class="tooltip-container" style="display: block;">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2">
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="permissoes[]" value="gerenciar_chaves" class="perm-cb"> Gerenciar Chaves e Reservas</label>
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="permissoes[]" value="gerenciar_usuarios" class="perm-cb"> Gerenciar Usuários</label>
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="permissoes[]" value="ver_relatorios" class="perm-cb"> Relatórios e Logs</label>
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="permissoes[]" value="gerenciar_configuracoes" class="perm-cb"> Configurações do Sistema</label>
                        </div>
                        <span class="tooltip-text">As áreas que o operador pode acessar no menu lateral.</span>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="button" class="px-4 py-2 text-sm font-bold rounded-lg cursor-pointer transition-colors" style="background:#e2e8f0; color:#334155; flex:1;" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 text-sm font-bold rounded-lg cursor-pointer transition-colors shadow-sm" style="flex:1;">Salvar</button>
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
    <script src="<?= BASE_URL ?>/api/admin_responsive.js"></script>
</body>
</html>
