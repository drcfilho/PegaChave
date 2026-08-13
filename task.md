# 📋 Controle de Tarefas (task.md)

Acompanhamento do desenvolvimento e implantação do **Sistema de Controle de Chaves por QR Code**.

---

## 🛠️ Checklist de Infraestrutura (TI Reitoria)

- [ ] Provisionar Máquina Virtual Linux (Ubuntu Server 22.04 LTS ou Debian 12)
  - *Especificações mínimas:* 1 vCPU, 2GB RAM, 20GB HD
- [ ] Liberar as portas HTTP (`80`) e HTTPS (`443`) no firewall interno da instituição
- [ ] Configurar IP estático ou apontar subdomínio (ex: `chaves.escola.edu.br`)
- [ ] Configurar Certificado SSL/HTTPS (Let's Encrypt ou certificado da CA institucional)
- [ ] Instalar Apache/Nginx, PHP 8.1+ e MySQL 8.0
- [ ] Criar o banco de dados MySQL `controle_chaves` e configurar usuário de acesso com privilégios específicos

---

## 📅 Fases do Desenvolvimento

### **Fase 1: Infraestrutura & BD**
- [x] Executar script DDL do banco de dados ([bd.md](file:///C:/Users/DanielF/Documents/PegaChave/bd.md))
- [x] Configurar ambiente local de desenvolvimento (PHP/MySQL)
- [x] Validar a conexão da aplicação com o banco de dados

### **Fase 2: Backend & API**
- [x] Criar estrutura base do projeto e conexão PDO
- [x] Implementar endpoint inteligente **`POST /api/processar_scan.php`**
  - [x] Lógica para devolução de chaves (chave indisponível -> atualiza movimentação)
  - [x] Lógica para identificação de usuário (crachá -> armazena em sessão)
  - [x] Lógica para retirada de chaves (sessão ativa + chave disponível -> registra nova movimentação)
- [x] Implementar endpoint **`GET /api/status_chaves.php`** (retorna lista de chaves e status)
- [x] Implementar endpoint **`GET /api/movimentacoes.php`** (histórico com filtros para admin)
- [x] Implementar endpoint/módulo **`POST /api/admin/gerar_qr.php`** (gerador de hash/PDF para impressão)

### **Fase 3: Frontend & Quiosque (Conforme Mockups)**
- [x] Desenvolver o **Painel Principal do Quiosque** (Mockup 1)
  - [x] Cabeçalho com o nome "Escola Lumiar", relógio digital ativo e indicador online
  - [x] Área de escaneamento circular com ícone de sinal, alvo de centralização e câmera integrada
  - [x] Botões rápidos para "Retirar Chave" (Verde) e "Devolver Chave" (Azul)
  - [x] Entrada de código de digitação manual para redundância e botão "Ajuda"
- [x] Desenvolver a **Tela de Confirmação de Sucesso/Operação** (Mockup 2)
  - [x] Card centralizado com ícone animado de marcação de verificação (Checkmark)
  - [x] Detalhes do Usuário (Nome/ID), da Chave (Nome/Sala) e Data/Hora formatados
  - [x] Retorno automático programado para a tela principal (2.5s / 3s)
- [x] Desenvolver o **Painel do Administrador (Dashboard)** (Mockup 3)
  - [x] Separar o painel administrativo em um menu lateral (Sidebar) integrado
  - [x] Criar página individual **`admin.php`** (Métricas Rápidas & Chaves em Uso)
  - [x] Criar página individual **`admin_chaves.php`** (Gerenciamento e CRUD de Salas)
  - [x] Criar página individual **`admin_usuarios.php`** (Gerenciamento e CRUD de Pessoas)
  - [x] Criar página individual **`admin_relatorio.php`** (Relatório Geral de Histórico com Filtros e Impressão)

### **Fase 4: Testes, Simulações & Lançamento**
- [x] Realizar testes integrados e simulações do fluxo completo (Mockup 1 ➔ Mockup 2 ➔ Atualização do Mockup 3)
- [x] Validar persistência da sessão e comunicação com banco de dados MySQL
- [ ] Instalar suporte físico e tablet na portaria da escola
- [ ] Gerar, imprimir e fixar as etiquetas QR Code nos chaveiros e crachás
- [ ] Treinar a equipe operacional e iniciar operações

### **Fase 5: Melhorias de Segurança & Funcionalidades**
- [x] Implementar autenticação segura para o Painel Admin (login, sessão e hash de senha)
- [x] Adicionar filtros e seleção individual na geração de etiquetas QR Code
- [x] Criar log de auditoria administrativa no banco de dados
- [x] Criar sistema de notificações de chaves atrasadas (Alerta em tela ou integração)

### **Fase 6: Refatoração & Limpeza**
- [x] Identificar e remover variáveis e estados órfãos (como `currentMode` no JS)
- [x] Remover rotas/códigos mortos no backend (antigo tratamento de arrays de seleção em PHP)
- [x] Limpar códigos e comentários obsoletos ou de rascunhos antigos
