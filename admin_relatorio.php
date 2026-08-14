<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}
require_once __DIR__ . '/api/db.php';

$usuario_id = $_GET['usuario_id'] ?? null;
$chave_id = $_GET['chave_id'] ?? null;
$data_inicio = $_GET['data_inicio'] ?? null;
$data_fim = $_GET['data_fim'] ?? null;

try {
    // Carregar filtros dinâmicos
    $chaves = $pdo->query("SELECT id, nome_sala, codigo_sala FROM chaves ORDER BY nome_sala ASC")->fetchAll();
    $usuarios = $pdo->query("SELECT id, nome FROM usuarios ORDER BY nome ASC")->fetchAll();

    // Buscar Movimentações com filtros
    $sql = "
        SELECT 
            m.id, 
            m.data_retirada, 
            m.data_devolucao, 
            m.observacao,
            u.nome AS usuario_nome, 
            u.matricula AS usuario_matricula,
            c.nome_sala, 
            c.codigo_sala
        FROM movimentacoes m
        JOIN usuarios u ON m.usuario_id = u.id
        JOIN chaves c ON m.chave_id = c.id
        WHERE 1=1
    ";
    
    $params = [];

    if ($usuario_id) {
        $sql .= " AND m.usuario_id = ?";
        $params[] = $usuario_id;
    }

    if ($chave_id) {
        $sql .= " AND m.chave_id = ?";
        $params[] = $chave_id;
    }

    if ($data_inicio) {
        $sql .= " AND m.data_retirada >= ?";
        $params[] = $data_inicio . " 00:00:00";
    }

    if ($data_fim) {
        $sql .= " AND m.data_retirada <= ?";
        $params[] = $data_fim . " 23:59:59";
    }

    $sql .= " ORDER BY m.data_retirada DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $movimentacoes = $stmt->fetchAll();

    // Exportação para CSV se solicitado
    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="relatorio_movimentacoes_' . date('Ymd_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeçalhos
        fputcsv($output, ['ID', 'Sala', 'Código Sala', 'Usuário', 'Matrícula', 'Data Retirada', 'Data Devolução', 'Observação'], ';');
        
        foreach ($movimentacoes as $row) {
            $dataRetirada = $row['data_retirada'] ? date('d/m/Y H:i:s', strtotime($row['data_retirada'])) : '';
            $dataDevolucao = $row['data_devolucao'] ? date('d/m/Y H:i:s', strtotime($row['data_devolucao'])) : 'Em aberto';
            
            fputcsv($output, [
                $row['id'],
                $row['nome_sala'],
                $row['codigo_sala'],
                $row['usuario_nome'],
                $row['usuario_matricula'],
                $dataRetirada,
                $dataDevolucao,
                $row['observacao']
            ], ';');
        }
        
        fclose($output);
        exit;
    }

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
    <title>Relatórios de Movimentação - PegaChave</title>
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

        .btn-print {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print:hover {
            background-color: var(--sidebar-active);
        }

        /* Formulário de Filtros */
        .filter-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 25px;
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) auto;
            gap: 16px;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }

        .form-control {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 14px;
            outline: none;
            background-color: #f8fafc;
        }

        .btn-filter {
            background-color: #334155;
            color: white;
            border: none;
            padding: 11px 24px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-filter:hover {
            background-color: #1e293b;
        }

        /* Relatório Card */
        .content-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 24px;
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

        .status-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 9999px;
            text-transform: uppercase;
        }

        .status-badge.retirada {
            background-color: rgba(239,68,68,0.1);
            color: var(--error);
        }

        .status-badge.devolvida {
            background-color: rgba(34,197,94,0.1);
            color: var(--success);
        }

        /* Estilo de Impressão */
        @media print {
            aside {
                display: none !important;
            }
            main {
                margin-left: 0 !important;
                padding: 0 !important;
            }
            .filter-card, .btn-print {
                display: none !important;
            }
            .content-card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
            th, td {
                padding: 8px 10px !important;
                border-bottom: 1px solid #000 !important;
            }
        }
    </style>
    <link rel="stylesheet" href="/api/admin_responsive.css">
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
                <a href="/admin_usuarios_arquivados.php">🗄️ Arquivados</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_reservas.php">📅 Agendamentos</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_restricoes.php">🔒 Restrições</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_gerar_qr.php">🖨️ Gerar QR Codes</a>
            </li>
            <li class="sidebar-item">
                <a href="/admin_consulta.php">🔍 Consultar Disponibilidade</a>
            </li>
            <li class="sidebar-item active">
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
                <h1>Relatório de Movimentação</h1>
                <p>Monitore e audite o histórico completo de retiradas e devoluções.</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <button class="btn-print" onclick="exportarCSV()" style="background-color: var(--primary); border-color: var(--primary); color: white;">
                    📊 Exportar CSV
                </button>
                <button class="btn-print" onclick="window.print()">
                    🖨️ Imprimir Página
                </button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filter-card">
            <form method="GET" action="admin_relatorio.php" class="filter-form">
                <div class="filter-group">
                    <label for="chave_id">Chave/Sala</label>
                    <select name="chave_id" id="chave_id" class="form-control">
                        <option value="">Todas</option>
                        <?php foreach ($chaves as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $chave_id == $c['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['nome_sala']); ?> (<?php echo htmlspecialchars($c['codigo_sala']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="usuario_id">Usuário</label>
                    <select name="usuario_id" id="usuario_id" class="form-control">
                        <option value="">Todos</option>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?php echo $u['id']; ?>" <?php echo $usuario_id == $u['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($u['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="data_inicio">Data Início</label>
                    <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="<?php echo htmlspecialchars($data_inicio ?? ''); ?>">
                </div>

                <div class="filter-group">
                    <label for="data_fim">Data Fim</label>
                    <input type="date" name="data_fim" id="data_fim" class="form-control" value="<?php echo htmlspecialchars($data_fim ?? ''); ?>">
                </div>

                <button type="submit" class="btn-filter">Filtrar</button>
            </form>
        </div>

        <!-- Tabela de Resultados -->
        <div class="content-card">
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Chave/Sala</th>
                            <th>Usuário (Matrícula)</th>
                            <th>Data Retirada</th>
                            <th>Data Devolução</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($movimentacoes)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #64748b; padding: 25px;">
                                    Nenhum registro de movimentação encontrado para os filtros selecionados.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($movimentacoes as $m): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($m['nome_sala']); ?></strong> (<?php echo htmlspecialchars($m['codigo_sala']); ?>)</td>
                                    <td><?php echo htmlspecialchars($m['usuario_nome']); ?> (<?php echo htmlspecialchars($m['usuario_matricula']); ?>)</td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($m['data_retirada'])); ?></td>
                                    <td><?php echo $m['data_devolucao'] ? date('d/m/Y H:i', strtotime($m['data_devolucao'])) : '-'; ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $m['data_devolucao'] ? 'devolvida' : 'retirada'; ?>">
                                            <?php echo $m['data_devolucao'] ? 'Devolvida' : 'Em Uso'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        function exportarCSV() {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('export', 'csv');
            window.location.href = window.location.pathname + '?' + urlParams.toString();
        }
    </script>
    <script src="/api/admin_responsive.js"></script>
</body>
</html>
