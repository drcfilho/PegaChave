<?php
// View para admin_gerar_qr.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de QR Codes - <?php echo htmlspecialchars($nome_escola); ?></title>
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
    <style>
        /* Print Styles */
        @media print {
            body * {
                visibility: hidden;
            }
            #print-preview-section, #print-preview-section * {
                visibility: visible;
            }
            #print-preview-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 0;
            }
            .preview-header {
                display: none;
            }
            .label-card {
                border: 1px solid #000;
                box-shadow: none;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans flex min-h-screen">

    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 ml-0 lg:ml-[260px] p-6 md:p-10 transition-all duration-300" id="admin-main">
        <div class="flex justify-between items-center mb-8 flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 mb-1">Gerador de Etiquetas QR Code</h1>
                <p class="text-sm text-slate-500">Selecione itens específicos ou imprima em lote de forma customizável.</p>
            </div>
            <button class="bg-[var(--primary)] hover:brightness-95 text-white font-bold py-3 px-5 rounded-lg shadow-sm transition-transform flex items-center gap-2" onclick="generateSelectedPreview()">
                🖨️ Gerar Grade Selecionada (<span id="selected-count">0</span>)
            </button>
        </div>

        <!-- Abas de Categoria -->
        <div class="flex gap-2 mb-6 border-b-2 border-slate-200 pb-3">
            <button class="tab-btn active bg-white text-[var(--primary)] shadow-sm px-5 py-2.5 text-[15px] font-bold rounded-lg transition-all" id="tab-chaves" onclick="switchTab('chaves')">
                🔑 Chaves/Salas
            </button>
            <button class="tab-btn bg-transparent text-slate-500 hover:text-slate-700 px-5 py-2.5 text-[15px] font-bold rounded-lg transition-all" id="tab-usuarios" onclick="switchTab('usuarios')">
                👤 Crachás de Usuários
            </button>
        </div>

        <!-- Barra de Busca -->
        <div class="flex gap-4 mb-5 items-center">
            <input type="text" id="search-box" class="flex-1 border border-slate-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:border-[var(--primary)] bg-white" placeholder="Buscar na lista..." onkeyup="filterItems()">
        </div>

        <!-- Lista de Itens -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden px-6 py-5">
            <!-- Tabela de Chaves -->
            <div style="overflow-x: auto;">
                <table id="table-chaves" class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th width="40" class="bg-slate-50 text-slate-500 border-b border-slate-200 px-4 py-3">
                                <label class="flex items-center cursor-pointer relative">
                                    <input type="checkbox" id="select-all-chaves" class="peer sr-only" onclick="toggleSelectAll('chaves')">
                                    <div class="w-5 h-5 border-2 border-slate-300 rounded bg-white peer-checked:bg-[var(--primary)] peer-checked:border-[var(--primary)] transition-all after:content-[''] after:absolute after:hidden peer-checked:after:block after:left-[6px] after:top-[2px] after:w-1.5 after:h-2.5 after:border-solid after:border-white after:border-b-2 after:border-r-2 after:rotate-45"></div>
                                </label>
                            </th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Sala / Ambiente</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Bloco</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Andar</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Código</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Hash do QR Code</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-chaves">
                        <?php foreach ($chaves as $c): ?>
                            <tr class="item-row hover:bg-slate-50 transition-colors" data-search="<?php echo strtolower(htmlspecialchars($c['nome'] . ' ' . $c['identificador'] . ' ' . ($c['bloco'] ?? '') . ' ' . ($c['andar'] ?? ''))); ?>">
                                <td class="px-4 py-3 border-b border-slate-100">
                                    <label class="flex items-center cursor-pointer relative">
                                        <input type="checkbox" class="chk-item chk-chave peer sr-only" value="<?php echo $c['id']; ?>" data-name="<?php echo htmlspecialchars($c['nome']); ?>" data-bloco="<?php echo htmlspecialchars($c['bloco'] ?? ''); ?>" data-andar="<?php echo htmlspecialchars($c['andar'] ?? ''); ?>" data-id="<?php echo htmlspecialchars($c['identificador']); ?>" data-hash="<?php echo htmlspecialchars($c['qr_code_hash']); ?>" data-cat="Chave" onchange="updateSelectedCount()">
                                        <div class="w-5 h-5 border-2 border-slate-300 rounded bg-white peer-checked:bg-[var(--primary)] peer-checked:border-[var(--primary)] transition-all after:content-[''] after:absolute after:hidden peer-checked:after:block after:left-[6px] after:top-[2px] after:w-1.5 after:h-2.5 after:border-solid after:border-white after:border-b-2 after:border-r-2 after:rotate-45"></div>
                                    </label>
                                </td>
                                <td class="px-4 py-3 border-b border-slate-100 text-sm text-slate-700"><strong><?php echo htmlspecialchars($c['nome']); ?></strong></td>
                                <td class="px-4 py-3 border-b border-slate-100 text-sm text-slate-700"><?php echo htmlspecialchars($c['bloco'] ?? '-'); ?></td>
                                <td class="px-4 py-3 border-b border-slate-100 text-sm text-slate-700"><?php echo htmlspecialchars($c['andar'] ?? '-'); ?></td>
                                <td class="px-4 py-3 border-b border-slate-100 text-sm text-slate-700"><?php echo htmlspecialchars($c['identificador']); ?></td>
                                <td class="px-4 py-3 border-b border-slate-100 text-sm"><code class="text-xs text-slate-500 bg-slate-100 px-1 py-0.5 rounded"><?php echo htmlspecialchars($c['qr_code_hash']); ?></code></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Tabela de Usuários -->
                <table id="table-usuarios" class="w-full text-left border-collapse" style="display: none;">
                    <thead>
                        <tr>
                            <th width="40" class="bg-slate-50 text-slate-500 border-b border-slate-200 px-4 py-3">
                                <label class="flex items-center cursor-pointer relative">
                                    <input type="checkbox" id="select-all-usuarios" class="peer sr-only" onclick="toggleSelectAll('usuarios')">
                                    <div class="w-5 h-5 border-2 border-slate-300 rounded bg-white peer-checked:bg-[var(--primary)] peer-checked:border-[var(--primary)] transition-all after:content-[''] after:absolute after:hidden peer-checked:after:block after:left-[6px] after:top-[2px] after:w-1.5 after:h-2.5 after:border-solid after:border-white after:border-b-2 after:border-r-2 after:rotate-45"></div>
                                </label>
                            </th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Nome</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Matrícula / Perfil</th>
                            <th class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider px-4 py-3 font-semibold">Hash do QR Code</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-usuarios">
                        <?php foreach ($usuarios as $u): ?>
                            <tr class="item-row hover:bg-slate-50 transition-colors" data-search="<?php echo strtolower(htmlspecialchars($u['nome'] . ' ' . $u['identificador'])); ?>">
                                <td class="px-4 py-3 border-b border-slate-100">
                                    <label class="flex items-center cursor-pointer relative">
                                        <input type="checkbox" class="chk-item chk-usuario peer sr-only" value="<?php echo $u['id']; ?>" data-name="<?php echo htmlspecialchars($u['nome']); ?>" data-id="<?php echo htmlspecialchars($u['identificador']); ?>" data-hash="<?php echo htmlspecialchars($u['qr_code_hash']); ?>" data-cat="Usuário" onchange="updateSelectedCount()">
                                        <div class="w-5 h-5 border-2 border-slate-300 rounded bg-white peer-checked:bg-[var(--primary)] peer-checked:border-[var(--primary)] transition-all after:content-[''] after:absolute after:hidden peer-checked:after:block after:left-[6px] after:top-[2px] after:w-1.5 after:h-2.5 after:border-solid after:border-white after:border-b-2 after:border-r-2 after:rotate-45"></div>
                                    </label>
                                </td>
                                <td class="px-4 py-3 border-b border-slate-100 text-sm text-slate-700"><strong><?php echo htmlspecialchars($u['nome']); ?></strong></td>
                                <td class="px-4 py-3 border-b border-slate-100 text-sm text-slate-700"><?php echo htmlspecialchars($u['identificador']); ?></td>
                                <td class="px-4 py-3 border-b border-slate-100 text-sm"><code class="text-xs text-slate-500 bg-slate-100 px-1 py-0.5 rounded"><?php echo htmlspecialchars($u['qr_code_hash']); ?></code></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Seção de Impressão (Preview) -->
    <div id="print-preview-section" class="hidden bg-white min-h-screen w-full p-10 absolute top-0 left-0 z-[1000]">
        <div class="preview-header flex justify-between items-center border-b-2 border-slate-200 pb-5 mb-8">
            <div>
                <h1 class="text-[22px] font-extrabold text-slate-900">Visualização de Impressão</h1>
                <p class="text-slate-500 text-sm mt-1">Confirme as etiquetas geradas e clique em Imprimir.</p>
            </div>
            <div class="flex gap-3">
                <button class="bg-transparent border border-slate-300 text-slate-600 px-5 py-3 font-bold rounded-lg hover:bg-slate-50 transition-colors" onclick="closePreview()">↩ Voltar</button>
                <button class="bg-red-600 text-white px-5 py-3 font-bold rounded-lg hover:brightness-95 transition-all shadow-sm" onclick="exportarPDF()">📄 Exportar PDF A4</button>
                <button class="bg-[var(--primary)] text-white px-5 py-3 font-bold rounded-lg hover:brightness-95 transition-all shadow-sm" onclick="window.print()">🖨️ Imprimir Agora</button>
            </div>
        </div>

        <div class="grid grid-cols-[repeat(auto-fill,minmax(180px,1fr))] gap-4 justify-center" id="preview-grid-container">
            <!-- Inserido dinamicamente via JS -->
        </div>
    </div>

    <script>
        let currentTab = 'chaves';

        function switchTab(tab) {
            currentTab = tab;
            const tabChaves = document.getElementById('tab-chaves');
            const tabUsuarios = document.getElementById('tab-usuarios');
            
            if (tab === 'chaves') {
                tabChaves.className = "tab-btn active bg-white text-[var(--primary)] shadow-sm px-5 py-2.5 text-[15px] font-bold rounded-lg transition-all";
                tabUsuarios.className = "tab-btn bg-transparent text-slate-500 hover:text-slate-700 px-5 py-2.5 text-[15px] font-bold rounded-lg transition-all";
            } else {
                tabUsuarios.className = "tab-btn active bg-white text-[var(--primary)] shadow-sm px-5 py-2.5 text-[15px] font-bold rounded-lg transition-all";
                tabChaves.className = "tab-btn bg-transparent text-slate-500 hover:text-slate-700 px-5 py-2.5 text-[15px] font-bold rounded-lg transition-all";
            }
            
            document.getElementById('table-chaves').style.display = tab === 'chaves' ? 'table' : 'none';
            document.getElementById('table-usuarios').style.display = tab === 'usuarios' ? 'table' : 'none';

            // Resetar busca
            document.getElementById('search-box').value = '';
            filterItems();
        }

        function filterItems() {
            const query = document.getElementById('search-box').value.toLowerCase().trim();
            const targetTbody = currentTab === 'chaves' ? 'tbody-chaves' : 'tbody-usuarios';
            const rows = document.querySelectorAll(`#${targetTbody} .item-row`);
            
            rows.forEach(row => {
                const searchData = row.getAttribute('data-search');
                if (searchData.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function toggleSelectAll(tab) {
            const selectAllChk = document.getElementById(tab === 'chaves' ? 'select-all-chaves' : 'select-all-usuarios');
            const chkItems = document.querySelectorAll(tab === 'chaves' ? '.chk-chave' : '.chk-usuario');
            
            chkItems.forEach(chk => {
                // Selecionar apenas as visíveis pelo filtro de busca
                const row = chk.closest('tr');
                if (row.style.display !== 'none') {
                    chk.checked = selectAllChk.checked;
                }
            });
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const count = document.querySelectorAll('.chk-item:checked').length;
            document.getElementById('selected-count').textContent = count;
        }

        function generateSelectedPreview() {
            const checkedBoxes = document.querySelectorAll('.chk-item:checked');
            
            if (checkedBoxes.length === 0) {
                alert("Por favor, selecione pelo menos um item para gerar as etiquetas.");
                return;
            }

            const container = document.getElementById('preview-grid-container');
            container.innerHTML = ''; // Limpar anterior

            checkedBoxes.forEach(chk => {
                const name = chk.getAttribute('data-name');
                const bloco = chk.getAttribute('data-bloco') || '';
                const andar = chk.getAttribute('data-andar') || '';
                const id = chk.getAttribute('data-id');
                const hash = chk.getAttribute('data-hash');
                const cat = chk.getAttribute('data-cat');

                const card = document.createElement('div');
                card.className = 'label-card bg-white border border-slate-200 rounded-lg p-4 text-center shadow-[0_2px_4px_rgba(0,0,0,0.02)] break-inside-avoid flex flex-col items-center';
                card.innerHTML = `
                    <div class="text-[9px] font-extrabold uppercase text-slate-600 border-b border-slate-200 pb-1 mb-2 w-full">
                        ${cat === 'Chave' ? '🔑 CHAVE' : '👤 CRACHÁ'}
                    </div>
                    <img class="w-[110px] h-[110px] mb-2" src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=${encodeURIComponent(hash)}" alt="QR Code">
                    <div class="no-print mt-1.5 mb-1">
                        <button onclick="downloadQRCode('${hash}', '${name.replace(/'/g, "\\'")}', '${id.replace(/'/g, "\\'")}', '${cat}', '${bloco.replace(/'/g, "\\'")}', '${andar.replace(/'/g, "\\'")}', '${cat}_${name.replace(/[^a-zA-Z0-9]/g, '_')}')" class="bg-[var(--primary)] text-white border-none px-2 py-1 rounded text-[11px] font-semibold cursor-pointer">⬇️ Baixar</button>
                    </div>
                    <div class="text-[13px] font-extrabold text-slate-900 mb-0.5">${name}</div>
                    ${bloco || andar ? `<div class="text-[11px] text-slate-600 font-semibold mb-1">${bloco ? 'Bloco: ' + bloco : ''} ${bloco && andar ? '|' : ''} ${andar ? 'Andar: ' + andar : ''}</div>` : ''}
                    <div class="text-[11px] text-slate-500 font-semibold">${id}</div>
                    <div class="text-[9px] font-mono text-slate-400 mt-1">${hash}</div>
                `;
                container.appendChild(card);
            });

            const previewSection = document.getElementById('print-preview-section');
            previewSection.classList.remove('hidden');
            previewSection.classList.add('block');
            window.scrollTo(0, 0);
        }

        async function downloadQRCode(hash, name, id, cat, bloco, andar, filename) {
            try {
                const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(hash)}`;
                
                const img = new Image();
                img.crossOrigin = "anonymous";
                img.src = qrUrl;
                
                await new Promise((resolve, reject) => {
                    img.onload = resolve;
                    img.onerror = reject;
                });

                const canvas = document.createElement('canvas');
                canvas.width = 300;
                canvas.height = 410;
                const ctx = canvas.getContext('2d');

                // Fundo branco
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                // Desenhar QR Code (300x300)
                ctx.drawImage(img, 0, 0, 300, 300);

                // Textos
                ctx.fillStyle = '#1e293b';
                ctx.textAlign = 'center';

                // Categoria (🔑 CHAVE ou 👤 CRACHÁ)
                ctx.font = 'bold 11px sans-serif';
                ctx.fillText(cat === 'Chave' ? '🔑 CHAVE' : '👤 CRACHÁ', 150, 320);

                // Nome do item completo
                ctx.font = 'bold 14px sans-serif';
                let displayName = name;
                if (displayName.length > 28) {
                    displayName = displayName.substring(0, 26) + '...';
                }
                ctx.fillText(displayName, 150, 340);

                // Bloco e Andar (se existirem)
                ctx.font = 'bold 11px sans-serif';
                ctx.fillStyle = '#475569';
                if (bloco || andar) {
                    const locText = (bloco ? 'Bloco: ' + bloco : '') + (bloco && andar ? ' | ' : '') + (andar ? 'Andar: ' + andar : '');
                    ctx.fillText(locText, 150, 356);
                }

                // Identificador / Código
                ctx.font = '12px sans-serif';
                ctx.fillStyle = '#64748b';
                ctx.fillText(id, 150, 375);

                // Hash do QR
                ctx.font = '9px monospace';
                ctx.fillStyle = '#94a3b8';
                ctx.fillText(hash, 150, 395);

                canvas.toBlob(blob => {
                    const blobUrl = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = blobUrl;
                    a.download = `${filename}.png`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(blobUrl);
                }, 'image/png');

            } catch (err) {
                console.error("Erro ao gerar imagem composta do QR Code:", err);
                window.open(`https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(hash)}`, '_blank');
            }
        }

        function closePreview() {
            const previewSection = document.getElementById('print-preview-section');
            previewSection.classList.remove('block');
            previewSection.classList.add('hidden');
        }

        function exportarPDF() {
            const checkedBoxes = document.querySelectorAll('.chk-item:checked');
            if (checkedBoxes.length === 0) return;

            const items = [];
            checkedBoxes.forEach(chk => {
                items.push({
                    name: chk.getAttribute('data-name'),
                    bloco: chk.getAttribute('data-bloco') || '',
                    andar: chk.getAttribute('data-andar') || '',
                    id: chk.getAttribute('data-id'),
                    hash: chk.getAttribute('data-hash'),
                    cat: chk.getAttribute('data-cat')
                });
            });

            // Criar um form oculto para enviar via POST e gerar o download
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = window.location.href; // Mesma página

            const inputAction = document.createElement('input');
            inputAction.type = 'hidden';
            inputAction.name = 'action';
            inputAction.value = 'export_pdf';

            const inputItems = document.createElement('input');
            inputItems.type = 'hidden';
            inputItems.name = 'items';
            inputItems.value = JSON.stringify(items);

            form.appendChild(inputAction);
            form.appendChild(inputItems);
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
    </script>
    <script src="<?= BASE_URL ?>/api/admin_responsive.js"></script>
</body>
</html>
