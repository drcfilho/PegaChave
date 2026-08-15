<?php
// View do Quiosque com Alpine.js e Tailwind CSS
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiosque - <?php echo htmlspecialchars($nome_escola); ?></title>
    
    <!-- PWA Meta -->
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <meta name="theme-color" content="#16a34a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/logo_pwa.jpg">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS Dinâmico Básico -->
    <style>
        :root {
            --theme-cor-primaria: <?php echo $cor_primaria; ?>;
            --theme-cor-secundaria: <?php echo $cor_secundaria; ?>;
        }
        
        [x-cloak] { display: none !important; }
        
        body {
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        #reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            border-radius: 1rem;
        }
        #reader {
            border: none !important;
        }
    </style>
    
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
    
    <!-- Tailwind CSS via CDN para desenvolvimento -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        corePlugins: {
          preflight: true,
        },
        darkMode: 'class',
        theme: {
          extend: {
            colors: {
              brand: {
                50: '#f0fdf4',
                100: '#dcfce7',
                500: '#22c55e',
                600: '#16a34a',
                700: '#15803d',
                800: '#166534',
                900: '#14532d',
              }
            },
            fontFamily: {
              sans: ['Inter', 'sans-serif'],
            }
          }
        }
      }
    </script>
</head>
<body x-data="quiosqueState()" class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 flex flex-col min-h-screen transition-colors duration-300">

    <!-- Header Quiosque -->
    <header class="bg-brand-600 dark:bg-brand-800 text-white shadow-lg p-4 px-6 flex justify-between items-center z-20 shrink-0 transition-colors duration-300">
        <div class="font-bold text-lg tracking-tight">
            <span><?php echo htmlspecialchars($nome_escola); ?></span>
        </div>
        <div class="flex items-center gap-3 md:gap-4 text-sm font-medium">
            <button @click="toggleDarkMode()" class="bg-white/10 hover:bg-white/20 border border-white/20 text-white px-3 py-1.5 rounded-lg shadow-sm backdrop-blur-sm transition-all active:scale-95 flex items-center gap-1.5">
                <span>🌗</span> <span class="hidden sm:inline">Tema</span>
            </button>
            <button onclick="window.location.href='<?= BASE_URL ?>/consulta'" class="bg-white/10 hover:bg-white/20 border border-white/20 text-white px-3 py-1.5 rounded-lg shadow-sm backdrop-blur-sm transition-all active:scale-95 flex items-center gap-1.5">
                <span>🔍</span> <span class="hidden sm:inline">Consultar Chaves</span>
            </button>
            <span class="hidden md:inline bg-black/20 px-3 py-1.5 rounded-lg border border-black/10" x-text="currentTime"></span>
            
            <div class="flex items-center gap-2 bg-black/20 px-3 py-1.5 rounded-lg border border-black/10">
                <span class="relative flex h-3 w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" 
                        :class="isOnline && offlineCount === 0 ? 'bg-green-400' : 'bg-red-400'"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3" 
                        :class="isOnline && offlineCount === 0 ? 'bg-green-500' : 'bg-red-500'"></span>
                </span>
                <span class="hidden sm:inline" x-text="!isOnline || offlineCount > 0 ? `Offline (${offlineCount} pendentes)` : 'Online'"></span>
            </div>
        </div>
    </header>

    <!-- Container Principal -->
    <main class="flex-1 flex flex-col justify-center items-center p-3 sm:p-6 w-full max-w-3xl mx-auto relative z-10">
        
        <!-- Quiosque Card Principal -->
        <div class="w-full bg-white dark:bg-slate-800 rounded-3xl shadow-2xl p-5 sm:p-6 border border-slate-200 dark:border-slate-700 flex flex-col gap-4 sm:gap-5 transform transition-all relative overflow-visible">
            
            <!-- Banner de Alertas -->
            <div class="absolute -top-12 left-0 right-0 z-50 flex justify-center transition-all duration-300"
                 :class="{ 'opacity-100 translate-y-0': alert.show, 'opacity-0 translate-y-4 pointer-events-none': !alert.show }">
                <div class="px-6 py-3 rounded-2xl shadow-xl font-semibold border flex items-center gap-3 backdrop-blur-md"
                     :class="{
                         'bg-green-100/90 text-green-800 border-green-200 dark:bg-green-900/80 dark:text-green-200 dark:border-green-800': alert.type === 'success',
                         'bg-blue-100/90 text-blue-800 border-blue-200 dark:bg-blue-900/80 dark:text-blue-200 dark:border-blue-800': alert.type === 'info',
                         'bg-red-100/90 text-red-800 border-red-200 dark:bg-red-900/80 dark:text-red-200 dark:border-red-800': alert.type === 'error',
                         'bg-yellow-100/90 text-yellow-800 border-yellow-200 dark:bg-yellow-900/80 dark:text-yellow-200 dark:border-yellow-800': alert.type === 'warning'
                     }">
                     <span x-show="alert.type === 'success'">✅</span>
                     <span x-show="alert.type === 'error'">🚨</span>
                     <span x-show="alert.type === 'warning'">⚠️</span>
                     <span x-show="alert.type === 'info'">ℹ️</span>
                     <span x-text="alert.message"></span>
                </div>
            </div>

            <!-- Título da Instrução -->
            <div class="text-center font-semibold text-slate-500 dark:text-slate-400 text-sm tracking-wide uppercase" x-text="titleMessage"></div>

            <!-- Área da Câmera -->
            <div class="relative bg-slate-100 dark:bg-slate-900 rounded-2xl aspect-video max-h-[200px] sm:max-h-[280px] w-full flex justify-center items-center overflow-hidden border border-slate-200 dark:border-slate-700 shadow-inner group">
                
                <!-- Placeholder / Loading indicator -->
                <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 dark:text-slate-600 gap-3 z-0" wire:ignore.self>
                    <svg class="w-12 h-12 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="text-xs font-medium">Iniciando câmera...</span>
                </div>

                <!-- Elementos Decorativos Scanner -->
                <div class="absolute top-3 left-3 text-brand-500 animate-pulse font-bold text-[10px] flex items-center gap-1.5 z-10 pointer-events-none">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span> LIVE
                </div>
                
                <!-- Target Box Scanner -->
                <div class="absolute border border-brand-500/30 w-[60%] h-[60%] max-w-[200px] max-h-[200px] rounded-xl z-10 pointer-events-none
                            before:content-[''] before:absolute before:w-6 before:h-6 before:border-t-4 before:border-l-4 before:border-brand-500 before:-top-1 before:-left-1 before:rounded-tl-lg
                            after:content-[''] after:absolute after:w-6 after:h-6 after:border-t-4 after:border-r-4 after:border-brand-500 after:-top-1 after:-right-1 after:rounded-tr-lg">
                    <div class="absolute inset-0 pointer-events-none
                                before:content-[''] before:absolute before:w-6 before:h-6 before:border-b-4 before:border-l-4 before:border-brand-500 before:-bottom-1 before:-left-1 before:rounded-bl-lg
                                after:content-[''] after:absolute after:w-6 after:h-6 after:border-b-4 after:border-r-4 after:border-brand-500 after:-bottom-1 after:-right-1 after:rounded-br-lg"></div>
                </div>

                <!-- Div do leitor onde a lib injeta a câmera -->
                <div id="reader" wire:ignore class="absolute inset-0 w-full h-full z-0"></div>
            </div>

            <!-- Botões de Ação (Retirar/Devolver) -->
            <div class="flex flex-col sm:flex-row gap-4 w-full">
                <button class="flex-1 py-4 px-6 rounded-2xl font-bold text-lg sm:text-xl text-white shadow-lg hover:shadow-xl transition-all duration-300 active:scale-95 flex items-center justify-center gap-3 relative overflow-hidden bg-gradient-to-br from-emerald-500 to-brand-600 hover:from-emerald-400 hover:to-brand-500 group"
                        @click="setMode('retirar')">
                    <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 rounded-2xl"></div>
                    <span class="text-2xl relative z-10">🔑</span> 
                    <span class="relative z-10">Retirar Chave</span>
                </button>
                
                <button class="flex-1 py-4 px-6 rounded-2xl font-bold text-lg sm:text-xl text-white shadow-lg hover:shadow-xl transition-all duration-300 active:scale-95 flex items-center justify-center gap-3 relative overflow-hidden bg-gradient-to-br from-blue-500 to-indigo-600 hover:from-blue-400 hover:to-indigo-500 group"
                        @click="setMode('devolver')">
                    <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 rounded-2xl"></div>
                    <span class="text-2xl relative z-10">↩</span> 
                    <span class="relative z-10">Devolver Chave</span>
                </button>
            </div>

            <!-- Input de Código Manual -->
            <div class="flex gap-3 w-full">
                <div class="flex-1 relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-brand-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </div>
                    <input type="text" x-model="manualCode" @keydown.enter="submitManualCode" 
                           class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-white rounded-xl pl-11 pr-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all shadow-sm placeholder:text-slate-400 dark:placeholder:text-slate-500 font-medium" 
                           placeholder="Digitar Matrícula ou Sala">
                </div>
                <button class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 px-5 py-3.5 rounded-xl font-bold transition-all duration-200 shadow-sm flex items-center justify-center gap-2 active:scale-95 border border-slate-300 dark:border-slate-600"
                        onclick="window.location.href='<?= BASE_URL ?>/regras'">
                    <span>❓</span> <span class="hidden sm:inline">Ajuda</span>
                </button>
            </div>
            
        </div>
    </main>

    <!-- Tela de Confirmação (Sucesso Modal) -->
    <div class="fixed inset-0 bg-slate-900/60 dark:bg-black/80 backdrop-blur-sm z-50 flex justify-center items-center p-4 transition-all duration-300"
         :class="{ 'opacity-100 pointer-events-auto': successScreen.show, 'opacity-0 pointer-events-none': !successScreen.show }"
         x-cloak>
         
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] p-8 max-w-sm w-full shadow-2xl flex flex-col items-center gap-6 transform transition-all duration-500 border border-slate-200 dark:border-slate-700"
             :class="{ 'scale-100 translate-y-0': successScreen.show, 'scale-90 translate-y-8': !successScreen.show }"
             @click.away="closeSuccessScreen()">
             
            <div class="w-24 h-24 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center text-green-500 dark:text-green-400 mb-2 relative">
                <div class="absolute inset-0 rounded-full animate-ping bg-green-200 dark:bg-green-800/40 opacity-75"></div>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="w-12 h-12 relative z-10">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            
            <h2 class="text-2xl font-extrabold text-slate-800 dark:text-white text-center tracking-tight" x-text="successScreen.title"></h2>
            
            <div class="w-full bg-slate-50 dark:bg-slate-900/60 rounded-2xl p-5 flex flex-col gap-4 text-sm border border-slate-100 dark:border-slate-700">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700/50 pb-3">
                    <strong class="text-slate-500 dark:text-slate-400">Usuário:</strong> 
                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="successScreen.userName"></span>
                </div>
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700/50 pb-3">
                    <strong class="text-slate-500 dark:text-slate-400">Chave:</strong> 
                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="successScreen.keyName"></span>
                </div>
                <div class="flex justify-between items-center">
                    <strong class="text-slate-500 dark:text-slate-400">Data/Hora:</strong> 
                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="successScreen.dateTime"></span>
                </div>
            </div>

            <div class="flex flex-col w-full gap-3 mt-2">
                <button class="w-full bg-brand-500 hover:bg-brand-600 text-white font-bold py-3.5 px-4 rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95 flex justify-center items-center gap-2"
                        @click="closeSuccessScreen()">
                    <span>🔄</span> Voltar ao Início
                </button>
                <button class="w-full bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold py-3.5 px-4 rounded-xl transition-all active:scale-95 flex justify-center items-center gap-2"
                        onclick="window.location.href='<?= BASE_URL ?>/consulta'">
                    <span>🔍</span> Consultar Outras
                </button>
            </div>
        </div>
    </div>

    <!-- Atalho para o Admin -->
    <a href="<?= BASE_URL ?>/admin" class="fixed bottom-6 right-6 bg-slate-800 dark:bg-slate-100 text-white dark:text-slate-900 px-5 py-2.5 rounded-full font-bold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 flex items-center gap-2 text-sm z-40 opacity-50 hover:opacity-100 border border-slate-700 dark:border-slate-300 backdrop-blur-sm">
        <span>⚙️</span> Admin
    </a>

    <!-- Lógica Modularizada AlpineJS -->
    <script src="<?= BASE_URL ?>/assets/js/quiosque.js"></script>
</body>
</html>
