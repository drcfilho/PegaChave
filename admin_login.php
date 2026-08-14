<?php
// admin_login.php
session_start();
if (!defined('BASE_URL')) { define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\')); }
require_once __DIR__ . '/api/db.php';

// Se já estiver logado, redireciona para o admin.php
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: " . BASE_URL . "/admin");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validar_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Token de segurança inválido. Tente novamente.";
    } else {
        $usuarioInput = trim($_POST['usuario'] ?? '');
        $senhaInput = $_POST['senha'] ?? '';

        if ($usuarioInput && $senhaInput) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM administradores WHERE usuario = ?");
            $stmt->execute([$usuarioInput]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($senhaInput, $admin['senha'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['nome'];
                $_SESSION['admin_role'] = $admin['role'] ?? 'admin_master';
                $_SESSION['admin_permissoes'] = $admin['permissoes'] ? json_decode($admin['permissoes'], true) : [];
                
                header("Location: " . BASE_URL . "/admin");
                exit;
            } else {
                $error = "Usuário ou senha incorretos.";
            }
        } catch (\PDOException $e) {
            $error = "Erro no banco de dados: " . $e->getMessage();
        }
    } else {
        $error = "Por favor, preencha todos os campos.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrativo - <?php echo htmlspecialchars($nome_escola); ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #f1f5f9;
            --text-color: #1e293b;
            --primary: <?php echo $cor_primaria; ?>;
            --primary-hover: #0369a1;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background-color: var(--card-bg);
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            max-width: 400px;
            width: 100%;
            padding: 40px 30px;
            text-align: center;
        }

        .login-header {
            margin-bottom: 30px;
        }

        .login-logo {
            font-size: 40px;
            margin-bottom: 12px;
            display: inline-block;
        }

        .login-header h1 {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .login-header p {
            font-size: 14px;
            color: #64748b;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .form-control {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 14px;
            outline: none;
            background-color: #f8fafc;
            color: var(--text-color);
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary);
            background-color: #ffffff;
        }

        .error-message {
            background-color: #fee2e2;
            color: var(--error);
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: left;
            border: 1px solid #fca5a5;
        }

        .btn-login {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 14px;
            width: 100%;
            font-size: 15px;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-login:hover {
            filter: brightness(0.9);
        }

        .btn-kiosk {
            display: inline-block;
            margin-top: 20px;
            color: #64748b;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: color 0.2s;
        }

        .btn-kiosk:hover {
            color: #1e293b;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <span class="login-logo">⚙️</span>
            <h1>Painel Administrativo</h1>
            <p><?php echo htmlspecialchars($nome_escola); ?></p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                ⚠️ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="admin_login.php">
            <?php renderizar_csrf_input(); ?>
            <div class="form-group">
                <label for="usuario">Usuário</label>
                <input type="text" name="usuario" id="usuario" class="form-control" placeholder="Digite seu usuário" required autofocus>
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" name="senha" id="senha" class="form-control" placeholder="Digite sua senha" required>
            </div>

            <button type="submit" class="btn-login">Entrar</button>
        </form>

        <a href="index.php" class="btn-kiosk">
            🖥️ Voltar ao Quiosque
        </a>
    </div>

</body>
</html>
