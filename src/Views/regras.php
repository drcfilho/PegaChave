<?php
require_once __DIR__ . '/../../api/db.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regras e Instruções - <?php echo htmlspecialchars($nome_escola); ?></title>
    
    <!-- PWA Meta -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0284c7">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="logo_pwa.jpg">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #f1f5f9;
            --text-color: #1e293b;
            --primary-blue: <?php echo $cor_primaria; ?>;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
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
            padding: 40px 20px;
            display: flex;
            justify-content: center;
        }

        .rules-container {
            background-color: var(--card-bg);
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            max-width: 700px;
            width: 100%;
            padding: 40px;
        }

        .header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 20px;
        }

        .logo-icon {
            font-size: 36px;
        }

        h1 {
            font-size: 24px;
            font-weight: 800;
            color: var(--text-color);
        }

        h2 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-color);
            margin-top: 25px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        p {
            font-size: 15px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 15px;
        }

        ol, ul {
            margin-left: 20px;
            margin-bottom: 20px;
            color: #475569;
            font-size: 15px;
            line-height: 1.6;
        }

        li {
            margin-bottom: 8px;
        }

        .alert-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            color: #78350f;
            padding: 16px;
            border-radius: 8px;
            font-size: 14px;
            margin: 25px 0;
            line-height: 1.5;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: var(--primary-blue);
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 15px;
            margin-top: 20px;
            transition: background 0.2s;
        }

        .btn-back:hover {
            filter: brightness(0.9);
        }
    </style>
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
<body>

    <div class="rules-container">
        <div class="header">
            <span class="logo-icon">🔑</span>
            <div>
                <h1>Regras de Uso do PegaChave</h1>
                <p style="margin: 0; font-size: 13px; color: #64748b;">Instruções e boas práticas para controle de chaves da <?php echo htmlspecialchars($nome_escola); ?>.</p>
            </div>
        </div>

        <h2>📖 Como Retirar uma Chave</h2>
        <ol>
            <li>Aproxime o QR Code do seu <strong>Crachá de Usuário</strong> da câmera do Quiosque.</li>
            <li>Aguarde a mensagem de saudação na tela informando que seu perfil foi identificado.</li>
            <li>Aproxime o QR Code do <strong>Chaveiro da Chave</strong> que deseja retirar.</li>
            <li>Aguarde a confirmação verde na tela indicando que a chave foi associada a você. Pronto!</li>
        </ol>

        <h2>↩ Como Devolver uma Chave</h2>
        <ul>
            <li><strong>Não é necessário crachá para devoluções:</strong> Apenas aproxime o QR Code da chave física da câmera do Quiosque.</li>
            <li>O sistema identificará a chave, fechará o histórico de uso e atualizará a sala para "Disponível".</li>
            <li>Coloque a chave de volta no quadro de chaves/claviculário da portaria.</li>
        </ul>

        <div class="alert-box">
            <strong>⚠️ Atenção e Responsabilidade:</strong><br>
            A chave ficará registrada em seu nome até que seja devolvida no Quiosque. Não passe chaves a outros servidores sem registrar a devolução e a nova retirada correspondente. Em caso de perda da etiqueta QR Code, notifique imediatamente a Administração da Escola.
        </div>

        <h2>⏳ Horários e Prazos</h2>
        <p>As chaves devem ser devolvidas ao final do expediente de cada turno ou assim que o ambiente correspondente não estiver mais em uso. O sistema emitirá alertas automáticos no painel do administrador para chaves que ficarem em uso por mais de 8 horas consecutivas.</p>

        <a href="<?= BASE_URL ?>/" class="btn-back">
            <span>↩</span> Voltar ao Quiosque
        </a>
    </div>

    <script>
        // Inicializar Tema de acordo com a preferência salva
        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.body.classList.add('dark-theme');
            }
        })();
    </script>
</body>
</html>
