<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aviso de Atraso - PegaChave</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { background-color: #16a34a; padding: 25px 30px; text-align: center; color: #ffffff; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .content { padding: 30px; color: #334155; line-height: 1.6; }
        .content p { margin-bottom: 15px; font-size: 16px; }
        .highlight-box { background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 15px 20px; margin: 25px 0; border-radius: 0 8px 8px 0; }
        .highlight-box p { margin: 5px 0; color: #991b1b; }
        .highlight-box strong { color: #7f1d1d; }
        .footer { background-color: #f1f5f9; padding: 20px 30px; text-align: center; font-size: 13px; color: #64748b; }
        .btn { display: inline-block; padding: 12px 24px; background-color: #16a34a; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Alerta: Chave Pendente</h1>
        </div>
        <div class="content">
            <p>Olá, <strong><?php echo htmlspecialchars($nome); ?></strong>!</p>
            
            <p>Notamos em nosso sistema que você realizou a retirada de uma chave, mas ela ainda não foi devolvida à portaria.</p>
            
            <div class="highlight-box">
                <p><strong>Chave/Sala:</strong> <?php echo htmlspecialchars($sala); ?></p>
                <p><strong>Data de Retirada:</strong> <?php echo htmlspecialchars($data_retirada); ?></p>
                <p><strong>Tempo em Posse:</strong> <?php echo htmlspecialchars($tempo_posse); ?> horas</p>
            </div>
            
            <p>Para garantir a segurança do local e a disponibilidade para os próximos usuários, pedimos a gentileza de devolvê-la assim que possível.</p>
            
            <p><em>Se você já devolveu esta chave recentemente, por favor desconsidere este e-mail.</em></p>
        </div>
        <div class="footer">
            Mensagem automática gerada pelo sistema <strong>PegaChave</strong> da sua instituição.<br>
            Por favor, não responda a este e-mail.
        </div>
    </div>
</body>
</html>
