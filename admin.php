<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}
require_once __DIR__ . '/api/db.php';

try {
    // 1. Obter Chaves em Uso (Atuais)
    $stmtUso = $pdo->query("
        SELECT 
            c.id AS chave_id,
            c.nome_sala,
            c.codigo_sala,
            u.nome AS usuario_nome,
            p.nome AS usuario_perfil,
            DATE_FORMAT(m.data_retirada, '%H:%i') AS desde,
            IF(m.data_retirada < DATE_SUB(NOW(), INTERVAL 8 HOUR), 1, 0) AS atrasada,
            m.id AS movimentacao_id
        FROM chaves c
        JOIN movimentacoes m ON c.id = m.chave_id AND m.data_devolucao IS NULL
        JOIN usuarios u ON m.usuario_id = u.id
        JOIN perfis p ON u.perfil_id = p.id
        ORDER BY m.data_retirada DESC
    ");
    $chavesEmUso = $stmtUso->fetchAll();

    // 2. Estatísticas
    $totalChaves = $pdo->query("SELECT COUNT(*) FROM chaves")->fetchColumn();
    $chavesEmUsoCount = count($chavesEmUso);
    $chavesDisponiveis = $totalChaves - $chavesEmUsoCount;

    // Contar devoluções de hoje
    $devolucoesHoje = $pdo->query("
        SELECT COUNT(*) FROM movimentacoes 
        WHERE DATE(data_devolucao) = CURDATE()
    ")->fetchColumn();

    // Contar retiradas de hoje
    $retiradasHoje = $pdo->query("
        SELECT COUNT(*) FROM movimentacoes 
        WHERE DATE(data_retirada) = CURDATE()
    ")->fetchColumn();

    // Contar pendentes
    $pendentesCount = $pdo->query("
        SELECT COUNT(*) FROM movimentacoes 
        WHERE data_devolucao IS NULL 
        AND data_retirada < DATE_SUB(NOW(), INTERVAL 8 HOUR)
    ")->fetchColumn();

    // 3. Analytics - Top 5 salas mais utilizadas
    $topSalas = $pdo->query("
        SELECT c.nome_sala, COUNT(m.id) AS total_retiradas
        FROM movimentacoes m
        JOIN chaves c ON m.chave_id = c.id
        GROUP BY c.id
        ORDER BY total_retiradas DESC
        LIMIT 5
    ")->fetchAll();

    // 4. Analytics - Fluxo de retiradas nos últimos 7 dias
    $fluxoSemanal = $pdo->query("
        SELECT 
            DATE_FORMAT(d.data, '%d/%m') AS dia_mes,
            COALESCE(COUNT(m.id), 0) AS total
        FROM (
            SELECT CURDATE() - INTERVAL 6 DAY AS data UNION ALL
            SELECT CURDATE() - INTERVAL 5 DAY UNION ALL
            SELECT CURDATE() - INTERVAL 4 DAY UNION ALL
            SELECT CURDATE() - INTERVAL 3 DAY UNION ALL
            SELECT CURDATE() - INTERVAL 2 DAY UNION ALL
            SELECT CURDATE() - INTERVAL 1 DAY UNION ALL
            SELECT CURDATE()
        ) d
        LEFT JOIN movimentacoes m ON DATE(m.data_retirada) = d.data
        GROUP BY d.data
        ORDER BY d.data ASC
    ")->fetchAll();

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
    <title>Dashboard - Administrador <?php echo htmlspecialchars($nome_escola); ?></title>
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

        /* Widgets/Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-info h3 {
            font-size: 14px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
        }

        .stat-icon {
            font-size: 32px;
            background: #f1f5f9;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .content-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 24px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #0f172a;
        }

        /* Tables */
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
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            cursor: pointer;
        }

        .action-link:hover {
            text-decoration: underline;
        }
    </style>
    <link rel="stylesheet" href="/api/admin_responsive.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <!-- Sidebar -->
    <aside>
        <div class="sidebar-header">
            <span>🔑</span> PegaChave
        </div>
        <ul class="sidebar-menu">
            <li class="sidebar-item active">
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
    <main>
        <div class="page-header">
            <div class="page-title">
                <h1>Painel Administrativo</h1>
                <p>Monitoramento e estatísticas rápidas do claviculário digital.</p>
            </div>
        </div>

        <?php if ($pendentesCount > 0): ?>
            <div class="alert-banner" style="background-color: #fee2e2; border: 1px solid #ef4444; border-left: 6px solid #ef4444; color: #991b1b; padding: 16px; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; font-size: 14px; font-weight: 600;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 20px;">⚠️</span>
                    <div>
                        Atenção: Existem <?php echo $pendentesCount; ?> chaves com atraso de devolução (em uso há mais de 8 horas)!
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Estatísticas Rápidas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Disponíveis</h3>
                    <div class="stat-value" style="color: var(--success);"><?php echo $chavesDisponiveis; ?></div>
                </div>
                <div class="stat-icon">🟢</div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Em Uso</h3>
                    <div class="stat-value" style="color: var(--error);"><?php echo $chavesEmUsoCount; ?></div>
                </div>
                <div class="stat-icon">🔴</div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Atrasadas</h3>
                    <div class="stat-value" style="color: #f59e0b;"><?php echo $pendentesCount; ?></div>
                </div>
                <div class="stat-icon">⚠️</div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Retiradas Hoje</h3>
                    <div class="stat-value" style="color: #6366f1;"><?php echo $retiradasHoje; ?></div>
                </div>
                <div class="stat-icon">🔑</div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Devoluções Hoje</h3>
                    <div class="stat-value" style="color: var(--success);"><?php echo $devolucoesHoje; ?></div>
                </div>
                <div class="stat-icon">↩</div>
            </div>
        </div>

        <!-- Gráficos Analíticos -->
        <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 25px;">
            <div class="content-card" style="margin-bottom: 0;">
                <h2 class="card-title">Fluxo de Retiradas (Últimos 7 dias)</h2>
                <div style="height: 220px; position: relative;">
                    <canvas id="chartFluxoSemanal"></canvas>
                </div>
            </div>
            <div class="content-card" style="margin-bottom: 0;">
                <h2 class="card-title">Top 5 Salas Mais Utilizadas</h2>
                <div style="height: 220px; position: relative;">
                    <canvas id="chartTopSalas"></canvas>
                </div>
            </div>
        </div>

        <!-- Chaves em Uso -->
        <div class="content-card">
            <h2 class="card-title">Chaves em Uso Atualmente</h2>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Chave/Sala</th>
                            <th>Portador</th>
                            <th>Cargo/Função</th>
                            <th>Hora da Retirada</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($chavesEmUso)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #64748b; padding: 25px;">
                                    Nenhuma chave retirada no momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($chavesEmUso as $c): ?>
                                 <tr <?php echo $c['atrasada'] ? 'style="background-color: #fee2e2;"' : ''; ?>>
                                     <td><strong><?php echo htmlspecialchars($c['nome_sala']); ?></strong> (<?php echo htmlspecialchars($c['codigo_sala']); ?>)</td>
                                     <td><?php echo htmlspecialchars($c['usuario_nome']); ?></td>
                                     <td><?php echo htmlspecialchars($c['usuario_perfil']); ?></td>
                                     <td>
                                         <?php echo htmlspecialchars($c['desde']); ?>
                                         <?php if ($c['atrasada']): ?>
                                             <span style="background-color: #ef4444; color: white; font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px; margin-left: 6px; display: inline-block;">ATRASADA ⚠️</span>
                                         <?php endif; ?>
                                     </td>
                                     <td><a class="action-link" style="color: var(--error);" onclick="devolverChave(<?php echo $c['chave_id']; ?>)">Devolver Manual</a></td>
                                 </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        async function devolverChave(chaveId) {
            if (!confirm("Deseja forçar a devolução manual desta chave?")) return;
            try {
                const response = await fetch('/api/status_chaves.php');
                const result = await response.json();
                const chaveObj = result.data.find(c => c.id == chaveId);
                
                if (chaveObj && chaveObj.qr_code_hash) {
                    const scanRes = await fetch('/api/processar_scan.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ qr_code: chaveObj.qr_code_hash })
                    });
                    const scanData = await scanRes.json();
                    alert(scanData.message);
                    window.location.reload();
                }
            } catch (err) {
                console.error(err);
                alert("Erro ao processar devolução.");
            }
        }

        // Configuração dos Gráficos Analíticos com Chart.js
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Gráfico de Fluxo Semanal (Linha)
            const ctxFluxo = document.getElementById('chartFluxoSemanal').getContext('2d');
            new Chart(ctxFluxo, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_column($fluxoSemanal, 'dia_mes')); ?>,
                    datasets: [{
                        label: 'Chaves Retiradas',
                        data: <?php echo json_encode(array_column($fluxoSemanal, 'total')); ?>,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });

            // 2. Gráfico de Top 5 Salas (Barras Horizontais)
            const ctxSalas = document.getElementById('chartTopSalas').getContext('2d');
            new Chart(ctxSalas, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($topSalas, 'nome_sala')); ?>,
                    datasets: [{
                        label: 'Total de Retiradas',
                        data: <?php echo json_encode(array_column($topSalas, 'total_retiradas')); ?>,
                        backgroundColor: 'rgba(2, 132, 199, 0.85)',
                        borderRadius: 6
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        });
    </script>
    <script src="/api/admin_responsive.js"></script>
</body>
</html>
