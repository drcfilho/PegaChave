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
    echo "Conectando ao MySQL em $host:$port...\n";
    $dsnSemDb = "mysql:host=$host;port=$port;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
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
    $pdoInit = null;

    $dsnComDb = "mysql:host=$host;dbname=$dbName;port=$port;charset=utf8mb4";
    $pdo = new PDO($dsnComDb, $user, $pass, $options);
    echo "Conectado ao banco de dados '$dbName' com sucesso!\n";

    echo "Criando tabelas...\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS perfis (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(50) NOT NULL UNIQUE
        ) ENGINE=InnoDB;
        
        INSERT IGNORE INTO perfis (id, nome) VALUES 
        (1, 'Administrador'), (2, 'Estagiário'), (3, 'Docente'), (4, 'TAE'),
        (5, 'Limpeza'), (6, 'Segurança'), (7, 'Externo'), (8, 'Aluno');
        
        CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE,
            matricula VARCHAR(50) NOT NULL UNIQUE,
            perfil_id INT NOT NULL,
            qr_code_hash VARCHAR(64) NOT NULL UNIQUE,
            senha_hash VARCHAR(255) NULL,
            ativo BOOLEAN DEFAULT TRUE,
            excluido BOOLEAN DEFAULT FALSE,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (perfil_id) REFERENCES perfis(id)
        ) ENGINE=InnoDB;
        
        CREATE TABLE IF NOT EXISTS chaves (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome_sala VARCHAR(100) NOT NULL,
            bloco VARCHAR(50) NULL,
            andar VARCHAR(50) NULL,
            matriculas_permitidas TEXT NULL,
            codigo_sala VARCHAR(20) NOT NULL UNIQUE,
            qr_code_hash VARCHAR(64) NOT NULL UNIQUE,
            status_disponivel BOOLEAN DEFAULT TRUE,
            descricao TEXT NULL,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            deleted_at DATETIME NULL
        ) ENGINE=InnoDB;
        
        CREATE TABLE IF NOT EXISTS movimentacoes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            chave_id INT NOT NULL,
            data_retirada DATETIME DEFAULT CURRENT_TIMESTAMP,
            data_devolucao DATETIME NULL,
            observacao TEXT NULL,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
            FOREIGN KEY (chave_id) REFERENCES chaves(id),
            INDEX idx_chave_devolucao (chave_id, data_devolucao),
            INDEX idx_usuario (usuario_id)
        ) ENGINE=InnoDB;

        CREATE TABLE IF NOT EXISTS administradores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario VARCHAR(50) NOT NULL UNIQUE,
            senha VARCHAR(255) NOT NULL,
            nome VARCHAR(100) NOT NULL,
            role ENUM('admin_master', 'operador') DEFAULT 'admin_master',
            permissoes JSON NULL,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;
        
        CREATE TABLE IF NOT EXISTS configuracoes (
            chave VARCHAR(50) PRIMARY KEY,
            valor VARCHAR(255) NOT NULL
        ) ENGINE=InnoDB;
        
        CREATE TABLE IF NOT EXISTS logs_auditoria (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NULL,
            acao VARCHAR(255) NOT NULL,
            detalhes TEXT NULL,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES administradores(id) ON DELETE SET NULL
        ) ENGINE=InnoDB;

        CREATE TABLE IF NOT EXISTS reservas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            chave_id INT NOT NULL,
            usuario_id INT NOT NULL,
            data_reserva DATE NOT NULL,
            hora_inicio TIME NOT NULL,
            hora_fim TIME NOT NULL,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (chave_id) REFERENCES chaves(id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;

        CREATE TABLE IF NOT EXISTS restricoes_acesso (
            perfil_id INT NOT NULL,
            chave_id INT NOT NULL,
            PRIMARY KEY (perfil_id, chave_id),
            FOREIGN KEY (perfil_id) REFERENCES perfis(id) ON DELETE CASCADE,
            FOREIGN KEY (chave_id) REFERENCES chaves(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ");
    
    echo "Tabelas criadas com sucesso!\n";

    $stmt = $pdo->query("SELECT COUNT(*) FROM administradores");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        echo "Tabela de administradores vazia. Cadastrando usuário administrador padrão...\n";
        $senhaHash = password_hash('admin', PASSWORD_DEFAULT);
        $stmtInsert = $pdo->prepare("INSERT INTO administradores (usuario, senha, nome, role, permissoes) VALUES (?, ?, ?, ?, ?)");
        $stmtInsert->execute(['admin', $senhaHash, 'Administrador Geral', 'admin_master', '["all"]']);
        echo "Administrador padrão cadastrado: Usuário 'admin' / Senha 'admin'\n";
    } else {
        echo "Tabela de administradores já possui registros. Pulando cadastro padrão.\n";
    }

    echo "=== Instalação Concluída com Sucesso! ===\n";

} catch (Exception $e) {
    echo "ERRO DURANTE A INSTALAÇÃO: " . $e->getMessage() . "\n";
    exit(1);
}
