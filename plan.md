# 📋 PLANO DE IMPLEMENTAÇÃO: Sistema de Controle de Chaves por QR Code

**Arquitetura:** PHP 8.1+ | MySQL 8.0 | HTML5/JS (PWA) | Hospedagem na Reitoria
**Objetivo:** Controle simples, rápido e seguro da entrega e devolução de chaves escolares usando tablets/quiosques.

---

## 1. Visão Geral da Arquitetura e Servidor

```
[ Tablet na Escola ] ─── (HTTPS / Rede Reitoria) ───► [ VM na Reitoria ]
 (Leitor QR + JS)                                     ├── Nginx / Apache
                                                      ├── PHP 8.1+ (API)
                                                      └── MySQL 8.0 (BD)
```

- **Hospedagem:** Máquina Virtual Linux (Ubuntu Server 22.04 LTS ou Debian 12) provisionada no datacenter da Reitoria.
- **Protocolo Obrigatório:** HTTPS com SSL/TLS (Let's Encrypt ou Certificado da CA da Instituição) para permitir acesso à câmera do tablet.
- **Interface:** PWA (Progressive Web App) rodando em navegador (Chrome/Edge) em modo quiosque/tela cheia.

---

## 2. Estrutura do Banco de Dados (MySQL)

```sql
CREATE DATABASE IF NOT EXISTS controle_chaves 
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE controle_chaves;

-- Tabela de Perfis de Acesso
CREATE TABLE IF NOT EXISTS perfis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO perfis (id, nome) VALUES 
(1, 'Administrador'), 
(2, 'Professor'), 
(3, 'Servidor/Limpeza'), 
(4, 'Técnico TI');

-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    matricula VARCHAR(50) NOT NULL UNIQUE,
    perfil_id INT NOT NULL,
    qr_code_hash VARCHAR(64) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NULL,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (perfil_id) REFERENCES perfis(id)
) ENGINE=InnoDB;

-- Tabela de Chaves
CREATE TABLE IF NOT EXISTS chaves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_sala VARCHAR(100) NOT NULL,
    codigo_sala VARCHAR(20) NOT NULL UNIQUE,
    qr_code_hash VARCHAR(64) NOT NULL UNIQUE,
    status_disponivel BOOLEAN DEFAULT TRUE,
    descricao TEXT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de Movimentações (Histórico de Retirada/Devolução)
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
```

---

## 3. Especificação da API REST em PHP

Toda a comunicação do tablet com o servidor será feita via requisições AJAX (`fetch`) retornando respostas em formato JSON.

### Endpoints Principais:

1. **`POST /api/processar_scan.php`**
   - **Descrição:** Endpoint unificado inteligente para processar leituras do quiosque.
   - **Entrada:** `{ "qr_code": "HASH_LIDO" }`
   - **Lógica:**
     - Se o hash pertencer a uma **CHAVE INDISPONÍVEL**: Realiza a devolução automaticamente.
     - Se o hash pertencer a um **USUÁRIO**: Armazena o usuário na sessão temporária do tablet e solicita a chave.
     - Se o hash pertencer a uma **CHAVE DISPONÍVEL** e houver um usuário em sessão: Registra a retirada para aquele usuário.

2. **`GET /api/status_chaves.php`**
   - **Descrição:** Retorna a lista de chaves e se estão disponíveis ou com quem estão.

3. **`GET /api/movimentacoes.php`**
   - **Descrição:** Consulta do histórico com filtros de data/usuário/chave (para o Admin).

4. **`POST /api/admin/gerar_qr.php`**
   - **Descrição:** Gera etiquetas de QR Code prontas para impressão em formato PDF/HTML.

---

## 4. Detalhamento do Fluxo de UX / Telas no Tablet

1. **Tela de Standby / Descanso:**
   - Exibe prévia da câmera em loop com área demarcada.
   - Relógio digital grande no topo.
   - Botão para alteração manual de código caso a etiqueta esteja avariada.

2. **Fluxo de Devolução (Sem clique):**
   - Usuário aproxima o QR Code da chave da câmera.
   - Tela pisca verde com card: *"Chave Sala 12 Devolvida com Sucesso!"*.
   - Beep sonoro de confirmação.
   - Retorno automático em 2.5 segundos para a tela de Standby.

3. **Fluxo de Retirada (2 Passos rápidos):**
   - **Passo 1:** Usuário aproxima o QR Code do seu crachá.
   - Tela mostra: *"Olá, Prof. Carlos! Agora aproxime a chave."*
   - **Passo 2:** Usuário aproxima o QR Code da chave.
   - Tela pisca verde: *"Chave Sala 12 Retirada!"*.
   - Retorno automático em 3 segundos.

---

## 5. Cronograma de Implantação (4 Semanas)

| Semana | Etapa | Atividades |
| :---: | :--- | :--- |
| **Semana 1** | Infraestrutura & BD | - Provisionamento da VM na Reitoria<br>- Instalação do Apache/Nginx, PHP 8.1 e MySQL<br>- Instalação do certificado HTTPS<br>- Execução dos scripts SQL de criação do BD |
| **Semana 2** | Backend & API | - Criação das rotas e scripts PHP de conexão e regras de negócio<br>- Testes de performance do endpoint de scan<br>- Criação dos scripts de geração de hash único para QR Codes |
| **Semana 3** | Frontend & Quiosque | - Desenvolvimento do PWA em HTML/JS com `html5-qrcode`<br>- Implementação do visual Quiosque (CSS responsive)<br>- Criação do Painel Administrativo Web para o gestor da escola |
| **Semana 4** | Testes & Lançamento | - Teste de carga e resposta em rede local/Wi-Fi<br>- Instalação do tablet no suporte físico na portaria/sala dos professores<br>- Impressão e colagem das etiquetas nos chaveiros<br>- Treinamento da equipe e entrada em produção |

---

## 6. Checklist para o Setor de TI da Reitoria

- [ ] Criar Máquina Virtual com Ubuntu Server 22.04 LTS (1 vCPU, 2GB RAM, 20GB HD).
- [ ] Liberar porta 80/443 no firewall interno da instituição.
- [ ] Apontar subdomínio (ex: `chaves.escola.edu.br` ou IP estático interno).
- [ ] Configurar Certificado SSL/HTTPS.
- [ ] Criar banco de dados MySQL e usuário com privilégios adequados.
