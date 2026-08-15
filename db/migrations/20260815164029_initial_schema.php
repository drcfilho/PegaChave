<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InitialSchema extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
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
    }

    public function down(): void
    {
        $this->execute("
            DROP TABLE IF EXISTS restricoes_acesso;
            DROP TABLE IF EXISTS reservas;
            DROP TABLE IF EXISTS logs_auditoria;
            DROP TABLE IF EXISTS configuracoes;
            DROP TABLE IF EXISTS administradores;
            DROP TABLE IF EXISTS movimentacoes;
            DROP TABLE IF EXISTS chaves;
            DROP TABLE IF EXISTS usuarios;
            DROP TABLE IF EXISTS perfis;
        ");
    }
}
