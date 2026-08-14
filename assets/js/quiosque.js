function quiosqueState() {
    return {
        isDark: false,
        currentTime: '',
        isOnline: navigator.onLine,
        offlineCount: 0,
        titleMessage: 'Aproxime o QR Code do Crachá ou da Chave',
        
        alert: {
            show: false,
            message: '',
            type: 'info'
        },
        
        successScreen: {
            show: false,
            title: '',
            userName: '',
            keyName: '',
            dateTime: ''
        },
        
        manualCode: '',
        isProcessing: false,
        lastScannedCode: '',
        scanner: null,

        init() {
            this.initTheme();
            this.updateClock();
            setInterval(() => this.updateClock(), 1000);
            
            this.updateNetworkStatus();
            window.addEventListener('online', () => {
                this.updateNetworkStatus();
                this.syncOfflineScans();
            });
            window.addEventListener('offline', () => this.updateNetworkStatus());
            
            this.initScanner();
            this.syncOfflineScans();
            setInterval(() => this.syncOfflineScans(), 10000);
        },

        initTheme() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                this.isDark = true;
                document.body.classList.add('dark-theme');
            }
        },

        toggleDarkMode() {
            this.isDark = !this.isDark;
            if (this.isDark) {
                document.body.classList.add('dark-theme');
                localStorage.setItem('theme', 'dark');
            } else {
                document.body.classList.remove('dark-theme');
                localStorage.setItem('theme', 'light');
            }
        },

        updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            this.currentTime = `[${hours}:${minutes}]`;
        },

        updateNetworkStatus() {
            this.isOnline = navigator.onLine;
            const queue = this.getOfflineQueue();
            this.offlineCount = queue.length;
        },

        getOfflineQueue() {
            return JSON.parse(localStorage.getItem('offline_scans') || '[]');
        },

        saveOfflineQueue(queue) {
            localStorage.setItem('offline_scans', JSON.stringify(queue));
            this.updateNetworkStatus();
        },

        async syncOfflineScans() {
            if (!this.isOnline) return;
            const queue = this.getOfflineQueue();
            if (queue.length === 0) return;

            while (queue.length > 0) {
                const item = queue[0];
                try {
                    const response = await fetch(`${window.BASE_URL}/api/processar_scan`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ qr_code: item.code })
                    });
                    
                    if (response.ok) {
                        queue.shift();
                        this.saveOfflineQueue(queue);
                    } else {
                        break;
                    }
                } catch (err) {
                    console.error("Falha ao sincronizar:", err);
                    break;
                }
            }
            this.updateNetworkStatus();
        },

        setMode(mode) {
            if (mode === 'retirar') {
                this.titleMessage = "Modo Retirada: Aproxime o Crachá do Usuário";
            } else if (mode === 'devolver') {
                this.titleMessage = "Modo Devolução: Aproxime o QR Code da Chave";
            }
            
            setTimeout(() => {
                this.titleMessage = "Aproxime o QR Code do Crachá ou da Chave";
            }, 10000);
        },

        initScanner() {
            this.scanner = new Html5Qrcode("reader");
            const config = { fps: 10, qrbox: { width: 180, height: 180 }, aspectRatio: 1 };
            
            this.scanner.start({ facingMode: "user" }, config, (decodedText) => this.onScanSuccess(decodedText))
                .catch(err => {
                    Html5Qrcode.getCameras().then(devices => {
                        if (devices && devices.length > 0) {
                            this.scanner.start(devices[0].id, config, (decodedText) => this.onScanSuccess(decodedText));
                        }
                    });
                });
        },

        onScanSuccess(decodedText) {
            if (this.isProcessing || decodedText === this.lastScannedCode) return;
            this.lastScannedCode = decodedText;
            this.processCode(decodedText);
        },

        submitManualCode() {
            const code = this.manualCode.trim();
            if (code) {
                this.processCode(code);
                this.manualCode = '';
            }
        },

        playBeep(type) {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                
                if (type === 'success') {
                    osc.frequency.value = 880;
                    gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.15);
                    osc.start(audioCtx.currentTime);
                    osc.stop(audioCtx.currentTime + 0.15);
                } else if (type === 'error') {
                    osc.type = 'sawtooth';
                    osc.frequency.value = 150;
                    gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.4);
                    osc.start(audioCtx.currentTime);
                    osc.stop(audioCtx.currentTime + 0.4);
                } else if (type === 'info') {
                    osc.frequency.value = 600;
                    gain.gain.setValueAtTime(0.08, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.1);
                    osc.start(audioCtx.currentTime);
                    osc.stop(audioCtx.currentTime + 0.15);
                }
            } catch (e) {
                console.warn("Audio indisponível");
            }
        },

        showAlert(msg, type = 'error', duration = 5000) {
            this.alert.message = msg;
            this.alert.type = type;
            this.alert.show = true;
            setTimeout(() => { this.alert.show = false; }, duration);
        },

        async processCode(code) {
            this.isProcessing = true;
            
            if (!this.isOnline || this.offlineCount > 0) {
                this.playBeep('success');
                const queue = this.getOfflineQueue();
                queue.push({ code: code, timestamp: new Date().toISOString() });
                this.saveOfflineQueue(queue);
                
                this.showAlert('Registro Gravado Offline! Será sincronizado em breve.', 'warning', 3500);
                setTimeout(() => { this.lastScannedCode = ""; this.isProcessing = false; }, 2000);
                return;
            }

            try {
                const response = await fetch(`${window.BASE_URL}/api/processar_scan`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ qr_code: code })
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    if (result.tipo === 'devolucao' || result.tipo === 'retirada') {
                        this.playBeep('success');
                        
                        const now = new Date();
                        this.successScreen.title = result.tipo === 'devolucao' ? 'Chave Devolvida!' : 'Chave Retirada!';
                        this.successScreen.userName = result.usuario;
                        this.successScreen.keyName = result.sala;
                        this.successScreen.dateTime = `${now.toLocaleDateString('pt-BR')} - ${now.toLocaleTimeString('pt-BR', {hour:'2-digit', minute:'2-digit'})}`;
                        this.successScreen.show = true;
                        
                        setTimeout(() => this.closeSuccessScreen(), 3000);
                    } else if (result.tipo === 'usuario') {
                        this.playBeep('info');
                        this.titleMessage = `Olá, ${result.usuario}! Agora aproxime a Chave.`;
                    }
                } else {
                    this.playBeep('error');
                    this.showAlert(result.message, result.status === 'pending_user' ? 'info' : 'error');
                }
            } catch (err) {
                this.showAlert("Servidor indisponível. Salvando leitura offline...", 'warning');
                const queue = this.getOfflineQueue();
                queue.push({ code: code, timestamp: new Date().toISOString() });
                this.saveOfflineQueue(queue);
            } finally {
                setTimeout(() => { this.lastScannedCode = ""; this.isProcessing = false; }, 2000);
            }
        },

        closeSuccessScreen() {
            this.successScreen.show = false;
            this.titleMessage = "Aproxime o QR Code do Crachá ou da Chave";
        }
    };
}
