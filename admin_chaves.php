<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}
require_once __DIR__ . '/api/db.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validar_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = "Token de segurança inválido. Tente novamente.";
        $messageType = "error";
    } else {
        $action = $_POST['action'];

    try {
        if ($action === 'add_chave') {
            $nome_sala = trim($_POST['nome_sala'] ?? '');
            $bloco = trim($_POST['bloco'] ?? '');
            $andar = trim($_POST['andar'] ?? '');
            $matriculas_permitidas = trim($_POST['matriculas_permitidas'] ?? '');
            $codigo_sala = trim($_POST['codigo_sala'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');

            // Saneamento básico e validações
            $nome_sala = htmlspecialchars($nome_sala, ENT_QUOTES, 'UTF-8');
            $bloco = htmlspecialchars($bloco, ENT_QUOTES, 'UTF-8');
            $andar = htmlspecialchars($andar, ENT_QUOTES, 'UTF-8');
            $matriculas_permitidas = htmlspecialchars($matriculas_permitidas, ENT_QUOTES, 'UTF-8');
            $codigo_sala = preg_replace('/[^a-zA-Z0-9_-]/', '', $codigo_sala);
            $descricao = htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8');

            // Gerar hash automaticamente
            $qr_code_hash = 'chaves_' . $codigo_sala;

            if (empty($nome_sala) || empty($codigo_sala) || empty($qr_code_hash)) {
                throw new Exception("Por favor, preencha todos os campos obrigatórios.");
            }

            $stmt = $pdo->prepare("INSERT INTO chaves (nome_sala, bloco, andar, matriculas_permitidas, codigo_sala, qr_code_hash, descricao) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nome_sala, $bloco, $andar, $matriculas_permitidas, $codigo_sala, $qr_code_hash, $descricao]);
            registrar_log($pdo, 'Cadastro de Chave', "Chave '$nome_sala' ($codigo_sala) cadastrada. Bloco: '$bloco', Andar: '$andar'. Restrita para: '$matriculas_permitidas'. Hash automático: $qr_code_hash");
            $message = "Chave '$nome_sala' cadastrada com sucesso!";
            $messageType = "success";
        } 
        
        elseif ($action === 'edit_chave') {
            $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
            $nome_sala = trim($_POST['nome_sala'] ?? '');
            $bloco = trim($_POST['bloco'] ?? '');
            $andar = trim($_POST['andar'] ?? '');
            $matriculas_permitidas = trim($_POST['matriculas_permitidas'] ?? '');
            $codigo_sala = trim($_POST['codigo_sala'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');

            $nome_sala = htmlspecialchars($nome_sala, ENT_QUOTES, 'UTF-8');
            $bloco = htmlspecialchars($bloco, ENT_QUOTES, 'UTF-8');
            $andar = htmlspecialchars($andar, ENT_QUOTES, 'UTF-8');
            $matriculas_permitidas = htmlspecialchars($matriculas_permitidas, ENT_QUOTES, 'UTF-8');
            $codigo_sala = preg_replace('/[^a-zA-Z0-9_-]/', '', $codigo_sala);
            $descricao = htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8');

            // Gerar hash automaticamente
            $qr_code_hash = 'chaves_' . $codigo_sala;

            if (!$id || empty($nome_sala) || empty($codigo_sala) || empty($qr_code_hash)) {
                throw new Exception("Todos os campos obrigatórios devem ser preenchidos.");
            }

            $stmt = $pdo->prepare("UPDATE chaves SET nome_sala = ?, bloco = ?, andar = ?, matriculas_permitidas = ?, codigo_sala = ?, qr_code_hash = ?, descricao = ? WHERE id = ?");
            $stmt->execute([$nome_sala, $bloco, $andar, $matriculas_permitidas, $codigo_sala, $qr_code_hash, $descricao, $id]);
            registrar_log($pdo, 'Edição de Chave', "Chave ID $id atualizada para '$nome_sala' ($codigo_sala). Bloco: '$bloco', Andar: '$andar'. Restrita para: '$matriculas_permitidas'. Hash automático: $qr_code_hash");
            $message = "Chave atualizada com sucesso!";
            $messageType = "success";
        } 
        
        elseif ($action === 'delete_chave') {
            $id = $_POST['id'] ?? '';
            // Obter nome antes de deletar
            $stmtChave = $pdo->prepare("SELECT nome_sala, codigo_sala FROM chaves WHERE id = ?");
            $stmtChave->execute([$id]);
            $chaveInfo = $stmtChave->fetch();
            $nomeExcluido = $chaveInfo ? $chaveInfo['nome_sala'] . ' (' . $chaveInfo['codigo_sala'] . ')' : "ID $id";

            // Limpar movimentações associadas primeiro
            $pdo->prepare("DELETE FROM movimentacoes WHERE chave_id = ?")->execute([$id]);
            
            $stmt = $pdo->prepare("DELETE FROM chaves WHERE id = ?");
            $stmt->execute([$id]);
            registrar_log($pdo, 'Remoção de Chave', "Chave '$nomeExcluido' e seu histórico foram removidos.");
            $message = "Chave removida.";
            $messageType = "success";
        }
    } catch (\PDOException $e) {
        $message = "Erro ao salvar dados: " . $e->getMessage();
        $messageType = "error";
    }
  }
}

try {
    $chaves = $pdo->query("SELECT * FROM chaves ORDER BY codigo_sala ASC")->fetchAll();
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
    <title>Gerenciamento de Chaves - PegaChave</title>
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

        .btn-add {
            background-color: var(--success);
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

        .btn-add:hover {
            background-color: var(--primary-green-hover);
        }

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

        th.sortable {
            cursor: pointer;
            position: relative;
            user-select: none;
            transition: background 0.2s;
        }

        th.sortable:hover {
            background-color: rgba(0,0,0,0.05);
            color: var(--primary);
        }

        body.dark-theme th.sortable:hover {
            background-color: rgba(255,255,255,0.05);
        }

        th.sortable::after {
            content: ' ↕';
            font-size: 10px;
            color: #94a3b8;
            margin-left: 4px;
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

        .action-link.delete {
            color: var(--error);
        }

        /* Modais */
        .modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: all 0.25s ease;
            z-index: 200;
        }

        .modal.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-content {
            background: #ffffff;
            border-radius: 12px;
            padding: 24px;
            width: 95%;
            max-width: 480px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            transform: scale(0.95);
            transition: all 0.25s ease;
        }

        .modal.active .modal-content {
            transform: scale(1);
        }

        .modal-title-text {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 18px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 14px;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary);
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 24px;
        }

        .btn-modal {
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }

        .btn-modal.cancel {
            background-color: transparent;
            color: #64748b;
        }

        .btn-modal.save {
            background-color: var(--primary);
            color: white;
        }

        .toast-message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #22c55e;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            z-index: 500;
            animation: slide-up-down 4s forwards;
        }

        @keyframes slide-up-down {
            0% { top: -60px; opacity: 0; }
            10% { top: 20px; opacity: 1; }
            90% { top: 20px; opacity: 1; }
            100% { top: -60px; opacity: 0; }
        }
    </style>
    <link rel="stylesheet" href="/api/admin_responsive.css">
</head>
<body>

    <?php if ($message): ?>
        <div class="toast-message">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Sidebar -->
    <aside>
        <div class="sidebar-header">
            <span>🔑</span> PegaChave
        </div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="/admin.php">📊 Dashboard</a>
            </li>
            <li class="sidebar-item active">
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
                <h1>Gerenciamento de Chaves</h1>
                <p>Cadastre salas, edite informações e gerencie os códigos QR associados.</p>
            </div>
            <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                <input type="text" id="search-input" placeholder="🔍 Pesquisar chave..." style="padding: 10px 16px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--card-bg); color: var(--text-color); font-size: 14px; font-weight: 500; min-width: 250px; outline: none; transition: border 0.2s;" oninput="filterTable()">
                <button class="btn-add" onclick="openKeyModal()">
                    ➕ Nova Chave
                </button>
            </div>
        </div>

        <div class="content-card">
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th class="sortable" onclick="sortTable(0)">Sala/Ambiente</th>
                            <th class="sortable" onclick="sortTable(1)">Bloco</th>
                            <th class="sortable" onclick="sortTable(2)">Andar</th>
                            <th class="sortable" onclick="sortTable(3)">Código</th>
                            <th class="sortable" onclick="sortTable(4)">QR Hash</th>
                            <th class="sortable" onclick="sortTable(5)">Descrição</th>
                            <th class="sortable" onclick="sortTable(6)">Acesso Restrito</th>
                            <th class="sortable" onclick="sortTable(7)">Status</th>
                            <th>Ações</th>
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
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($c['nome_sala']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($c['bloco'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($c['andar'] ?? '-'); ?></td>
                                    <td><code><?php echo htmlspecialchars($c['codigo_sala']); ?></code></td>
                                    <td><small><?php echo htmlspecialchars($c['qr_code_hash']); ?></small></td>
                                    <td><?php echo htmlspecialchars($c['descricao']); ?></td>
                                    <td>
                                        <?php if (!empty($c['matriculas_permitidas'])): ?>
                                            <span style="background-color: #fee2e2; border: 1px solid #fecaca; color: #ef4444; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 700; display: inline-block;">Restrita</span>
                                            <div style="font-size: 11px; color: #64748b; margin-top: 4px; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($c['matriculas_permitidas']); ?>"><?php echo htmlspecialchars($c['matriculas_permitidas']); ?></div>
                                        <?php else: ?>
                                            <span style="color: #64748b; font-size: 12px;">Público</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="color: <?php echo $c['status_disponivel'] ? '#22c55e' : '#ef4444'; ?>; font-weight: bold;">
                                            <?php echo $c['status_disponivel'] ? 'Disponível' : 'Retirada'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a class="action-link" onclick="openEditKeyModal(<?php echo htmlspecialchars(json_encode($c)); ?>)">Editar</a>
                                        <span style="color: #cbd5e1;">|</span>
                                        <a class="action-link delete" onclick="confirmDeleteChave(<?php echo $c['id']; ?>)">Excluir</a>
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
    <div class="modal" id="key-modal">
        <div class="modal-content">
            <h3 class="modal-title-text" id="key-modal-title">Cadastrar Nova Chave</h3>
            <form method="POST" action="admin_chaves.php">
                <?php renderizar_csrf_input(); ?>
                <input type="hidden" name="action" id="key-action" value="add_chave">
                <input type="hidden" name="id" id="key-id">
                
                <div class="form-group">
                    <label for="nome_sala">Nome da Sala</label>
                    <input type="text" name="nome_sala" id="nome_sala" class="form-control" placeholder="Ex: Sala 12 - Lab. Biologia" required>
                </div>

                <div class="form-group">
                    <label for="bloco">Bloco</label>
                    <input type="text" name="bloco" id="bloco" class="form-control" placeholder="Ex: Bloco A">
                </div>

                <div class="form-group">
                    <label for="andar">Andar</label>
                    <input type="text" name="andar" id="andar" class="form-control" placeholder="Ex: 2º Andar">
                </div>
                
                <div class="form-group">
                    <label for="matriculas_permitidas">Matrículas Autorizadas (Separadas por vírgula)</label>
                    <input type="text" name="matriculas_permitidas" id="matriculas_permitidas" class="form-control" placeholder="Ex: 2023001, 2023002 (Deixe vazio para acesso público)">
                </div>
                
                <div class="form-group">
                    <label for="codigo_sala">Código da Sala</label>
                    <input type="text" name="codigo_sala" id="codigo_sala" class="form-control" placeholder="Ex: SALA-12" required>
                </div>
                
                <div class="form-group">
                    <label for="key_qr_code_hash">QR Code Hash (Gerado Automaticamente)</label>
                    <input type="text" name="qr_code_hash" id="key_qr_code_hash" class="form-control" style="background-color: rgba(0,0,0,0.05); cursor: not-allowed;" placeholder="chaves_SALA-12" readonly required>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea name="descricao" id="descricao" class="form-control" rows="3" placeholder="Informações adicionais..."></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-modal cancel" onclick="closeKeyModal()">Cancelar</button>
                    <button type="submit" class="btn-modal save">Salvar Chave</button>
                </div>
            </form>
        </div>
    </div>

    <form id="delete-form" method="POST" action="admin_chaves.php" style="display: none;">
        <?php renderizar_csrf_input(); ?>
        <input type="hidden" name="action" value="delete_chave">
        <input type="hidden" name="id" id="delete-id">
    </form>

    <script>
        function openKeyModal() {
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
    <script src="/api/admin_responsive.js"></script>
</body>
</html>
