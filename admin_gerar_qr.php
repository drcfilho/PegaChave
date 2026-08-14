<?php
// admin_gerar_qr.php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}
require_once __DIR__ . '/api/db.php';

try {
    // Buscar todas as chaves
    $chaves = $pdo->query("SELECT id, nome_sala AS nome, codigo_sala AS identificador, qr_code_hash, 'Chave' AS categoria FROM chaves ORDER BY nome_sala ASC")->fetchAll();
    
    // Buscar todos os usuários
    $usuarios = $pdo->query("SELECT id, nome, matricula AS identificador, qr_code_hash, 'Usuário' AS categoria FROM usuarios ORDER BY nome ASC")->fetchAll();
} catch (\PDOException $e) {
    echo "Erro de Banco de Dados: " . $e->getMessage();
    exit;
}
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
            transition: margin 0.2s;
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

        .btn-generate {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 12px 22px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 6px -1px rgba(2, 132, 199, 0.1);
            transition: transform 0.2s;
        }

        .btn-generate:hover {
            filter: brightness(0.95);
        }

        /* Abas */
        .tab-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 12px;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 10px 20px;
            font-size: 15px;
            font-weight: 700;
            color: #64748b;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .tab-btn.active {
            background-color: white;
            color: var(--primary);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        /* Filtro */
        .filter-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }

        .search-input {
            flex: 1;
            padding: 12px 16px;
            font-size: 14px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            outline: none;
            background-color: white;
        }

        /* Lista de Itens */
        .list-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 24px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }

        th {
            padding: 14px 16px;
            background-color: #f8fafc;
            color: #475569;
            font-weight: 700;
            border-bottom: 2px solid var(--border-color);
        }

        td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-color);
            color: #334155;
        }

        tr:hover td {
            background-color: #f8fafc;
        }

        /* Caixa de Seleção Customizada */
        .checkbox-container {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .checkbox-container input {
            display: none;
        }

        .checkmark {
            width: 20px;
            height: 20px;
            border: 2px solid #cbd5e1;
            border-radius: 4px;
            display: inline-block;
            margin-right: 10px;
            position: relative;
            background-color: white;
            transition: all 0.2s;
        }

        .checkbox-container input:checked + .checkmark {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .checkbox-container input:checked + .checkmark::after {
            content: "";
            position: absolute;
            left: 6px;
            top: 2px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        /* Visualização da Grade (Escondida por padrão) */
        #print-preview-section {
            display: none;
            background-color: white;
            min-height: 100vh;
            width: 100%;
            padding: 40px;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .btn-outline {
            background: none;
            border: 1px solid #cbd5e1;
            color: #475569;
            padding: 12px 22px;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
        }

        .btn-outline:hover {
            background-color: #f8fafc;
        }

        /* Grade de Impressão */
        .label-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
            justify-content: center;
        }

        .label-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            page-break-inside: avoid;
        }

        .label-card img {
            width: 110px;
            height: 110px;
            margin-bottom: 8px;
        }

        .item-name {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .item-id {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
        }

        .item-hash {
            font-size: 9px;
            font-family: monospace;
            color: #94a3b8;
            margin-top: 4px;
        }

        /* Impressão Apenas */
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
        }
    </style>
    <link rel="stylesheet" href="/api/admin_responsive.css">
</head>
<body>

    <!-- Sidebar -->
    <aside id="admin-sidebar">
        <div class="sidebar-header">
            <span>🔑</span> PegaChave
        </div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="/admin.php">📊 Dashboard</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_chaves.php">🔑 Chaves/Salas</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_usuarios.php">👤 Usuários</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_usuarios_arquivados.php">🗄️ Arquivados</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_reservas.php">📅 Agendamentos</a>
            </li>
            <li class="sidebar-item active">
                <a href="/admin_gerar_qr.php">🖨️ Gerar QR Codes</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_consulta.php">🔍 Consultar Disponibilidade</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_relatorio.php">📝 Relatório Geral</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_logs.php">📋 Logs de Auditoria</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_config.php">⚙️ Configurações</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_logout.php" style="color: #ef4444;">🚪 Sair</a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <a href="/index.php" class="btn-kiosk">🖥️ Voltar ao Quiosque</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main id="admin-main">
        <div class="page-header">
            <div class="page-title">
                <h1>Gerador de Etiquetas QR Code</h1>
                <p>Selecione itens específicos ou imprima em lote de forma customizável.</p>
            </div>
            <button class="btn-generate" onclick="generateSelectedPreview()">
                🖨️ Gerar Grade Selecionada (<span id="selected-count">0</span>)
            </button>
        </div>

        <!-- Abas de Categoria -->
        <div class="tab-buttons">
            <button class="tab-btn active" id="tab-chaves" onclick="switchTab('chaves')">
                🔑 Chaves/Salas
            </button>
            <button class="tab-btn" id="tab-usuarios" onclick="switchTab('usuarios')">
                👤 Crachás de Usuários
            </button>
        </div>

        <!-- Barra de Busca -->
        <div class="filter-row">
            <input type="text" id="search-box" class="search-input" placeholder="Buscar na lista..." onkeyup="filterItems()">
        </div>

        <!-- Lista de Itens -->
        <div class="list-card">
            <!-- Tabela de Chaves -->
            <table id="table-chaves">
                <thead>
                    <tr>
                        <th width="40">
                            <label class="checkbox-container">
                                <input type="checkbox" id="select-all-chaves" onclick="toggleSelectAll('chaves')">
                                <span class="checkmark"></span>
                            </label>
                        </th>
                        <th>Sala / Ambiente</th>
                        <th>Código</th>
                        <th>Hash do QR Code</th>
                    </tr>
                </thead>
                <tbody id="tbody-chaves">
                    <?php foreach ($chaves as $c): ?>
                        <tr class="item-row" data-search="<?php echo strtolower(htmlspecialchars($c['nome'] . ' ' . $c['identificador'])); ?>">
                            <td>
                                <label class="checkbox-container">
                                    <input type="checkbox" class="chk-item chk-chave" value="<?php echo $c['id']; ?>" data-name="<?php echo htmlspecialchars($c['nome']); ?>" data-id="<?php echo htmlspecialchars($c['identificador']); ?>" data-hash="<?php echo htmlspecialchars($c['qr_code_hash']); ?>" data-cat="Chave" onchange="updateSelectedCount()">
                                    <span class="checkmark"></span>
                                </label>
                            </td>
                            <td><strong><?php echo htmlspecialchars($c['nome']); ?></strong></td>
                            <td><?php echo htmlspecialchars($c['identificador']); ?></td>
                            <td><code style="font-size: 12px;"><?php echo htmlspecialchars($c['qr_code_hash']); ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Tabela de Usuários -->
            <table id="table-usuarios" style="display: none;">
                <thead>
                    <tr>
                        <th width="40">
                            <label class="checkbox-container">
                                <input type="checkbox" id="select-all-usuarios" onclick="toggleSelectAll('usuarios')">
                                <span class="checkmark"></span>
                            </label>
                        </th>
                        <th>Nome</th>
                        <th>Matrícula / Perfil</th>
                        <th>Hash do QR Code</th>
                    </tr>
                </thead>
                <tbody id="tbody-usuarios">
                    <?php foreach ($usuarios as $u): ?>
                        <tr class="item-row" data-search="<?php echo strtolower(htmlspecialchars($u['nome'] . ' ' . $u['identificador'])); ?>">
                            <td>
                                <label class="checkbox-container">
                                    <input type="checkbox" class="chk-item chk-usuario" value="<?php echo $u['id']; ?>" data-name="<?php echo htmlspecialchars($u['nome']); ?>" data-id="<?php echo htmlspecialchars($u['identificador']); ?>" data-hash="<?php echo htmlspecialchars($u['qr_code_hash']); ?>" data-cat="Usuário" onchange="updateSelectedCount()">
                                    <span class="checkmark"></span>
                                </label>
                            </td>
                            <td><strong><?php echo htmlspecialchars($u['nome']); ?></strong></td>
                            <td><?php echo htmlspecialchars($u['identificador']); ?></td>
                            <td><code style="font-size: 12px;"><?php echo htmlspecialchars($u['qr_code_hash']); ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Seção de Impressão (Preview) -->
    <div id="print-preview-section">
        <div class="preview-header">
            <div>
                <h1 style="font-size: 22px; font-weight: 800;">Visualização de Impressão</h1>
                <p style="color: #64748b; font-size: 14px;">Confirme as etiquetas geradas e clique em Imprimir.</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <button class="btn-outline" onclick="closePreview()">↩ Voltar</button>
                <button class="btn-generate" onclick="window.print()">🖨️ Imprimir Agora</button>
            </div>
        </div>

        <div class="label-grid" id="preview-grid-container">
            <!-- Inserido dinamicamente via JS -->
        </div>
    </div>

    <script>
        let currentTab = 'chaves';

        function switchTab(tab) {
            currentTab = tab;
            document.getElementById('tab-chaves').classList.toggle('active', tab === 'chaves');
            document.getElementById('tab-usuarios').classList.toggle('active', tab === 'usuarios');
            
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
                const id = chk.getAttribute('data-id');
                const hash = chk.getAttribute('data-hash');
                const cat = chk.getAttribute('data-cat');

                const card = document.createElement('div');
                card.className = 'label-card';
                card.innerHTML = `
                    <div style="font-size: 9px; font-weight: 800; text-transform: uppercase; color: #475569; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin-bottom: 8px;">
                        ${cat === 'Chave' ? '🔑 CHAVE' : '👤 CRACHÁ'}
                    </div>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=${encodeURIComponent(hash)}" alt="QR Code">
                    <div class="item-name">${name}</div>
                    <div class="item-id">${id}</div>
                    <div class="item-hash">${hash}</div>
                `;
                container.appendChild(card);
            });

            document.getElementById('print-preview-section').style.display = 'block';
            window.scrollTo(0, 0);
        }

        function closePreview() {
            document.getElementById('print-preview-section').style.display = 'none';
        }
    </script>
    <script src="/api/admin_responsive.js"></script>
</body>
</html>
