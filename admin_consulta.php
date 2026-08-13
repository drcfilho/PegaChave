<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}
require_once __DIR__ . '/api/db.php';

try {
    // Buscar todas as chaves e status atuais
    $query = "
        SELECT 
            c.id, 
            c.nome_sala, 
            c.codigo_sala, 
            c.status_disponivel, 
            c.descricao,
            u.nome AS reservado_por_nome, 
            p.nome AS reservado_por_perfil,
            DATE_FORMAT(m.data_retirada, '%d/%m/%Y às %H:%i') AS data_retirada_formatada
        FROM chaves c
        LEFT JOIN movimentacoes m ON c.id = m.chave_id AND m.data_devolucao IS NULL
        LEFT JOIN usuarios u ON m.usuario_id = u.id
        LEFT JOIN perfis p ON u.perfil_id = p.id
        ORDER BY c.nome_sala ASC
    ";

    $stmt = $pdo->query($query);
    $chaves = $stmt->fetchAll();

} catch (\PDOException $e) {
    echo "Erro ao carregar dados: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Chaves - Administrador</title>
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

        /* Busca */
        .search-container {
            margin-bottom: 25px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 14px 18px;
            padding-left: 45px;
            font-size: 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: #ffffff;
            color: var(--text-color);
            outline: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            transition: all 0.2s;
        }

        .search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.08);
        }

        .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            color: #94a3b8;
        }

        /* Cards Grid */
        .grid-chaves {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .card-chave {
            background-color: #ffffff;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            padding: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .card-title-text h3 {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .card-title-text span {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-badge {
            font-size: 10px;
            font-weight: 800;
            padding: 3px 8px;
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
            padding-top: 12px;
            margin-top: 12px;
            font-size: 13px;
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
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside>
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
                <a href="/admin_gerar_qr.php">🖨️ Gerar QR Codes</a>
            </li>
            <li class="sidebar-item active">
                <a href="/admin_consulta.php">🔍 Consultar Disponibilidade</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_relatorio.php">📝 Relatório Geral</a>
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
    <main>
        <div class="page-header">
            <div class="page-title">
                <h1>Consulta de Disponibilidade</h1>
                <p>Verifique o status de chaves e salas em tempo real de forma veloz.</p>
            </div>
        </div>

        <!-- Barra de Pesquisa -->
        <div class="search-container">
            <span class="search-icon">🔍</span>
            <input type="text" id="search" class="search-input" placeholder="Pesquisar por Sala, Código ou Nome do Servidor..." onkeyup="filterCards()">
        </div>

        <!-- Lista de Cards -->
        <div class="grid-chaves" id="keys-grid">
            <?php foreach ($chaves as $c): ?>
                <div class="card-chave" data-name="<?php echo strtolower(htmlspecialchars($c['nome_sala'] . ' ' . $c['codigo_sala'] . ' ' . ($c['reservado_por_nome'] ?? ''))); ?>">
                    <div>
                        <div class="card-header-flex">
                            <div class="card-title-text">
                                <h3><?php echo htmlspecialchars($c['nome_sala']); ?></h3>
                                <span>Código: <?php echo htmlspecialchars($c['codigo_sala']); ?></span>
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
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
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
