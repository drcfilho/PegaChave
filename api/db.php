<?php
// api/db.php
global $pdo, $nome_escola, $cor_primaria, $cor_secundaria, $limite_chaves;

if (!function_exists('carregarEnv')) {
    function carregarEnv($caminho) {
        if (!file_exists($caminho)) {
            return false;
        }
        $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($linhas as $linha) {
            $linha = trim($linha);
            if ($linha === '' || strpos($linha, '#') === 0) {
                continue;
            }
            if (strpos($linha, '=') !== false) {
                list($nome, $valor) = explode('=', $linha, 2);
                $nome = trim($nome);
                $valor = trim($valor);
                if (preg_match('/^"(.*)"$/', $valor, $matches)) {
                    $valor = $matches[1];
                } elseif (preg_match('/^\'(.*)\'$/', $valor, $matches)) {
                    $valor = $matches[1];
                }
                $_ENV[$nome] = $valor;
                putenv("$nome=$valor");
            }
        }
        return true;
    }
}

carregarEnv(dirname(__DIR__) . '/.env');

$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';
$db   = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'controle_chaves';
$user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
$pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=$port";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     
     // Carregar configurações globais
     $nome_escola = 'Escola Lumiar';
     $cor_primaria = '#0284c7';
     $cor_secundaria = '#0f172a';
     $limite_chaves = 0; // 0 = Sem limite
     
     try {
         $stmtConfig = $pdo->query("SELECT chave, valor FROM configuracoes");
         if ($stmtConfig) {
             $configs = $stmtConfig->fetchAll(PDO::FETCH_KEY_PAIR);
             if (isset($configs['nome_escola'])) $nome_escola = $configs['nome_escola'];
             if (isset($configs['cor_primaria'])) $cor_primaria = $configs['cor_primaria'];
             if (isset($configs['cor_secundaria'])) $cor_secundaria = $configs['cor_secundaria'];
             if (isset($configs['limite_chaves'])) $limite_chaves = (int)$configs['limite_chaves'];
             if (isset($configs['modo_portaria'])) $modo_portaria = $configs['modo_portaria'];
             if (isset($configs['limite_atraso_horas'])) $limite_atraso_horas = (int)$configs['limite_atraso_horas'];
         }
     } catch (\Exception $exConfig) {
         // Fallback silencioso se a tabela de configurações não existir
     }
} catch (\PDOException $e) {
     echo json_encode(["status" => "error", "message" => "Erro na conexão com o banco de dados: " . $e->getMessage()]);
     exit;
}

function registrar_log($pdo, $acao, $detalhes) {
    $admin_id = $_SESSION['admin_user_id'] ?? null;
    try {
        $stmt = $pdo->prepare("INSERT INTO logs_auditoria (admin_id, acao, detalhes) VALUES (?, ?, ?)");
        $stmt->execute([$admin_id, $acao, $detalhes]);
    } catch (\Exception $e) {
        // Silencioso para não quebrar a aplicação caso ocorra erro
    }
}

// Inicializar token CSRF se a sessão estiver ativa
if (session_status() === PHP_SESSION_ACTIVE) {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

// Funções Auxiliares de CSRF
if (!function_exists('obter_csrf_token')) {
    function obter_csrf_token() {
        return $_SESSION['csrf_token'] ?? '';
    }
}

if (!function_exists('renderizar_csrf_input')) {
    function renderizar_csrf_input() {
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(obter_csrf_token()) . '">';
    }
}

if (!function_exists('validar_csrf_token')) {
    function validar_csrf_token($token) {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('has_permission')) {
    function has_permission($perm) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $role = $_SESSION['admin_role'] ?? '';
        if (empty($role)) {
            $role = 'admin_master';
        }
        if ($role === 'admin_master') {
            return true;
        }
        
        $perms = $_SESSION['admin_permissoes'] ?? [];
        return is_array($perms) && (in_array($perm, $perms) || in_array('all', $perms));
    }
}
