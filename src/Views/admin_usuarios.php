<?php
// View para admin_usuarios.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Usuários - PegaChave</title>
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
        <div class="toast-message fixed left-1/2 -translate-x-1/2 bg-green-500 text-white px-6 py-3 rounded-lg font-semibold shadow-lg z-[500] animate-slide-up-down">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Sidebar -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 ml-0 lg:ml-[260px] p-6 md:p-10 transition-all duration-300">
        <div class="flex justify-between items-center mb-8 flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 mb-1">Gerenciamento de Usuários</h1>
                <p class="text-sm text-slate-500">Cadastre servidores e professores autorizados a realizar a movimentação de chaves.</p>
            </div>
            <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                <input type="text" id="search-input" placeholder="🔍 Pesquisar usuário..." class="w-full md:w-auto border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 min-w-[250px]" oninput="filterTable()">
                <button class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors flex items-center gap-2" onclick="openUserModal()">
                    ➕ Novo Usuário
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden px-6 py-5 mb-8">
            <div style="overflow-x: auto;">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="sortable cursor-pointer select-none transition-colors hover:bg-slate-100 hover:text-primary relative after:content-['\2195'] after:text-[10px] after:text-slate-400 after:ml-1 bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold" onclick="sortTable(0)">Nome</th>
                            <th class="sortable cursor-pointer select-none transition-colors hover:bg-slate-100 hover:text-primary relative after:content-['\2195'] after:text-[10px] after:text-slate-400 after:ml-1 bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold" onclick="sortTable(1)">ID (Matrícula)</th>
                            <th class="sortable cursor-pointer select-none transition-colors hover:bg-slate-100 hover:text-primary relative after:content-['\2195'] after:text-[10px] after:text-slate-400 after:ml-1 bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold" onclick="sortTable(2)">Função</th>
                            <th class="sortable cursor-pointer select-none transition-colors hover:bg-slate-100 hover:text-primary relative after:content-['\2195'] after:text-[10px] after:text-slate-400 after:ml-1 bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold" onclick="sortTable(3)">E-mail</th>
                            <th class="sortable cursor-pointer select-none transition-colors hover:bg-slate-100 hover:text-primary relative after:content-['\2195'] after:text-[10px] after:text-slate-400 after:ml-1 bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold" onclick="sortTable(4)">QR Hash</th>
                            <th class="sortable cursor-pointer select-none transition-colors hover:bg-slate-100 hover:text-primary relative after:content-['\2195'] after:text-[10px] after:text-slate-400 after:ml-1 bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold" onclick="sortTable(5)">Ativo</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: #64748b; padding: 25px;">
                                    Nenhum usuário cadastrado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $u): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><strong><?php echo htmlspecialchars($u['nome']); ?></strong></td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><code><?php echo htmlspecialchars($u['matricula']); ?></code></td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><?php echo htmlspecialchars($u['funcao']); ?></td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><small><?php echo htmlspecialchars($u['qr_code_hash']); ?></small></td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm">
                                        <span style="color: <?php echo $u['ativo'] ? '#22c55e' : '#ef4444'; ?>; font-weight: bold;">
                                            <?php echo $u['ativo'] ? 'Sim' : 'Não'; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm">
                                        <a class="text-primary font-bold cursor-pointer hover:underline" onclick="openEditUserModal(<?php echo htmlspecialchars(json_encode($u)); ?>)">Editar</a>
                                        <span style="color: #cbd5e1;">|</span>
                                        <a class="text-red-500 font-bold cursor-pointer hover:underline" onclick="confirmDeleteUser(<?php echo $u['id']; ?>)">Excluir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Form -->
    <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-[200] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-250 [&.active]:opacity-100 [&.active]:pointer-events-auto" id="user-modal">
        <div class="bg-white rounded-2xl p-6 w-[95%] max-w-[480px] shadow-2xl transform scale-95 transition-all duration-250 [.active_&]:scale-100">
            <h3 class="text-xl font-bold mb-5" id="user-modal-title">Cadastrar Novo Usuário</h3>
            <form method="POST" action="<?= BASE_URL ?>/admin/usuarios">
                <?php renderizar_csrf_input(); ?>
                <input type="hidden" name="action" id="user-action" value="add_usuario">
                <input type="hidden" name="id" id="user-id">
                
                <div class="mb-4">
                    <label for="nome" class="block text-[13px] font-semibold text-slate-600 mb-1.5">Nome Completo</label>
                    <div class="tooltip-container" style="display: block;">
                        <input type="text" name="nome" id="nome" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Ex: Carlos Silva" required>
                        <span class="tooltip-text">Nome do funcionário, professor ou servidor.</span>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="email" class="block text-[13px] font-semibold text-slate-600 mb-1.5">E-mail</label>
                    <div class="tooltip-container" style="display: block;">
                        <input type="email" name="email" id="email" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Ex: carlos@escola.edu.br" required>
                        <span class="tooltip-text">Endereço de e-mail institucional para contato (pode ser usado para notificações).</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="matricula" class="block text-[13px] font-semibold text-slate-600 mb-1.5">Matrícula / CPF (ID)</label>
                    <div class="tooltip-container" style="display: block;">
                        <input type="text" name="matricula" id="matricula" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Ex: 101" required>
                        <span class="tooltip-text">Matrícula (aluno/servidor) ou CPF (colaborador). Usado para gerar o crachá único.</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="perfil_id" class="block text-[13px] font-semibold text-slate-600 mb-1.5">Perfil / Função</label>
                    <div class="tooltip-container" style="display: block;">
                        <select name="perfil_id" id="perfil_id" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                            <?php foreach ($perfis as $p): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="tooltip-text">Define o grupo de permissões gerais (ex: Professor, Zelador).</span>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="user_qr_code_hash" class="block text-[13px] font-semibold text-slate-600 mb-1.5">QR Code Hash (Gerado Automaticamente)</label>
                    <div class="tooltip-container" style="display: block;">
                        <input type="text" name="qr_code_hash" id="user_qr_code_hash" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" style="background-color: rgba(0,0,0,0.05); cursor: not-allowed;" placeholder="user_101" readonly required>
                        <span class="tooltip-text">Hash encriptado vinculado a este usuário no banco de dados.</span>
                    </div>
                </div>

                <div class="mb-4" id="group-ativo" style="display: none; align-items: center; gap: 8px;">
                    <input type="checkbox" name="ativo" id="ativo" value="1" checked>
                    <label for="ativo" class="block text-[13px] font-semibold text-slate-600 mb-1.5" style="margin-bottom: 0;">Usuário Ativo</label>
                </div>

                <div class="flex gap-3 justify-end mt-6">
                    <button type="button" class="px-4 py-2 text-sm font-bold rounded-lg cursor-pointer bg-transparent text-slate-500 hover:bg-slate-100 transition-colors" onclick="closeUserModal()">Cancelar</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold rounded-lg cursor-pointer bg-green-600 text-white hover:bg-green-700 transition-colors shadow-sm">Salvar Usuário</button>
                </div>
            </form>
        </div>
    </div>

    <form id="delete-form" method="POST" action="<?= BASE_URL ?>/admin/usuarios" style="display: none;">
        <?php renderizar_csrf_input(); ?>
        <input type="hidden" name="action" value="delete_usuario">
        <input type="hidden" name="id" id="delete-id">
    </form>

    <script>
        function openUserModal() {
            document.getElementById('user-modal-title').textContent = "Cadastrar Novo Usuário";
            document.getElementById('user-action').value = "add_usuario";
            document.getElementById('user-id').value = "";
            document.getElementById('nome').value = "";
            document.getElementById('email').value = "";
            document.getElementById('matricula').value = "";
            document.getElementById('perfil_id').value = "2"; // Professor default
            document.getElementById('user_qr_code_hash').value = "";
            document.getElementById('group-ativo').style.display = "none";
            document.getElementById('user-modal').classList.add('active');
        }

        function openEditUserModal(user) {
            document.getElementById('user-modal-title').textContent = "Editar Usuário";
            document.getElementById('user-action').value = "edit_usuario";
            document.getElementById('user-id').value = user.id;
            document.getElementById('nome').value = user.nome;
            document.getElementById('email').value = user.email;
            document.getElementById('matricula').value = user.matricula;
            document.getElementById('perfil_id').value = user.perfil_id;
            document.getElementById('user_qr_code_hash').value = user.qr_code_hash;
            document.getElementById('ativo').checked = user.ativo == 1;
            document.getElementById('group-ativo').style.display = "flex";
            document.getElementById('user-modal').classList.add('active');
        }

        function closeUserModal() {
            document.getElementById('user-modal').classList.remove('active');
        }

        function confirmDeleteUser(id) {
            if (confirm("Deseja realmente deletar este usuário?")) {
                document.getElementById('delete-id').value = id;
                document.getElementById('delete-form').submit();
            }
        }

        // Preenchimento de Hash Automático
        document.getElementById('matricula').addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-Z0-9_-]/g, '');
            document.getElementById('user_qr_code_hash').value = this.value ? 'user_' + this.value : '';
        });

        // Ordenação Interativa de Tabela
        let sortDirections = {};
        function sortTable(colIndex) {
            const table = document.querySelector("table");
            const tbody = table.querySelector("tbody");
            const rows = Array.from(tbody.querySelectorAll("tr"));
            
            if (rows.length === 1 && rows[0].cells.length === 1) return;

            const currentDir = sortDirections[colIndex] || 'desc';
            const nextDir = currentDir === 'desc' ? 'asc' : 'desc';
            sortDirections[colIndex] = nextDir;

            // Ordenar linhas
            rows.sort((a, b) => {
                const cellA = a.cells[colIndex].textContent.trim();
                const cellB = b.cells[colIndex].textContent.trim();

                const numA = parseFloat(cellA);
                const numB = parseFloat(cellB);
                if (!isNaN(numA) && !isNaN(numB)) {
                    return nextDir === 'asc' ? numA - numB : numB - numA;
                }

                return nextDir === 'asc' 
                    ? cellA.localeCompare(cellB, 'pt-BR', { numeric: true, sensitivity: 'base' })
                    : cellB.localeCompare(cellA, 'pt-BR', { numeric: true, sensitivity: 'base' });
            });

            // Re-inserir ordenadas
            rows.forEach(row => tbody.appendChild(row));
        }

        // Filtro/Pesquisa Rápida na Tabela
        function filterTable() {
            const query = document.getElementById('search-input').value.toLowerCase();
            const rows = document.querySelectorAll("table tbody tr");
            
            if (rows.length === 1 && rows[0].cells.length === 1) return;

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
    <script src="<?= BASE_URL ?>/api/admin_responsive.js"></script>
</body>
</html>
