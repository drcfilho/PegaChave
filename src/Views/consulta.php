<?php
// View da Consulta
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Chaves - <?php echo htmlspecialchars($nome_escola); ?></title>
    
    <!-- PWA Meta -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0284c7">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="logo_pwa.jpg">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #f8fafc;
            --text-color: #0f172a;
            --card-bg: #ffffff;
            --primary: <?php echo $cor_primaria; ?>;
            --success: #22c55e;
            --error: #ef4444;
            --border-color: #e2e8f0;
        }

        /* Suporte ao Tema Escuro */
        body.dark-theme {
            --bg-color: #0f172a;
            --text-color: #f8fafc;
            --card-bg: #1e293b;
            --border-color: #334155;
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 35px;
            max-width: 600px;
            width: 100%;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 800;
            color: var(--text-color);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .header p {
            color: #64748b;
            font-size: 15px;
        }

        /* Container de Busca */
        .search-container {
            max-width: 600px;
            width: 100%;
            margin-bottom: 30px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 16px 20px;
            padding-left: 50px;
            font-size: 16px;
            border-radius: 12px;
            border: 2px solid var(--border-color);
            background-color: var(--card-bg);
            color: var(--text-color);
            outline: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
        }

        .search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 10px 15px -3px rgba(2, 132, 199, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            color: #94a3b8;
            pointer-events: none;
        }

        /* Grid de Chaves */
        .grid-chaves {
            max-width: 900px;
            width: 100%;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .card-chave {
            background-color: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            padding: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card-chave:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .card-title h3 {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 4px;
        }

        .card-title span {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-badge {
            font-size: 11px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 9999px;
            text-transform: uppercase;
        }

        .status-badge.disponivel {
            background-color: #dcfce7;
            color: #15803d;
        }

        .status-badge.ocupada {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .card-details {
            border-top: 1px solid #f1f5f9;
            padding-top: 15px;
            margin-top: 15px;
            font-size: 13px;
        }
        .badge-loc {
            font-size: 11px;
            background: rgba(0,0,0,0.05);
            padding: 2px 6px;
            border-radius: 4px;
            color: #475569;
            font-weight: 500;
        }

        body.dark-theme .badge-loc {
            background: rgba(255,255,255,0.08);
            color: #cbd5e1;
        }
        .detail-row {
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
            color: #475569;
        }

        .detail-row:last-child {
            margin-bottom: 0;
        }

        .detail-row strong {
            color: #0f172a;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #0f172a;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 15px;
            margin-top: 40px;
            transition: background 0.2s;
        }

        .btn-back:hover {
            background-color: #1e293b;
        }
        /* Estilos do Modo Listagem */
        .grid-chaves.list-mode {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .grid-chaves.list-mode .card-chave {
            flex-direction: column;
            padding: 14px 20px;
            gap: 10px;
        }

        .grid-chaves.list-mode .card-chave:hover {
            transform: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .grid-chaves.list-mode .card-header {
            margin-bottom: 0;
            align-items: center;
            width: 100%;
        }

        .grid-chaves.list-mode .card-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .grid-chaves.list-mode .card-title h3 {
            margin-bottom: 0;
        }

        .grid-chaves.list-mode .card-details {
            margin-top: 0;
            border-top: 1px dashed var(--border-color);
            padding-top: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .grid-chaves.list-mode .detail-row {
            margin-bottom: 0;
        }
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

    <div class="header">
        <h1>🔍 Consulta de Disponibilidade</h1>
        <p>Verifique em tempo real se uma sala está livre ou com quem está a chave.</p>
    </div>

    <!-- Barra de Pesquisa -->
    <div class="search-container">
        <span class="search-icon">🔍</span>
        <input type="text" id="search" class="search-input" placeholder="Pesquisar por Sala, Código ou Professor..." onkeyup="filterCards()">
    </div>

    <!-- Seletor de Modo de Exibição -->
    <div style="display: flex; justify-content: flex-end; width: 100%; max-width: 900px; margin-bottom: 15px; gap: 8px;">
        <button id="btn-view-grid" onclick="setViewMode('grid')" style="background: var(--primary); color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;">
            <span>🔲</span> Cards
        </button>
        <button id="btn-view-list" onclick="setViewMode('list')" style="background: rgba(0,0,0,0.05); color: var(--text-color); border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;">
            <span>☰</span> Lista
        </button>
    </div>
    <!-- Lista de Cards -->
    <div class="grid-chaves" id="keys-grid">
        <?php foreach ($chaves as $c): ?>
            <div class="card-chave" data-name="<?php echo strtolower(htmlspecialchars($c['nome_sala'] . ' ' . $c['codigo_sala'] . ' ' . ($c['bloco'] ?? '') . ' ' . ($c['andar'] ?? '') . ' ' . ($c['reservado_por_nome'] ?? ''))); ?>">
                <div>
                    <div class="card-header">
                        <div class="card-title">
                            <h3><?php echo htmlspecialchars($c['nome_sala']); ?></h3>
                            <span style="font-size: 11px; font-weight: 600; color: #64748b;">Código: <?php echo htmlspecialchars($c['codigo_sala']); ?></span>
                            <?php if (!empty($c['bloco']) || !empty($c['andar'])): ?>
                                <span class="badge-loc">
                                    <?php 
                                        $loc = [];
                                        if (!empty($c['bloco'])) $loc[] = "Bloco: " . htmlspecialchars($c['bloco']);
                                        if (!empty($c['andar'])) $loc[] = "Andar: " . htmlspecialchars($c['andar']);
                                        echo implode(" | ", $loc);
                                    ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <span class="status-badge <?php echo $c['status_disponivel'] ? 'disponivel' : 'ocupada'; ?>">
                            <?php echo $c['status_disponivel'] ? 'Disponível' : 'Em Uso'; ?>
                        </span>
                    </div>

                    <?php if ($c['descricao']): ?>
                        <p style="font-size: 12px; color: #64748b; margin-top: -5px; margin-bottom: 10px; font-style: italic;">
                            <?php echo htmlspecialchars($c['descricao']); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <?php if (!$c['status_disponivel']): ?>
                    <div class="card-details">
                        <div class="detail-row">
                            <span>👤</span> <strong>Com:</strong> <?php echo htmlspecialchars($c['reservado_por_nome']); ?> (<?php echo htmlspecialchars($c['reservado_por_perfil']); ?>)
                        </div>
                        <div class="detail-row">
                            <span>⏰</span> <strong>Retirada:</strong> <?php echo htmlspecialchars($c['data_retirada_formatada']); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($reservasHoje[$c['id']])): ?>
                    <div style="margin-top: 12px; padding-top: 10px; border-top: 1px dashed var(--border-color); font-size: 13px;">
                        <span style="font-weight: 700; color: var(--primary); display: block; margin-bottom: 5px;">📅 Agendada hoje:</span>
                        <ul style="list-style: none; padding-left: 0;">
                            <?php foreach ($reservasHoje[$c['id']] as $rHoje): ?>
                                <li style="display: flex; align-items: center; gap: 6px; color: #64748b; margin-bottom: 2px;">
                                    <span>🕒</span>
                                    <span><?php echo date('H:i', strtotime($rHoje['hora_inicio'])) . ' - ' . date('H:i', strtotime($rHoje['hora_fim'])) . ' (' . htmlspecialchars($rHoje['reservado_nome']) . ')'; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <a href="<?= BASE_URL ?>/" class="btn-back">
        <span>↩</span> Voltar ao Quiosque
    </a>

    <script>
        // Inicializar Tema e Modo de Exibição
        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.body.classList.add('dark-theme');
            }
            
            const savedViewMode = localStorage.getItem('view_mode') || 'grid';
            setViewMode(savedViewMode);
        })();

        function setViewMode(mode) {
            const grid = document.getElementById('keys-grid');
            const btnGrid = document.getElementById('btn-view-grid');
            const btnList = document.getElementById('btn-view-list');
            if (!grid || !btnGrid || !btnList) return;
            
            const isDark = document.body.classList.contains('dark-theme');
            const inactiveBg = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)';
            const activeBg = 'var(--primary)';
            const activeColor = '#ffffff';
            const inactiveColor = 'var(--text-color)';

            if (mode === 'list') {
                grid.classList.add('list-mode');
                btnList.style.backgroundColor = activeBg;
                btnList.style.color = activeColor;
                btnGrid.style.backgroundColor = inactiveBg;
                btnGrid.style.color = inactiveColor;
                localStorage.setItem('view_mode', 'list');
            } else {
                grid.classList.remove('list-mode');
                btnGrid.style.backgroundColor = activeBg;
                btnGrid.style.color = activeColor;
                btnList.style.backgroundColor = inactiveBg;
                btnList.style.color = inactiveColor;
                localStorage.setItem('view_mode', 'grid');
            }
        }

        function filterCards() {
            const query = document.getElementById('search').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.card-chave');
            
            cards.forEach(card => {
                const searchData = card.getAttribute('data-name');
                if (searchData.includes(query)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>

</body>
</html>
