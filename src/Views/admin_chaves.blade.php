<?php
// View para admin_chaves.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Chaves - PegaChave</title>
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
    @include('partials.sidebar')

    <!-- Main Content -->
    <main class="flex-1 ml-0 lg:ml-[260px] p-6 md:p-10 transition-all duration-300">
        <div class="flex justify-between items-center mb-8 flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 mb-1">Gerenciamento de Chaves</h1>
                <p class="text-sm text-slate-500">Cadastre salas, edite informações e gerencie os códigos QR associados.</p>
            </div>
            <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                <input type="text" id="search-input" placeholder="🔍 Pesquisar chave..." class="w-full md:w-auto border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 min-w-[250px]" oninput="filterTable()">
                <button class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors flex items-center gap-2" onclick="openKeyModal()">
                    ➕ Nova Chave
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden px-6 py-5 mb-8">
            <div style="overflow-x: auto;">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="sortable cursor-pointer select-none transition-colors hover:bg-slate-100 hover:text-primary relative after:content-['\2195'] after:text-[10px] after:text-slate-400 after:ml-1 bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold" onclick="sortTable(0)">Sala/Ambiente</th>
                            <th class="sortable cursor-pointer select-none transition-colors hover:bg-slate-100 hover:text-primary relative after:content-['\2195'] after:text-[10px] after:text-slate-400 after:ml-1 bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold" onclick="sortTable(1)">Bloco</th>
                            <th class="sortable cursor-pointer select-none transition-colors hover:bg-slate-100 hover:text-primary relative after:content-['\2195'] after:text-[10px] after:text-slate-400 after:ml-1 bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold" onclick="sortTable(2)">Andar</th>
                            <th class="sortable cursor-pointer select-none transition-colors hover:bg-slate-100 hover:text-primary relative after:content-['\2195'] after:text-[10px] after:text-slate-400 after:ml-1 bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold" onclick="sortTable(3)">Código</th>
                            <th class="sortable cursor-pointer select-none transition-colors hover:bg-slate-100 hover:text-primary relative after:content-['\2195'] after:text-[10px] after:text-slate-400 after:ml-1 bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold" onclick="sortTable(4)">QR Hash</th>
                            <th class="sortable cursor-pointer select-none transition-colors hover:bg-slate-100 hover:text-primary relative after:content-['\2195'] after:text-[10px] after:text-slate-400 after:ml-1 bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold" onclick="sortTable(5)">Descrição</th>
                            <th class="sortable cursor-pointer select-none transition-colors hover:bg-slate-100 hover:text-primary relative after:content-['\2195'] after:text-[10px] after:text-slate-400 after:ml-1 bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold" onclick="sortTable(6)">Acesso Restrito</th>
                            <th class="sortable cursor-pointer select-none transition-colors hover:bg-slate-100 hover:text-primary relative after:content-['\2195'] after:text-[10px] after:text-slate-400 after:ml-1 bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold" onclick="sortTable(7)">Status</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($chaves)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; color: #64748b; padding: 25px;">
                                    Nenhuma chave cadastrada.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($chaves as $c): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><strong><?php echo htmlspecialchars($c['nome_sala']); ?></strong></td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><?php echo htmlspecialchars($c['bloco'] ?? '-'); ?></td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><?php echo htmlspecialchars($c['andar'] ?? '-'); ?></td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><code><?php echo htmlspecialchars($c['codigo_sala']); ?></code></td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><small><?php echo htmlspecialchars($c['qr_code_hash']); ?></small></td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm"><?php echo htmlspecialchars($c['descricao']); ?></td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm">
                                        <?php if (!empty($c['matriculas_permitidas'])): ?>
                                            <span style="background-color: #fee2e2; border: 1px solid #fecaca; color: #ef4444; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 700; display: inline-block;">Restrita</span>
                                            <div style="font-size: 11px; color: #64748b; margin-top: 4px; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($c['matriculas_permitidas']); ?>"><?php echo htmlspecialchars($c['matriculas_permitidas']); ?></div>
                                        <?php else: ?>
                                            <span style="color: #64748b; font-size: 12px;">Público</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm">
                                        <span style="color: <?php echo $c['status_disponivel'] ? '#22c55e' : '#ef4444'; ?>; font-weight: bold;">
                                            <?php echo $c['status_disponivel'] ? 'Disponível' : 'Retirada'; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 border-b border-slate-100 text-sm">
                                        <a class="text-primary font-bold cursor-pointer hover:underline" onclick="openEditKeyModal(<?php echo htmlspecialchars(json_encode($c)); ?>)">Editar</a>
                                        <span style="color: #cbd5e1;">|</span>
                                        <a class="text-red-500 font-bold cursor-pointer hover:underline" onclick="confirmDeleteChave(<?php echo $c['id']; ?>)">Excluir</a>
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
    <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-[200] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-250 [&.active]:opacity-100 [&.active]:pointer-events-auto" id="key-modal">
        <div class="bg-white rounded-2xl p-6 w-[95%] max-w-[480px] shadow-2xl transform scale-95 transition-all duration-250 [.active_&]:scale-100">
            <h3 class="text-xl font-bold mb-5" id="key-modal-title">Cadastrar Nova Chave</h3>
            <form method="POST" action="<?= BASE_URL ?>/admin/chaves">
                @csrf
                <input type="hidden" name="action" id="key-action" value="add_chave">
                <input type="hidden" name="id" id="key-id">
                
                <div class="mb-4">
                    <label for="nome_sala" class="block text-[13px] font-semibold text-slate-600 mb-1.5">Nome da Sala</label>
                    <div class="tooltip-container" style="display: block;">
                        <input type="text" name="nome_sala" id="nome_sala" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Ex: Sala 12 - Lab. Biologia" required>
                        <span class="tooltip-text">Nome descritivo do ambiente que ficará visível para os usuários na busca.</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="bloco" class="block text-[13px] font-semibold text-slate-600 mb-1.5">Bloco</label>
                    <div class="tooltip-container" style="display: block;">
                        <input type="text" name="bloco" id="bloco" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Ex: Bloco A">
                        <span class="tooltip-text">Edifício ou bloco onde a sala está localizada (opcional).</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="andar" class="block text-[13px] font-semibold text-slate-600 mb-1.5">Andar</label>
                    <div class="tooltip-container" style="display: block;">
                        <input type="text" name="andar" id="andar" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Ex: 2º Andar">
                        <span class="tooltip-text">Andar onde a sala se encontra (opcional).</span>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="matriculas_permitidas" class="block text-[13px] font-semibold text-slate-600 mb-1.5">Matrículas Autorizadas (Separadas por vírgula)</label>
                    <div class="tooltip-container" style="display: block;">
                        <input type="text" name="matriculas_permitidas" id="matriculas_permitidas" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Ex: 2023001, 2023002 (Deixe vazio para acesso público)">
                        <span class="tooltip-text">Apenas essas matrículas poderão retirar esta chave. Deixe vazio para não ter restrição.</span>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="codigo_sala" class="block text-[13px] font-semibold text-slate-600 mb-1.5">Código da Sala</label>
                    <div class="tooltip-container" style="display: block;">
                        <input type="text" name="codigo_sala" id="codigo_sala" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Ex: SALA-12" required>
                        <span class="tooltip-text">Identificador único (sem espaços). Usado para gerar o QR Code associado.</span>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="key_qr_code_hash" class="block text-[13px] font-semibold text-slate-600 mb-1.5">QR Code Hash (Gerado Automaticamente)</label>
                    <div class="tooltip-container" style="display: block;">
                        <input type="text" name="qr_code_hash" id="key_qr_code_hash" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" style="background-color: rgba(0,0,0,0.05); cursor: not-allowed;" placeholder="chaves_SALA-12" readonly required>
                        <span class="tooltip-text">Código final que será lido pelo scanner do quiosque.</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="descricao" class="block text-[13px] font-semibold text-slate-600 mb-1.5">Descrição</label>
                    <div class="tooltip-container" style="display: block;">
                        <textarea name="descricao" id="descricao" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" rows="3" placeholder="Informações adicionais..."></textarea>
                        <span class="tooltip-text">Qualquer outra informação sobre a sala ou chave.</span>
                    </div>
                </div>

                <div class="flex gap-3 justify-end mt-6">
                    <button type="button" class="px-4 py-2 text-sm font-bold rounded-lg cursor-pointer bg-transparent text-slate-500 hover:bg-slate-100 transition-colors" onclick="closeKeyModal()">Cancelar</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold rounded-lg cursor-pointer bg-green-600 text-white hover:bg-green-700 transition-colors shadow-sm">Salvar Chave</button>
                </div>
            </form>
        </div>
    </div>

    <form id="delete-form" method="POST" action="<?= BASE_URL ?>/admin/chaves/delete" style="display: none;">
        @csrf
        <input type="hidden" name="action" value="delete_chave">
        <input type="hidden" name="id" id="delete-id">
    </form>

    <script>
        function openKeyModal() {
            document.querySelector('#key-modal form').action = "<?= BASE_URL ?>/admin/chaves/create";
            document.getElementById('key-modal-title').textContent = "Cadastrar Nova Chave";
            document.getElementById('key-action').value = "add_chave";
            document.getElementById('key-id').value = "";
            document.getElementById('nome_sala').value = "";
            document.getElementById('bloco').value = "";
            document.getElementById('andar').value = "";
            document.getElementById('matriculas_permitidas').value = "";
            document.getElementById('codigo_sala').value = "";
            document.getElementById('key_qr_code_hash').value = "";
            document.getElementById('descricao').value = "";
            document.getElementById('key-modal').classList.add('active');
        }

        function openEditKeyModal(chave) {
            document.querySelector('#key-modal form').action = "<?= BASE_URL ?>/admin/chaves/update";
            document.getElementById('key-modal-title').textContent = "Editar Chave";
            document.getElementById('key-action').value = "edit_chave";
            document.getElementById('key-id').value = chave.id;
            document.getElementById('nome_sala').value = chave.nome_sala;
            document.getElementById('bloco').value = chave.bloco || "";
            document.getElementById('andar').value = chave.andar || "";
            document.getElementById('matriculas_permitidas').value = chave.matriculas_permitidas || "";
            document.getElementById('codigo_sala').value = chave.codigo_sala;
            document.getElementById('key_qr_code_hash').value = chave.qr_code_hash;
            document.getElementById('descricao').value = chave.descricao;
            document.getElementById('key-modal').classList.add('active');
        }

        function closeKeyModal() {
            document.getElementById('key-modal').classList.remove('active');
        }

        function confirmDeleteChave(id) {
            if (confirm("Deseja realmente deletar esta chave? Todas as movimentações dela serão deletadas.")) {
                document.getElementById('delete-id').value = id;
                document.getElementById('delete-form').submit();
            }
        }

        // Preenchimento de Hash Automático
        document.getElementById('codigo_sala').addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-Z0-9_-]/g, '');
            document.getElementById('key_qr_code_hash').value = this.value ? 'chaves_' + this.value : '';
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
