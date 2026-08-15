-- Banco de Dados PegaChave
-- Gerado em: 2026-08-15 16:08:46

SET FOREIGN_KEY_CHECKS=0;

-- Estrutura da tabela `administradores`
DROP TABLE IF EXISTS `administradores`;
CREATE TABLE `administradores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `role` enum('admin_master','operador') DEFAULT 'admin_master',
  `permissoes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissoes`)),
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados da tabela `administradores`
INSERT INTO `administradores` (`id`, `usuario`, `senha`, `nome`, `role`, `permissoes`, `criado_em`) VALUES ('1', 'admin', '$2y$12$I..VCor0Z.6wNU70zDrZceJkqX6Idg4OzdEKh5A0yuc/6.a9rjpQu', 'Administrador Geral', 'admin_master', NULL, '2026-08-14 20:22:31');
INSERT INTO `administradores` (`id`, `usuario`, `senha`, `nome`, `role`, `permissoes`, `criado_em`) VALUES ('2', 'lorena', '$2y$10$wJHuCDzHsmvwDCtbP7uZoOfZ4KXodWV4EFDGgFi7XF6md8oUOo/Ze', 'Lorena', 'operador', '[\"gerenciar_chaves\",\"gerenciar_usuarios\"]', '2026-08-14 21:00:23');
INSERT INTO `administradores` (`id`, `usuario`, `senha`, `nome`, `role`, `permissoes`, `criado_em`) VALUES ('4', 'daniel', '$2y$10$vWLLrA3zKFfHITojBBvn3.JdNMLHjHQUQ7Kp.iy/kKtu9VFvHG.wS', 'Daniel', 'admin_master', '[\"gerenciar_chaves\",\"gerenciar_usuarios\",\"ver_relatorios\",\"gerenciar_configuracoes\"]', '2026-08-14 21:03:24');


-- Estrutura da tabela `chaves`
DROP TABLE IF EXISTS `chaves`;
CREATE TABLE `chaves` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome_sala` varchar(100) NOT NULL,
  `bloco` varchar(50) DEFAULT NULL,
  `andar` varchar(50) DEFAULT NULL,
  `matriculas_permitidas` text DEFAULT NULL,
  `codigo_sala` varchar(20) NOT NULL,
  `qr_code_hash` varchar(64) NOT NULL,
  `status_disponivel` tinyint(1) DEFAULT 1,
  `descricao` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_sala` (`codigo_sala`),
  UNIQUE KEY `qr_code_hash` (`qr_code_hash`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados da tabela `chaves`
INSERT INTO `chaves` (`id`, `nome_sala`, `bloco`, `andar`, `matriculas_permitidas`, `codigo_sala`, `qr_code_hash`, `status_disponivel`, `descricao`, `criado_em`) VALUES ('1', 'CPD', 'ADMIN', 'TERREO', '1841404', '000', 'chaves_000', '0', 'Somente a TI pode acessar a sala.', '2026-08-14 23:19:20');


-- Estrutura da tabela `configuracoes`
DROP TABLE IF EXISTS `configuracoes`;
CREATE TABLE `configuracoes` (
  `chave` varchar(50) NOT NULL,
  `valor` varchar(255) NOT NULL,
  PRIMARY KEY (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados da tabela `configuracoes`
INSERT INTO `configuracoes` (`chave`, `valor`) VALUES ('cor_primaria', '#2f9e41');
INSERT INTO `configuracoes` (`chave`, `valor`) VALUES ('cor_secundaria', '#0f172a');
INSERT INTO `configuracoes` (`chave`, `valor`) VALUES ('limite_chaves', '0');
INSERT INTO `configuracoes` (`chave`, `valor`) VALUES ('nome_escola', 'IFCE Acaraú');


-- Estrutura da tabela `logs_auditoria`
DROP TABLE IF EXISTS `logs_auditoria`;
CREATE TABLE `logs_auditoria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `acao` varchar(255) NOT NULL,
  `detalhes` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `logs_auditoria_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `administradores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados da tabela `logs_auditoria`
INSERT INTO `logs_auditoria` (`id`, `admin_id`, `acao`, `detalhes`, `criado_em`) VALUES ('1', '1', 'Acesso Autorizado', 'Acessou a aba de usuários arquivados.', '2026-08-14 20:23:56');
INSERT INTO `logs_auditoria` (`id`, `admin_id`, `acao`, `detalhes`, `criado_em`) VALUES ('2', '1', 'Alteração de Configuração', 'Nome da Escola: \'IFCE Acaraú\', Cor Primária: \'#0284c7\', Cor Secundária: \'#0f172a\', Limite de Chaves: 0.', '2026-08-14 20:55:49');
INSERT INTO `logs_auditoria` (`id`, `admin_id`, `acao`, `detalhes`, `criado_em`) VALUES ('3', '1', 'Cadastro de Operador', 'Operador daniel cadastrado.', '2026-08-14 21:03:24');
INSERT INTO `logs_auditoria` (`id`, `admin_id`, `acao`, `detalhes`, `criado_em`) VALUES ('4', '4', 'Configuração de Restrições', 'Matriz de restrições de acesso atualizada. Total de bloqueios: 0.', '2026-08-14 23:17:38');
INSERT INTO `logs_auditoria` (`id`, `admin_id`, `acao`, `detalhes`, `criado_em`) VALUES ('5', '4', 'Cadastro de Chave', 'Chave \'CPD\' (000) cadastrada. Bloco: \'ADMIN\', Andar: \'TERREO\'. Restrita para: \'1841404\'. Hash automático: chaves_000', '2026-08-14 23:19:20');
INSERT INTO `logs_auditoria` (`id`, `admin_id`, `acao`, `detalhes`, `criado_em`) VALUES ('6', '4', 'Configuração de Restrições', 'Matriz de restrições de acesso atualizada. Total de bloqueios: 2.', '2026-08-14 23:19:55');
INSERT INTO `logs_auditoria` (`id`, `admin_id`, `acao`, `detalhes`, `criado_em`) VALUES ('7', '4', 'Configuração de Restrições', 'Matriz de restrições de acesso atualizada. Total de bloqueios: 2.', '2026-08-14 23:20:37');
INSERT INTO `logs_auditoria` (`id`, `admin_id`, `acao`, `detalhes`, `criado_em`) VALUES ('8', '4', 'Cadastro de Usuário', 'Usuário \'Daniel Filho\' (Matrícula: 1841404) cadastrado. Hash automático: user_1841404', '2026-08-14 23:22:13');


-- Estrutura da tabela `movimentacoes`
DROP TABLE IF EXISTS `movimentacoes`;
CREATE TABLE `movimentacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `chave_id` int(11) NOT NULL,
  `data_retirada` datetime DEFAULT current_timestamp(),
  `data_devolucao` datetime DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_chave_devolucao` (`chave_id`,`data_devolucao`),
  KEY `idx_usuario` (`usuario_id`),
  CONSTRAINT `movimentacoes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `movimentacoes_ibfk_2` FOREIGN KEY (`chave_id`) REFERENCES `chaves` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados da tabela `movimentacoes`
INSERT INTO `movimentacoes` (`id`, `usuario_id`, `chave_id`, `data_retirada`, `data_devolucao`, `observacao`) VALUES ('1', '1', '1', '2026-08-15 10:27:34', '2026-08-15 10:27:46', 'Devolvida manualmente por: Daniel');
INSERT INTO `movimentacoes` (`id`, `usuario_id`, `chave_id`, `data_retirada`, `data_devolucao`, `observacao`) VALUES ('2', '1', '1', '2026-08-15 10:27:59', NULL, NULL);


-- Estrutura da tabela `perfis`
DROP TABLE IF EXISTS `perfis`;
CREATE TABLE `perfis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados da tabela `perfis`
INSERT INTO `perfis` (`id`, `nome`) VALUES ('1', 'Administrador');
INSERT INTO `perfis` (`id`, `nome`) VALUES ('8', 'Aluno');
INSERT INTO `perfis` (`id`, `nome`) VALUES ('3', 'Docente');
INSERT INTO `perfis` (`id`, `nome`) VALUES ('2', 'Estagiário');
INSERT INTO `perfis` (`id`, `nome`) VALUES ('7', 'Externo');
INSERT INTO `perfis` (`id`, `nome`) VALUES ('5', 'Limpeza');
INSERT INTO `perfis` (`id`, `nome`) VALUES ('6', 'Segurança');
INSERT INTO `perfis` (`id`, `nome`) VALUES ('4', 'TAE');


-- Estrutura da tabela `reservas`
DROP TABLE IF EXISTS `reservas`;
CREATE TABLE `reservas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chave_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `data_reserva` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `chave_id` (`chave_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`chave_id`) REFERENCES `chaves` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados da tabela `reservas`


-- Estrutura da tabela `restricoes_acesso`
DROP TABLE IF EXISTS `restricoes_acesso`;
CREATE TABLE `restricoes_acesso` (
  `perfil_id` int(11) NOT NULL,
  `chave_id` int(11) NOT NULL,
  PRIMARY KEY (`perfil_id`,`chave_id`),
  KEY `chave_id` (`chave_id`),
  CONSTRAINT `restricoes_acesso_ibfk_1` FOREIGN KEY (`perfil_id`) REFERENCES `perfis` (`id`) ON DELETE CASCADE,
  CONSTRAINT `restricoes_acesso_ibfk_2` FOREIGN KEY (`chave_id`) REFERENCES `chaves` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados da tabela `restricoes_acesso`
INSERT INTO `restricoes_acesso` (`perfil_id`, `chave_id`) VALUES ('7', '1');
INSERT INTO `restricoes_acesso` (`perfil_id`, `chave_id`) VALUES ('8', '1');


-- Estrutura da tabela `usuarios`
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `matricula` varchar(50) NOT NULL,
  `perfil_id` int(11) NOT NULL,
  `qr_code_hash` varchar(64) NOT NULL,
  `senha_hash` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `excluido` tinyint(1) DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricula` (`matricula`),
  UNIQUE KEY `qr_code_hash` (`qr_code_hash`),
  UNIQUE KEY `email` (`email`),
  KEY `perfil_id` (`perfil_id`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`perfil_id`) REFERENCES `perfis` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados da tabela `usuarios`
INSERT INTO `usuarios` (`id`, `nome`, `email`, `matricula`, `perfil_id`, `qr_code_hash`, `senha_hash`, `ativo`, `excluido`, `criado_em`) VALUES ('1', 'Daniel Filho', 'danielfilho@ifce.edu.br', '1841404', '4', 'user_1841404', NULL, '1', '0', '2026-08-14 23:22:13');


SET FOREIGN_KEY_CHECKS=1;
