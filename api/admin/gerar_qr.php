<?php
// api/admin/gerar_qr.php
require_once __DIR__ . '/../db.php';

$tipo = $_GET['tipo'] ?? 'todas'; // 'chaves', 'usuarios', 'todas'

try {
    $chaves = [];
    $usuarios = [];

    if ($tipo === 'chaves' || $tipo === 'todas') {
        $chaves = $pdo->query("SELECT nome_sala, codigo_sala, qr_code_hash FROM chaves ORDER BY nome_sala ASC")->fetchAll();
    }

    if ($tipo === 'usuarios' || $tipo === 'todas') {
        $usuarios = $pdo->query("SELECT nome, matricula, qr_code_hash FROM usuarios ORDER BY nome ASC")->fetchAll();
    }

} catch (\PDOException $e) {
    echo "Erro ao carregar dados para QR Code: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Etiquetas QR Code</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            background-color: #f3f4f6;
            color: #333;
        }
        .header-print {
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background-color: #3b82f6;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn-print:hover {
            background-color: #2563eb;
        }
        .grid-etiquetas {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }
        .card-etiqueta {
            background-color: #fff;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            page-break-inside: avoid;
        }
        .card-etiqueta img {
            width: 150px;
            height: 150px;
            margin-bottom: 10px;
        }
        .badge {
            background-color: #e0f2fe;
            color: #0369a1;
            font-size: 11px;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 9999px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .badge.usuario {
            background-color: #dcfce7;
            color: #15803d;
        }
        .nome {
            font-size: 14px;
            font-weight: 700;
            margin: 4px 0;
            word-break: break-word;
        }
        .codigo {
            font-size: 12px;
            color: #6b7280;
        }
        .hash {
            font-size: 8px;
            color: #9ca3af;
            margin-top: 5px;
            font-family: monospace;
            word-break: break-all;
            max-width: 180px;
        }

        /* Configurações de Impressão */
        @media print {
            body {
                background-color: #fff;
                margin: 0;
            }
            .header-print {
                display: none;
            }
            .grid-etiquetas {
                grid-template-columns: repeat(3, 1fr);
                gap: 15px;
            }
            .card-etiqueta {
                border: 1px solid #000;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

    <div class="header-print">
        <div>
            <h1 style="margin: 0; font-size: 24px;">🖨️ Gerador de Etiquetas QR Code</h1>
            <p style="margin: 5px 0 0 0; color: #6b7280;">Etiquetas prontas para colagem em chaves e crachás.</p>
        </div>
        <button class="btn-print" onclick="window.print()">Imprimir Etiquetas</button>
    </div>

    <div class="grid-etiquetas">
        <!-- Loop Chaves -->
        <?php foreach ($chaves as $c): ?>
            <div class="card-etiqueta">
                <span class="badge">Chave</span>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($c['qr_code_hash']); ?>" alt="QR Code Sala">
                <div class="nome"><?php echo htmlspecialchars($c['nome_sala']); ?></div>
                <div class="codigo">Código: <?php echo htmlspecialchars($c['codigo_sala']); ?></div>
                <div class="hash"><?php echo htmlspecialchars($c['qr_code_hash']); ?></div>
            </div>
        <?php endforeach; ?>

        <!-- Loop Usuários -->
        <?php foreach ($usuarios as $u): ?>
            <div class="card-etiqueta">
                <span class="badge usuario">Usuário</span>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($u['qr_code_hash']); ?>" alt="QR Code Usuário">
                <div class="nome"><?php echo htmlspecialchars($u['nome']); ?></div>
                <div class="codigo">Matrícula: <?php echo htmlspecialchars($u['matricula']); ?></div>
                <div class="hash"><?php echo htmlspecialchars($u['qr_code_hash']); ?></div>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>
