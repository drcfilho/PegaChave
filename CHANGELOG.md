# 📝 Histórico de Mudanças - PegaChave

Este arquivo registra todo o histórico de desenvolvimento e as melhorias aplicadas no projeto **PegaChave**.

---

## 🚀 Versão Atual (Melhorias e Ajustes Recentes)

### 🐳 1. Portabilidade e Ambientes de Execução
* **Ambiente Docker**: Criação dos arquivos `Dockerfile`, `.dockerignore` e `docker-compose.yml` para rodar o PHP 8.3 (Apache) e o MySQL 8.4 integrados com um único comando.
* **Instalação Automatizada no Docker**: O contêiner de backend executa automaticamente o script de instalação no primeiro carregamento, aguardando a inicialização do MySQL para configurar as tabelas e dados iniciais.
* **Script `iniciar.bat`**: Atalho simples de dois cliques para carregar localmente os servidores PHP e MySQL sem necessidade do Docker.

### 🎨 2. Experiência do Usuário (UX/UI)
* **Feedback de Áudio (Web Audio API)**:
  * **Sucesso (Verde)**: Bip agudo duplo ao concluir retirada/devolução.
  * **Informação (Azul)**: Bip de tom médio na leitura do crachá do usuário.
  * **Erro (Vermelho)**: Bip grave em caso de leitura inválida ou falha na API.
* **Suporte Completo a PWA**: Criação de `manifest.json`, service worker de cache (`service-worker.js`) e ícones de atalho para instalação direta em tablets.
* **Tema Escuro (Dark Mode)**: Botão no cabeçalho e suporte automático à preferência do sistema, sincronizado localmente entre todas as telas.

### 🔒 3. Segurança e Auditoria
* **Proteção contra CSRF**: Geração e validação de tokens de segurança únicos por sessão em todos os formulários administrativos POST (Login, Cadastro de Salas, Cadastro de Usuários e Configurações).
* **Limitação de Acesso (Rate Limiting)**: Máximo de 10 tentativas rápidas de escaneamento a cada 30 segundos no leitor para evitar ataques automatizados.
* **Arquivamento Seguro (Soft Delete)**: O botão de excluir usuário foi alterado para desativar e arquivar o registro. Isso mantém o histórico de logs e movimentações de chaves íntegro no banco de dados.
* **Aba de Auditoria com Senha**: Criação da tela `admin_usuarios_arquivados.php`, onde os administradores acessam os dados de usuários arquivados inserindo a sua senha de login.
* **Sistema de Migrações Automáticas**: O instalador `bin/install.php` agora possui controle de versões para aplicar alterações de estrutura (como novas colunas e tabelas) de forma incremental e sem perda de dados.
* **Validação Estrita de Dados**: Sanitização e validações avançadas (incluindo tipo, e-mail e expressões regulares de segurança) de todas as entradas do painel para cadastros e edições.

### 📊 4. Relatórios e Exportações
* **Exportação de Movimentações (CSV)**: Botão na tela de relatórios gerais (`admin_relatorio.php`) para baixar o histórico atual filtrado em formato `.csv` (com suporte a UTF-8/BOM para Excel).
* **Exportação de Logs de Auditoria (CSV)**: Botão na tela de logs administrativos (`admin_logs.php`) para baixar os registros de auditoria em formato `.csv`.

---

## 📜 Histórico de Commits (Git Log Atualizado)

Abaixo estão listados os commits oficiais do projeto em ordem cronológica reversa, traduzidos e padronizados para **Português (PT-BR)**:

1. **`docs: atualiza README.md com instruções de Docker, iniciar.bat e segurança`**
   * Atualização completa da documentação para novos usuários do projeto.
2. **`feat: adiciona suporte a contêineres Docker e script iniciar.bat`**
   * Configuração de ambiente portátil e instalável de forma isolada.
3. **`feat: implementa instalador de banco de dados, soft delete de usuários e tema escuro`**
   * Refatoração do modelo de dados e melhorias visuais e de UX.
4. **`docs: adiciona o arquivo de documentação inicial README.md`**
   * Criação do arquivo de documentação original.
5. **`docs: atualiza checklist de tarefas de refatoração no task.md`**
   * Controle de fases do desenvolvimento.
6. **`refactor: remove variável de estado currentMode obsoleta no quiosque`**
   * Limpeza de código e tratamento de lógica JS.
7. **`feat: adiciona alertas de chaves atrasadas no dashboard do administrador`**
   * Implementação de painel com alertas de tempo excedido de uso de chaves.
8. **`feat: implementa logs de auditoria no MySQL e tela de relatórios administrativos`**
   * Auditoria de ações tomadas dentro do painel admin.
9. **`feat: adiciona busca e seleção por checkbox no gerador de etiquetas QR`**
   * Facilidade de impressão de grades selecionadas de etiquetas.
10. **`fix: corrige links do menu e variáveis de cor na tela de consulta de chaves`**
    * Correções de layout e navegação do admin.
11. **`feat: adiciona personalização do nome da escola e cores dinâmicas no painel`**
    * Configurações globais e banco de dados adaptável.
12. **`feat: adiciona consulta de disponibilidade, página de regras e gerador de QR Code`**
    * Interfaces auxiliares e geração inicial de hashes de chaveiros.
13. **`init: commit inicial com a estrutura base do PegaChave`**
    * Criação da estrutura base do quiosque e conexões originais do PHP.
