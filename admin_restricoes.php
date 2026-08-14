<?php
// admin_restricoes.php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}
require_once __DIR__ . '/api/db.php';

$message = '';
$messageType = '';

// Buscar perfis e chaves
try {
    $perfis = $pdo->query("SELECT id, nome FROM perfis ORDER BY id ASC")->fetchAll();
    $chaves = $pdo->query("SELECT id, nome_sala, codigo_sala FROM chaves ORDER BY nome_sala ASC")->fetchAll();
} catch (\PDOException $e) {
    echo "Erro ao carregar dados: " . $e->getMessage();
    exit;
}

// Processar formulário de atualização de restrições
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validar_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = "Token de segurança inválido. Tente novamente.";
        $messageType = "error";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'save_restrictions') {
        try {
            $pdo->beginTransaction();

            // Limpar restrições anteriores
            $pdo->exec("DELETE FROM restricoes_acesso");

            // Inserir novas restrições enviadas
            $bloqueios = $_POST['bloqueio'] ?? []; // Array bidimensional [perfil_id][chave_id]
            $stmtInsert = $pdo->prepare("INSERT INTO restricoes_acesso (perfil_id, chave_id) VALUES (?, ?)");

            $count = 0;
            foreach ($bloqueios as $perfil_id => $chaves_bloqueadas) {
                foreach ($chaves_bloqueadas as $chave_id => $val) {
                    $stmtInsert->execute([(int)$perfil_id, (int)$chave_id]);
                    $count++;
                }
            }

            $pdo->commit();
            registrar_log($pdo, 'Configuração de Restrições', "Matriz de restrições de acesso atualizada. Total de bloqueios: $count.");
            
            $message = "Restrições de acesso salvas com sucesso!";
            $messageType = "success";
        } catch (\PDOException $e) {
            $pdo->rollBack();
            $message = "Erro ao salvar restrições: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Carregar restrições atuais para preencher a matriz
$restricoes = [];
try {
    $stmtRest = $pdo->query("SELECT perfil_id, chave_id FROM restricoes_acesso");
    if ($stmtRest) {
        while ($row = $stmtRest->fetch()) {
            $restricoes[$row['perfil_id']][$row['chave_id']] = true;
        }
    }
} catch (\Exception $e) {
    // Silencioso se tabela não existir
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restrições de Acesso - PegaChave</title>
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

        .sidebar-item a:hover, .sidebar-item.active a {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.02);
            border-left-color: var(--sidebar-active);
        }

        .sidebar-footer {
            padding: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .btn-kiosk {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.07);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 10px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-kiosk:hover {
            background-color: rgba(255, 255, 255, 0.15);
        }

        /* Main Content */
        main {
            flex: 1;
            margin-left: 260px;
            padding: 40px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title h1 {
            font-size: 28px;
            font-weight: 800;
        }

        .page-title p {
            color: #64748b;
            margin-top: 4px;
            font-size: 15px;
        }

        /* Cards e Layout */
        .content-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        /* Alertas */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-weight: 600;
            font-size: 15px;
        }

        .alert.success {
            background-color: #dcfce7;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .alert.error {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        /* Matriz de Bloqueio */
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            font-weight: 700;
            color: var(--text-color);
            font-size: 14px;
            background-color: rgba(0, 0, 0, 0.02);
        }

        body.dark-theme th {
            background-color: rgba(255, 255, 255, 0.02);
        }

        td {
            font-size: 14px;
        }

        /* Checkbox Customizado */
        .checkbox-container {
            display: block;
            position: relative;
            padding-left: 25px;
            cursor: pointer;
            font-size: 14px;
            user-select: none;
        }

        .checkbox-container input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .checkmark {
            position: absolute;
            top: 1px;
            left: 0;
            height: 18px;
            width: 18px;
            background-color: #cbd5e1;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        body.dark-theme .checkmark {
            background-color: #475569;
        }

        .checkbox-container:hover input ~ .checkmark {
            background-color: #94a3b8;
        }

        .checkbox-container input:checked ~ .checkmark {
            background-color: var(--error);
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }

        .checkbox-container input:checked ~ .checkmark:after {
            display: block;
        }

        .checkbox-container .checkmark:after {
            left: 6px;
            top: 2px;
            width: 4px;
            height: 9px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .btn-save {
            background-color: var(--primary);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.2s;
            margin-top: 20px;
        }

        .btn-save:hover {
            opacity: 0.9;
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
                <a href="/admin_restricoes.php">🔒 Restrições</a>
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
                <h1>Restrições de Acesso</h1>
                <p>Marque os cruzamentos para <strong>bloquear</strong> perfis específicos de retirarem determinadas chaves.</p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="content-card">
            <h2 class="card-title">Matriz de Perfis vs Salas (Marcado = Acesso Bloqueado)</h2>
            
            <form method="POST" action="admin_restricoes.php">
                <?php renderizar_csrf_input(); ?>
                <input type="hidden" name="action" value="save_restrictions">

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Chave / Sala</th>
                                <?php foreach ($perfis as $p): ?>
                                    <th style="text-align: center;"><?php echo htmlspecialchars($p['nome']); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($chaves)): ?>
                                <tr>
                                    <td colspan="<?php echo count($perfis) + 1; ?>" style="text-align: center; color: #64748b; padding: 30px;">
                                        Nenhuma chave cadastrada para configurar.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($chaves as $c): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($c['nome_sala']); ?></strong> (<?php echo htmlspecialchars($c['codigo_sala']); ?>)</td>
                                        <?php foreach ($perfis as $p): ?>
                                            <td style="text-align: center;">
                                                <!-- Ignora o perfil Administrador (ID 1) para evitar autobloqueio indesejado, pois Admin tem acesso total -->
                                                <?php if ($p['id'] == 1): ?>
                                                    <span style="font-size: 12px; color: #22c55e; font-weight: 700;">Livre</span>
                                                <?php else: ?>
                                                    <label class="checkbox-container" style="display: inline-block; padding-left: 18px;">
                                                        <input type="checkbox" name="bloqueio[<?php echo $p['id']; ?>][<?php echo $c['id']; ?>]" value="1" <?php echo isset($restricoes[$p['id']][$c['id']]) ? 'checked' : ''; ?>>
                                                        <span class="checkmark"></span>
                                                    </label>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="btn-save">Salvar Configurações de Acesso</button>
            </form>
        </div>
    </main>

    <script src="/api/admin_responsive.js"></script>
</body>
</html>
