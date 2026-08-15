<?php
// View do Quiosque com Alpine.js
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiosque - <?php echo htmlspecialchars($nome_escola); ?></title>
    
    <!-- PWA Meta -->
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <meta name="theme-color" content="#0284c7">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/logo_pwa.jpg">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS Modularizado -->
    <style>
        :root {
            --theme-cor-primaria: <?php echo $cor_primaria; ?>;
            --theme-cor-secundaria: <?php echo $cor_secundaria; ?>;
        }
    </style>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/quiosque.css">
    
    <!-- Alpine.js e HTML5-QRCode -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>

    <script>
        window.BASE_URL = '<?= BASE_URL ?>';
        // Registrar Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register(`${window.BASE_URL}/service-worker.js`)
                    .catch(err => console.warn('Erro ao registrar Service Worker:', err));
            });
        }
    </script>
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
<body x-data="quiosqueState()">

    <!-- Header Quiosque -->
    <header>
        <div class="header-left">
            <span><?php echo htmlspecialchars($nome_escola); ?></span>
        </div>
        <div class="header-right">
            <button @click="toggleDarkMode()" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; margin-right: 15px; font-size: 13px;">🌗 Tema</button>
            <button onclick="window.location.href='<?= BASE_URL ?>/consulta'" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; margin-right: 15px; font-size: 13px;">🔍 Consultar Chaves</button>
            <span x-text="currentTime"></span>
            <span style="display: flex; align-items: center; gap: 6px;">
                <span class="status-dot" :class="isOnline && offlineCount === 0 ? 'online' : 'offline'"></span> 
                <span x-text="!isOnline || offlineCount > 0 ? `Offline (${offlineCount} pendentes)` : 'Online'"></span>
            </span>
        </div>
    </header>

    <!-- Container Principal (Quiosque Principal) -->
    <div class="main-container">
        <div class="quiosque-card">
            <!-- Banner de Alertas -->
            <div class="quiosque-alert" 
                 :class="{ 'active': alert.show, 'alert-success': alert.type === 'success', 'alert-info': alert.type === 'info', 'alert-error': alert.type === 'error', 'alert-warning': alert.type === 'warning' }"
                 x-text="alert.message"></div>
                 
            <div class="instruction-title" x-text="titleMessage"></div>

            <!-- Área da Câmera -->
            <div class="camera-container">
                <div class="signal-icon">(( • ))</div>
                <div class="camera-placeholder-icon">📷</div>
                <div class="scan-target-box"></div>
                <div id="reader" wire:ignore></div>
            </div>

            <!-- Botões de Ação -->
            <div class="action-buttons-row">
                <button class="btn-action retirar" @click="setMode('retirar')">
                    <span>🔑</span> Retirar Chave
                </button>
                <button class="btn-action devolver" @click="setMode('devolver')">
                    <span>↩</span> Devolver Chave
                </button>
            </div>

            <!-- Input de Código Manual -->
            <div class="input-row">
                <div class="input-code-wrapper">
                    <input type="text" x-model="manualCode" @keydown.enter="submitManualCode" class="input-code" placeholder="Digitar Código (Matrícula ou Sala/s)">
                </div>
                <button class="btn-help" onclick="window.location.href='<?= BASE_URL ?>/regras'">
                    <span>❓</span> Ajuda
                </button>
            </div>
        </div>
    </div>

    <!-- Tela de Confirmação (Sucesso) -->
    <div class="success-overlay" :class="{ 'active': successScreen.show }">
        <div class="success-card">
            <div class="success-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            
            <h2 class="success-title" x-text="successScreen.title"></h2>
            
            <div class="success-details">
                <div class="detail-row">
                    <strong>Usuário:</strong> <span x-text="successScreen.userName"></span>
                </div>
                <div class="detail-row">
                    <strong>Chave:</strong> <span x-text="successScreen.keyName"></span>
                </div>
                <div class="detail-row">
                    <strong>Data/Hora:</strong> <span x-text="successScreen.dateTime"></span>
                </div>
            </div>

            <div class="success-buttons">
                <button class="btn-success-action back" @click="closeSuccessScreen()">
                    <span>🔄</span> Voltar ao Início
                </button>
                <button class="btn-success-action other" onclick="window.location.href='<?= BASE_URL ?>/consulta'">
                    <span>❓</span> Consultar Outra
                </button>
            </div>
        </div>
    </div>

    <!-- Atalho para o Admin -->
    <a href="<?= BASE_URL ?>/admin" class="admin-link">
        <span>⚙️</span> Painel Admin
    </a>

    <!-- Lógica Modularizada AlpineJS -->
    <script src="<?= BASE_URL ?>/assets/js/quiosque.js"></script>
</body>
</html>
