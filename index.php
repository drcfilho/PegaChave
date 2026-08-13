<?php
require_once __DIR__ . '/api/db.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiosque - <?php echo htmlspecialchars($nome_escola); ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #f1f5f9;
            --text-color: #1e293b;
            --primary-green: #22c55e;
            --primary-green-hover: #16a34a;
            --primary-blue: <?php echo $cor_primaria; ?>;
            --primary-blue-hover: #0369a1;
            --header-bg: <?php echo $cor_secundaria; ?>;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
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
        }

        /* Header Quiosque */
        header {
            width: 100%;
            background-color: var(--header-bg);
            color: #ffffff;
            padding: 16px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .header-left {
            font-size: 20px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 16px;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            background-color: #22c55e;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 8px #22c55e;
        }

        /* Container Principal */
        .main-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 30px;
            max-width: 600px;
        }

        /* Card do Quiosque */
        .quiosque-card {
            background-color: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            width: 100%;
            padding: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            border: 1px solid var(--border-color);
        }

        .instruction-title {
            font-size: 18px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 20px;
            color: #334155;
        }

        /* Área do Scanner */
        .camera-container {
            width: 100%;
            max-width: 380px;
            aspect-ratio: 1;
            background-color: #1e293b;
            border-radius: 50%;
            overflow: hidden;
            position: relative;
            border: 8px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.6);
        }

        #reader {
            width: 120% !important;
            height: 120% !important;
            transform: scale(1.2);
        }

        /* Marcador de Scan */
        .scan-target-box {
            position: absolute;
            width: 160px;
            height: 160px;
            border: 3px solid rgba(255, 255, 255, 0.4);
            border-radius: 24px;
            pointer-events: none;
            z-index: 5;
        }

        .camera-placeholder-icon {
            position: absolute;
            font-size: 48px;
            opacity: 0.2;
            color: #ffffff;
            pointer-events: none;
            z-index: 2;
        }

        .signal-icon {
            position: absolute;
            top: 25px;
            font-size: 20px;
            color: #38bdf8;
            opacity: 0.8;
            z-index: 3;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.4; }
            50% { opacity: 1; }
        }

        /* Botões de Ação */
        .action-buttons-row {
            display: flex;
            width: 100%;
            gap: 16px;
            margin-bottom: 20px;
        }

        .btn-action {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            border-radius: 8px;
            padding: 14px;
            font-size: 16px;
            font-weight: 700;
            color: #ffffff;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-action.retirar {
            background-color: var(--primary-green);
        }

        .btn-action.retirar:hover {
            background-color: var(--primary-green-hover);
        }

        .btn-action.devolver {
            background-color: #02529c;
        }

        .btn-action.devolver:hover {
            background-color: #013e77;
        }

        /* Input Inferior */
        .input-row {
            display: flex;
            width: 100%;
            gap: 12px;
            align-items: center;
        }

        .input-code-wrapper {
            flex: 1;
        }

        .input-code {
            width: 100%;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 15px;
            outline: none;
            background-color: #f8fafc;
            color: #334155;
            transition: border-color 0.2s;
        }

        .input-code:focus {
            border-color: #94a3b8;
        }

        .btn-help {
            background: none;
            border: none;
            color: #64748b;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 10px;
        }

        .btn-help:hover {
            color: #334155;
        }

        /* Tela de Confirmação (Sobreposição) */
        .success-overlay {
            position: fixed;
            inset: 0;
            background-color: #f1f5f9;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }

        .success-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .success-card {
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
            width: 100%;
            max-width: 420px;
            padding: 35px 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .success-icon-circle {
            width: 90px;
            height: 90px;
            background-color: #dcfce7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
        }

        .success-icon-circle svg {
            width: 48px;
            height: 48px;
            color: #22c55e;
        }

        .success-title {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 20px;
            color: #1e293b;
        }

        .success-details {
            width: 100%;
            background-color: #f8fafc;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            text-align: left;
            border: 1px solid #e2e8f0;
        }

        .detail-row {
            margin-bottom: 10px;
            font-size: 14px;
            color: #475569;
        }

        .detail-row:last-child {
            margin-bottom: 0;
        }

        .detail-row strong {
            color: #0f172a;
        }

        .success-buttons {
            display: flex;
            width: 100%;
            gap: 12px;
        }

        .btn-success-action {
            flex: 1;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            color: #ffffff;
        }

        .btn-success-action.back {
            background-color: var(--primary-green);
        }

        .btn-success-action.back:hover {
            background-color: var(--primary-green-hover);
        }

        .btn-success-action.other {
            background-color: var(--primary-blue);
        }

        .btn-success-action.other:hover {
            background-color: var(--primary-blue-hover);
        }

        /* Estilo temporário para alternar telas */
        .admin-link {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #0f172a;
            color: white;
            padding: 10px 18px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 6px;
            transition: transform 0.2s;
            z-index: 99;
        }

        .admin-link:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>

    <!-- Header Quiosque -->
    <header>
        <div class="header-left">
            <span><?php echo htmlspecialchars($nome_escola); ?></span>
        </div>
        <div class="header-right">
            <button onclick="window.location.href='/consulta.php'" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; margin-right: 15px; font-size: 13px;">🔍 Consultar Chaves</button>
            <span id="quiosque-time">[10:45]</span>
            <span style="display: flex; align-items: center; gap: 6px;">
                <span class="status-dot"></span> Online
            </span>
        </div>
    </header>

    <!-- Container Principal (Quiosque Principal) -->
    <div class="main-container" id="quiosque-view">
        <div class="quiosque-card">
            <div class="instruction-title" id="quiosque-title">
                Aproxime o QR Code do Crachá ou da Chave
            </div>

            <!-- Área da Câmera -->
            <div class="camera-container">
                <div class="signal-icon">(( • ))</div>
                <div class="camera-placeholder-icon">📷</div>
                <div class="scan-target-box"></div>
                <div id="reader"></div>
            </div>

            <!-- Botões de Ação -->
            <div class="action-buttons-row">
                <button class="btn-action retirar" onclick="setMode('retirar')">
                    <span>🔑</span> Retirar Chave
                </button>
                <button class="btn-action devolver" onclick="setMode('devolver')">
                    <span>↩</span> Devolver Chave
                </button>
            </div>

            <!-- Input de Código Manual -->
            <div class="input-row">
                <div class="input-code-wrapper">
                    <input type="text" id="manual-code" class="input-code" placeholder="Digitar Código (Sala)" onkeydown="checkEnter(event)">
                </div>
                <button class="btn-help" onclick="window.location.href='/regras.php'">
                    <span>❓</span> Ajuda
                </button>
            </div>
        </div>
    </div>

    <!-- Tela de Confirmação (Sucesso) -->
    <div class="success-overlay" id="success-view">
        <div class="success-card">
            <div class="success-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            
            <h2 class="success-title" id="success-status-title">Chave Retirada!</h2>
            
            <div class="success-details">
                <div class="detail-row">
                    <strong>Usuário:</strong> <span id="success-user-name">Prof. Carlos Silva (ID: 101)</span>
                </div>
                <div class="detail-row">
                    <strong>Chave:</strong> <span id="success-key-name">Sala 12 - Lab. Biologia</span>
                </div>
                <div class="detail-row">
                    <strong>Data/Hora:</strong> <span id="success-datetime">13/08/2026 - 10:46</span>
                </div>
            </div>

            <div class="success-buttons">
                <button class="btn-success-action back" onclick="closeSuccessScreen()">
                    <span>🔄</span> Voltar ao Início
                </button>
                <button class="btn-success-action other" onclick="window.location.href='/consulta.php'">
                    <span>❓</span> Consultar Outra
                </button>
            </div>
        </div>
    </div>

    <!-- Atalho para o Admin -->
    <a href="/admin.php" class="admin-link">
        <span>⚙️</span> Painel Admin
    </a>

    <!-- html5-qrcode library -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        // Atualizar Relógio
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('quiosque-time').textContent = `[${hours}:${minutes}]`;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Estado do Quiosque
        let html5QrcodeScanner;
        let isProcessing = false;
        let lastScannedCode = "";

        function setMode(mode) {
            const titleEl = document.getElementById('quiosque-title');
            if (mode === 'retirar') {
                titleEl.textContent = "Modo Retirada: Aproxime o Crachá do Usuário";
            } else if (mode === 'devolver') {
                titleEl.textContent = "Modo Devolução: Aproxime o QR Code da Chave";
            }
            
            // Feedback rápido nos botões
            setTimeout(() => {
                titleEl.textContent = "Aproxime o QR Code do Crachá ou da Chave";
            }, 10000);
        }

        // Iniciar Câmera
        function initScanner() {
            html5QrcodeScanner = new Html5Qrcode("reader");
            const config = { 
                fps: 10, 
                qrbox: { width: 180, height: 180 },
                aspectRatio: 1
            };

            html5QrcodeScanner.start(
                { facingMode: "user" },
                config,
                onScanSuccess
            ).catch(err => {
                console.warn("Câmera frontal não encontrada ou sem permissão. Tentando fallback...", err);
                Html5Qrcode.getCameras().then(devices => {
                    if (devices && devices.length > 0) {
                        html5QrcodeScanner.start(devices[0].id, config, onScanSuccess);
                    }
                });
            });
        }

        function onScanSuccess(decodedText) {
            if (isProcessing) return;
            if (decodedText === lastScannedCode) return;

            lastScannedCode = decodedText;
            processCode(decodedText);
        }

        function checkEnter(event) {
            if (event.key === 'Enter') {
                const code = event.target.value.trim();
                if (code) {
                    processCode(code);
                    event.target.value = '';
                }
            }
        }

        async function processCode(code) {
            isProcessing = true;
            try {
                const response = await fetch('/api/processar_scan.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ qr_code: code })
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    if (result.tipo === 'devolucao' || result.tipo === 'retirada') {
                        showSuccessScreen(result);
                    } else if (result.tipo === 'usuario') {
                        // Apenas atualiza o cabeçalho para orientar a leitura da chave
                        const titleEl = document.getElementById('quiosque-title');
                        titleEl.textContent = `Olá, ${result.usuario}! Agora aproxime a Chave.`;
                    }
                } else if (result.status === 'pending_user') {
                    alert(result.message);
                } else {
                    alert("Erro: " + result.message);
                }
            } catch (err) {
                console.error("Erro na API:", err);
            } finally {
                setTimeout(() => {
                    lastScannedCode = "";
                    isProcessing = false;
                }, 2000);
            }
        }

        function showSuccessScreen(data) {
            const now = new Date();
            const dateStr = now.toLocaleDateString('pt-BR');
            const timeStr = now.toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'});

            document.getElementById('success-status-title').textContent = data.tipo === 'devolucao' ? 'Chave Devolvida!' : 'Chave Retirada!';
            document.getElementById('success-user-name').textContent = data.usuario;
            document.getElementById('success-key-name').textContent = data.sala;
            document.getElementById('success-datetime').textContent = `${dateStr} - ${timeStr}`;

            document.getElementById('success-view').classList.add('active');

            // Retorno automático após 3 segundos
            setTimeout(closeSuccessScreen, 3000);
        }

        function closeSuccessScreen() {
            document.getElementById('success-view').classList.remove('active');
            document.getElementById('quiosque-title').textContent = "Aproxime o QR Code do Crachá ou da Chave";
        }

        window.addEventListener('DOMContentLoaded', initScanner);
    </script>
</body>
</html>
