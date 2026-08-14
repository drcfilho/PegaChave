<?php
// bin/install.php

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
            
            // Remover aspas se existirem
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

echo "=== Iniciando Instalador do Banco de Dados ===\n";

$rootPath = dirname(__DIR__);
if (!carregarEnv($rootPath . '/.env')) {
    echo "Aviso: Arquivo .env não encontrado. Buscando variáveis de ambiente...\n";
}

$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';
$dbName = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'controle_chaves';
$user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
$pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';

try {
    // 1. Conectar ao MySQL sem banco de dados para garantir que o banco exista
    echo "Conectando ao MySQL em $host:$port...\n";
    $dsnSemDb = "mysql:host=$host;port=$port;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    // Loop de tentativas de conexão (importante para o Docker aguardar a inicialização do MySQL)
    $maxAttempts = 12;
    $attempt = 1;
    $pdoInit = null;
    while ($attempt <= $maxAttempts) {
        try {
            $pdoInit = new PDO($dsnSemDb, $user, $pass, $options);
            break;
        } catch (PDOException $e) {
            if ($attempt === $maxAttempts) {
                throw $e;
            }
            echo "Banco de dados indisponível no momento. Aguardando inicialização (tentativa $attempt de $maxAttempts)... \n";
            sleep(3);
            $attempt++;
        }
    }
    
    echo "Criando o banco de dados '$dbName' (se não existir)...\n";
    $pdoInit->exec("CREATE DATABASE IF NOT EXISTS `$dbName` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdoInit = null; // fechar conexão

    // 2. Conectar ao banco de dados específico
    $dsnComDb = "mysql:host=$host;dbname=$dbName;port=$port;charset=utf8mb4";
    $pdo = new PDO($dsnComDb, $user, $pass, $options);
    echo "Conectado ao banco de dados '$dbName' com sucesso!\n";

    // 3. Ler e executar o schema.sql
    $schemaPath = $rootPath . '/bin/mysql/schema.sql';
    if (!file_exists($schemaPath)) {
        throw new Exception("Arquivo de esquema SQL não encontrado em: $schemaPath");
    }
    
    echo "Lendo arquivo schema.sql...\n";
    $sql = file_get_contents($schemaPath);
    
    echo "Executando queries de criação de tabelas...\n";
    $pdo->exec($sql);
    echo "Tabelas criadas/verificadas com sucesso!\n";

    // 4. Inserir administrador padrão se a tabela de administradores estiver vazia
    $stmt = $pdo->query("SELECT COUNT(*) FROM administradores");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        echo "Tabela de administradores vazia. Cadastrando usuário administrador padrão...\n";
        $senhaHash = password_hash('admin', PASSWORD_DEFAULT);
        $stmtInsert = $pdo->prepare("INSERT INTO administradores (usuario, senha, nome) VALUES (?, ?, ?)");
        $stmtInsert->execute(['admin', $senhaHash, 'Administrador Geral']);
        echo "Administrador padrão cadastrado: Usuário 'admin' / Senha 'admin'\n";
    } else {
        echo "Tabela de administradores já possui registros. Pulando cadastro padrão.\n";
    }

    echo "=== Instalação Concluída com Sucesso! ===\n";

} catch (Exception $e) {
    echo "ERRO DURANTE A INSTALAÇÃO: " . $e->getMessage() . "\n";
    exit(1);
}
