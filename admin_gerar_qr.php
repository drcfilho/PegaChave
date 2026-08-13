<?php
// admin_gerar_qr.php
require_once __DIR__ . '/api/db.php';

$tipo = $_GET['tipo'] ?? 'chaves'; // 'chaves', 'usuarios', 'selecionados'
$selecionados = $_GET['ids'] ?? [];

try {
    $itens = [];

    if ($tipo === 'chaves') {
        $itens = $pdo->query("SELECT id, nome_sala AS nome, codigo_sala AS identificador, qr_code_hash, 'Chave' AS categoria FROM chaves ORDER BY nome_sala ASC")->fetchAll();
    } elseif ($tipo === 'usuarios') {
        $itens = $pdo->query("SELECT id, nome, matricula AS identificador, qr_code_hash, 'Usuário' AS categoria FROM usuarios ORDER BY nome ASC")->fetchAll();
    } elseif ($tipo === 'selecionados' && !empty($selecionados)) {
        // Sanitizar IDs
        $idsStr = implode(',', array_map('intval', $selecionados));
        
        $origem = $_GET['origem'] ?? 'chaves';
        if ($origem === 'chaves') {
            $itens = $pdo->query("SELECT id, nome_sala AS nome, codigo_sala AS identificador, qr_code_hash, 'Chave' AS categoria FROM chaves WHERE id IN ($idsStr)")->fetchAll();
        } else {
            $itens = $pdo->query("SELECT id, nome, matricula AS identificador, qr_code_hash, 'Usuário' AS categoria FROM usuarios WHERE id IN ($idsStr)")->fetchAll();
        }
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
    <title>Gerador de QR Code - PegaChave</title>
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

        /* Abas de Opções */
        .tabs-container {
            background-color: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 25px;
        }

        .tab-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 12px;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 700;
            color: #64748b;
            cursor: pointer;
            border-radius: 6px;
        }

        .tab-btn:hover {
            color: #0f172a;
            background-color: #f1f5f9;
        }

        .tab-btn.active {
            color: #ffffff;
            background-color: var(--primary);
        }

        /* Grade de Etiquetas */
        .label-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .label-card {
            background-color: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .label-card img {
            width: 140px;
            height: 140px;
            margin-bottom: 8px;
        }

        .category-badge {
            background-color: #e0f2fe;
            color: #0369a1;
            font-size: 10px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 9999px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .category-badge.usuario {
            background-color: #dcfce7;
            color: #15803d;
        }

        .item-name {
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            margin: 2px 0;
            word-break: break-word;
            max-width: 180px;
        }

        .item-id {
            font-size: 11px;
            color: #64748b;
        }

        .item-hash {
            font-size: 8px;
            color: #94a3b8;
            font-family: monospace;
            word-break: break-all;
            margin-top: 4px;
            max-width: 160px;
        }

        /* Impressão */
        @media print {
            aside {
                display: none !important;
            }
            main {
                margin-left: 0 !important;
                padding: 0 !important;
            }
            .tabs-container, .btn-print {
                display: none !important;
            }
            .label-grid {
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 12px !important;
            }
            .label-card {
                border: 1px solid #000 !important;
                page-break-inside: avoid;
            }
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
                <a href="/admin_config.php">⚙️ Configurações</a>
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
                <h1>Gerador de Etiquetas QR Code</h1>
                <p>Visualize e imprima etiquetas de identificação rápidas para colar nos chaveiros ou crachás.</p>
            </div>
            <button class="btn-print" onclick="window.print()">
                🖨️ Imprimir Etiquetas
            </button>
        </div>

        <!-- Abas de Categoria -->
        <div class="tabs-container">
            <div class="tab-buttons">
                <button class="tab-btn <?php echo $tipo === 'chaves' ? 'active' : ''; ?>" onclick="window.location.href='admin_gerar_qr.php?tipo=chaves'">
                    🔑 Etiquetas de Chaves
                </button>
                <button class="tab-btn <?php echo $tipo === 'usuarios' ? 'active' : ''; ?>" onclick="window.location.href='admin_gerar_qr.php?tipo=usuarios'">
                    👤 Etiquetas de Crachás
                </button>
            </div>
            <p style="font-size: 13px; color: #64748b; margin-top: 5px;">Selecione a categoria acima para listar todos os cadastros prontos para impressão.</p>
        </div>

        <!-- Grade de QR Codes -->
        <div class="label-grid">
            <?php if (empty($itens)): ?>
                <div style="grid-column: 1/-1; text-align: center; color: #64748b; padding: 40px; background: white; border-radius: 12px; border: 1px solid var(--border-color);">
                    Nenhuma etiqueta encontrada para a categoria selecionada.
                </div>
            <?php else: ?>
                <?php foreach ($itens as $item): ?>
                    <div class="label-card">
                        <span class="category-badge <?php echo $item['categoria'] === 'Usuário' ? 'usuario' : ''; ?>">
                            <?php echo $item['categoria']; ?>
                        </span>
                        
                        <!-- Chamada para API de Geração de QR Code -->
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=<?php echo urlencode($item['qr_code_hash']); ?>" alt="QR Code">
                        
                        <div class="item-name"><?php echo htmlspecialchars($item['nome']); ?></div>
                        <div class="item-id"><?php echo htmlspecialchars($item['identificador']); ?></div>
                        <div class="item-hash"><?php echo htmlspecialchars($item['qr_code_hash']); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>
