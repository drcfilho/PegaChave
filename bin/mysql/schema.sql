SET NAMES utf8mb4;
CREATE DATABASE IF NOT EXISTS controle_chaves 
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE controle_chaves;

-- 1. Tabela de Perfis de Acesso
CREATE TABLE IF NOT EXISTS perfis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Carga inicial de perfis
INSERT INTO perfis (id, nome) VALUES 
(1, 'Administrador'), 
(2, 'Estagiário'), 
(3, 'Docente'), 
(4, 'TAE'),
(5, 'Limpeza'),
(6, 'Segurança'),
(7, 'Externo'),
(8, 'Aluno')
ON DUPLICATE KEY UPDATE nome=VALUES(nome);

-- 2. Tabela de Administradores
CREATE TABLE IF NOT EXISTS administradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Tabela de Configurações
CREATE TABLE IF NOT EXISTS configuracoes (
    chave VARCHAR(50) PRIMARY KEY,
    valor VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- 4. Tabela de Usuários (Crachás / Portadores)
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

-- 5. Tabela de Chaves (Salas / Ambientes)
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
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 6. Tabela de Movimentações (Histórico de Empréstimos)
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

-- 7. Tabela de Logs de Auditoria
CREATE TABLE IF NOT EXISTS logs_auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NULL,
    acao VARCHAR(255) NOT NULL,
    detalhes TEXT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES administradores(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 8. Tabela de Reservas / Agendamentos
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

-- 9. Tabela de Restrições de Acesso por Perfil a Chaves
CREATE TABLE IF NOT EXISTS restricoes_acesso (
    perfil_id INT NOT NULL,
    chave_id INT NOT NULL,
    PRIMARY KEY (perfil_id, chave_id),
    FOREIGN KEY (perfil_id) REFERENCES perfis(id) ON DELETE CASCADE,
    FOREIGN KEY (chave_id) REFERENCES chaves(id) ON DELETE CASCADE
) ENGINE=InnoDB;
