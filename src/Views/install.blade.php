<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação do PegaChave</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/dist/app.min.css">
</head>
<body class="h-full flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 space-y-6 text-center">
        <h1 class="text-3xl font-bold text-slate-800">PegaChave</h1>
        <p class="text-slate-500">Bem-vindo ao instalador! O sistema identificou que o banco de dados está vazio e precisa ser preparado.</p>
        
        <div id="loading" class="hidden flex flex-col items-center gap-3 py-4 text-brand-600 font-semibold">
            <svg class="animate-spin h-8 w-8 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            Instalando...
        </div>

        <div id="result" class="hidden p-4 rounded-lg text-sm text-left bg-slate-900 text-green-400 overflow-y-auto max-h-40 whitespace-pre-wrap font-mono"></div>

        <button id="btnInstalar" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 px-4 rounded-xl transition-all shadow-md active:scale-95" onclick="instalar()">
            Iniciar Instalação
        </button>

        <a id="btnAdmin" href="<?= BASE_URL ?>/admin" class="hidden w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 px-4 rounded-xl transition-all shadow-md active:scale-95 block">
            Acessar Painel Admin
        </a>
    </div>

    <script>
        function instalar() {
            document.getElementById('btnInstalar').classList.add('hidden');
            document.getElementById('loading').classList.remove('hidden');

            fetch('<?= BASE_URL ?>/install/run', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('loading').classList.add('hidden');
                let resultDiv = document.getElementById('result');
                resultDiv.classList.remove('hidden');
                resultDiv.innerText = data.log;
                
                if (data.status === 'success') {
                    document.getElementById('btnAdmin').classList.remove('hidden');
                }
            })
            .catch(err => {
                document.getElementById('loading').classList.add('hidden');
                alert("Erro ao instalar: " + err);
                document.getElementById('btnInstalar').classList.remove('hidden');
            });
        }
    </script>
</body>
</html>
