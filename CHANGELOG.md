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
* **Tema Escuro (Dark Mode)**: Botão no cabeçalho e suporte automático à preferência do sistema, sincronizado localmente entre todas as telas. O contraste do Quiosque foi aprimorado no modo escuro definindo cor branca para os títulos de instrução, inputs manuais e detalhes do popup de sucesso.
* **Alertas Integrados Sem Popup**: Remoção total dos alertas nativos de navegador (`alert()`) no Quiosque (`index.php`). Agora, as mensagens de erro ou informações de escaneamento pendente são exibidas diretamente no topo do cartão do quiosque em um banner colorido dinâmico (vermelho para erros, azul para avisos) com auto-fechamento após 5 segundos.
* **Download de QR Code**: Adicionado botão "⬇️ Baixar" em cada cartão de etiqueta no preview do gerador de etiquetas (`admin_gerar_qr.php`), que gera e baixa uma imagem composta (PNG de 300x390 pixels) contendo o QR Code e as informações completas do item (nome da sala ou usuário, código e hash) escritas na própria imagem usando HTML5 Canvas.
* **Câmera Espelhada (Efeito Espelho)**: Ajustado o espelhamento da webcam no Quiosque (`index.php`), fazendo com que a imagem se comporte como um espelho de forma natural (quando você levanta a mão esquerda, ela aparece no lado esquerdo do visor).

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

### 📧 5. Alertas de Atraso por E-mail
* **Script de Varredura Automatizada**: Script `bin/alertar_atrasos.php` que varre o banco localizando retiradas ativas de chaves com mais de 8 horas de duração.
* **Notificação e Simulação**: Envio automático de e-mail ao usuário atrasado e logs de depuração detalhados em `tmp/email_alerts.log`, integrado ao fluxo de logs de auditoria do banco.

### 📈 6. Gráficos Analíticos (Dashboard)
* **Fluxo de Movimentação Semanal**: Gráfico de linha interativo exibindo a quantidade de chaves retiradas nos últimos 7 dias.
* **Top 5 Salas Mais Utilizadas**: Gráfico de barras horizontais indicando quais salas tiveram mais acessos e retiradas no sistema.
* **Integração com Chart.js**: Gráficos totalmente responsivos, com temas de cores personalizados e renderizados no topo do painel administrativo.

### 📅 7. Sistema de Reservas/Agendamentos
* **Tabela de Reservas**: Criação da tabela `reservas` via migração `004_cria_sistema_reservas` no instalador.
* **Painel de Agendamentos & Histórico**: Página `admin_reservas.php` para cadastro de reservas de salas (Chave, Usuário, Data, Horário) com validação de sobreposição de horários, prevenção de conflitos e listagem de reservas passadas para auditoria.
* **Validação no Quiosque**: Verificação integrada no leitor (`api/processar_scan.php`) bloqueando retiradas de salas que estejam reservadas para terceiros no horário atual ou nos próximos 15 minutos.
* **Integração na Consulta Pública**: A tela `consulta.php` agora exibe de forma clara e organizada a lista de reservas ativas de hoje para cada sala, além de oferecer um alternador dinâmico de layout (Modo Cards/Grid e Modo Listagem compacta) com preferência salva localmente.

---

### ⚙️ 8. Limite de Chaves sob Posse
* **Configuração no Panel**: Adicionado input numérico em `admin_config.php` que permite ao administrador definir o limite máximo de chaves que um usuário pode ter em posse simultaneamente (deixando 0 para ilimitadas).
* **Travamento no Quiosque**: A API de escaneamento valida em tempo real se o usuário já atingiu seu limite de chaves e bloqueia novas retiradas caso necessário.

### 📡 9. Funcionamento Offline no Quiosque (Sincronização PWA)
* **Fila Offline no LocalStorage**: Quando a conexão cair ou a API falhar, o Quiosque grava os escaneamentos em uma fila offline local.
* **Feedback de Registro Offline**: Emite feedback sonoro (bip) e exibe um status temporário informando que o registro foi gravado localmente.
* **Sincronização Automática**: Loop de segundo plano que tenta enviar os escaneamentos offline salvos na fila ao servidor de forma automática a cada 10 segundos quando a conexão volta.
* **Indicador de Status Dinâmico**: O indicador de rede exibe "Online" (verde) ou "Offline (X pendentes)" (amarelo) para informar a situação do sistema.

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
