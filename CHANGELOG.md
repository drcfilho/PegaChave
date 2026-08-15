# 📝 Histórico de Mudanças - PegaChave

Este arquivo registra todo o histórico de desenvolvimento e as melhorias aplicadas no projeto **PegaChave**.

---

## 🚀 Versão Atual (Melhorias e Ajustes Recentes)

### 💉 Injeção de Dependência e Remoção de Variáveis Globais (Task 1)
* **Remoção de Globais**: Variáveis globais como `$pdo` e `$nome_escola` foram removidas de todos os Controllers.
* **Injeção via Construtor**: O `Router.php` foi modificado para gerenciar instâncias de dependência (`setDependencies`) e repassá-las aos construtores dos Controllers de forma limpa.

### 🏗️ 1. Refatoração Arquitetural (MVC, Repositories e Roteador)
* **Padrão MVC**: Os arquivos de frontend e backend, antes misturados na raiz, foram completamente divididos na nova estrutura `src/Controllers`, `src/Models` e `src/Views`, seguindo os mais altos padrões de arquitetura de software de forma limpa.
* **Padrão Repository**: A comunicação com o banco de dados agora está totalmente blindada. Os Controllers não escrevem mais queries SQL (como `SELECT` e `INSERT`) diretamente; toda a camada de banco de dados foi movida para as classes em `src/Models/` (ex: `ChaveRepository`, `UsuarioRepository`), facilitando testes e futuras trocas de banco.
* **Autoloading PSR-4**: Configuração do `composer.json` para carregar classes automaticamente na pasta `src/`, removendo a necessidade manual de importar arquivos por todo o projeto.
* **Roteamento Centralizado e URLs Limpas**: Exclusão de mais de 12 arquivos físicos soltos na raiz (como `admin_chaves.php`). Todas as requisições agora batem no `index.php` (Front Controller) graças à edição do `.htaccess`. As URLs do sistema ficaram muito mais modernas (ex: `/admin/chaves` no lugar de `/admin_chaves.php`).
* **Componentização com Alpine.js**: A View principal do Quiosque (`quiosque.php`) foi completamente refatorada utilizando o *framework* Alpine.js. O arquivo foi reduzido de quase 900 linhas para apenas cerca de 130 linhas declarativas. Toda a lógica de interatividade, câmera, alertas e offline foi separada para o script dedicado `assets/js/quiosque.js` e o layout movido para `assets/css/quiosque.css`.

### 🐳 2. Portabilidade e Ambientes de Execução
* **Ambiente Docker**: Criação dos arquivos `Dockerfile`, `.dockerignore` e `docker-compose.yml` para rodar o PHP 8.3 (Apache) e o MySQL 8.4 integrados com um único comando.
* **Instalação Automatizada no Docker**: O contêiner de backend executa automaticamente o script de instalação no primeiro carregamento, aguardando a inicialização do MySQL para configurar as tabelas e dados iniciais.
* **Script `iniciar.bat`**: Atalho simples de dois cliques para carregar localmente os servidores PHP e MySQL sem necessidade do Docker.

### 🎨 2. Experiência do Usuário (UX/UI)
* **Feedback de Áudio (Web Audio API)**:
  * **Sucesso (Verde)**: Bip agudo duplo ao concluir retirada/devolução.
  * **Informação (Azul)**: Bip de tom médio na leitura do crachá do usuário.
  * **Erro (Vermelho)**: Bip grave em caso de leitura inválida ou falha na API.
* **Suporte Completo a PWA**: Criação de `manifest.json`, service worker de cache (`service-worker.js`) e ícones de atalho para instalação direta em tablets.
* **Dashboard com Atualização em Tempo Real**: O painel administrativo geral (`admin.php`) foi atualizado para efetuar pooling de dados em tempo real (a cada 2 segundos) via AJAX consumindo o novo endpoint `/api/dashboard_data.php`. Toda e qualquer alteração de status de chaves (retirada ou devolução no Quiosque) é refletida instantaneamente na tela: atualizando os cards de estatísticas, gerando/removendo o banner de alerta de chaves atrasadas, redesenhando a tabela de "Chaves em Uso" e atualizando dinamicamente os gráficos do Chart.js.
* **Tema Escuro (Dark Mode)**: Botão no cabeçalho e suporte automático à preferência do sistema, sincronizado localmente entre todas as telas. O contraste do Quiosque foi aprimorado no modo escuro definindo cor branca para os títulos de instrução, inputs manuais e detalhes do popup de sucesso.
* **Alertas Integrados Sem Popup**: Remoção total dos alertas nativos de navegador (`alert()`) no Quiosque (`index.php`). Agora, as mensagens de erro ou informações de escaneamento pendente são exibidas diretamente no topo do cartão do quiosque em um banner colorido dinâmico (vermelho para erros, azul para avisos) com auto-fechamento após 5 segundos.
* **Entrada Manual Otimizada & Múltiplas Chaves por Vírgula**: O campo de digitação manual do Quiosque foi aprimorado. Agora ele aceita a **Matrícula** física do usuário para identificação e os **Códigos Físicos** das salas. Além disso, é possível digitar múltiplos códigos de chaves separados por vírgula (ex: `SALA-12, SALA-13, SALA-15`) para realizar a retirada ou devolução de várias chaves de uma única vez.
* **Download de QR Code**: Adicionado botão "⬇️ Baixar" em cada cartão de etiqueta no preview do gerador de etiquetas (`admin_gerar_qr.php`), que gera e baixa uma imagem composta (PNG de 300x410 pixels) contendo o QR Code e as informações completas do item (nome da sala ou usuário, código, localização e hash) escritas na própria imagem usando HTML5 Canvas.
* **Campos Bloco e Andar nas Chaves**: Adicionados os campos `bloco` (bloco físico) e `andar` (nível/andar) no cadastro de salas/chaves. A localização é preenchida no painel administrativo, exibida na listagem (`admin_chaves.php`), integrada à consulta de disponibilidade pública (`consulta.php`) com suporte a busca rápida de localização, e incluída nas etiquetas do gerador de etiquetas.
* **Ordenação Interativa de Tabelas**: Adicionado suporte a ordenação interativa A-Z nas tabelas de chaves (`admin_chaves.php`) e de usuários (`admin_usuarios.php`). Os cabeçalhos das tabelas (Nome, Matrícula, Bloco, Andar, Função, etc.) são agora clicáveis e ordenam as linhas instantaneamente de forma ascendente ou descendente.
* **Pesquisa e Filtragem em Tempo Real**: Adicionado um campo de busca dinâmica na barra superior das telas de gerenciamento de chaves e de usuários. Conforme o operador digita, a listagem é refinada instantaneamente no navegador para exibir apenas os registros que possuem correspondência parcial de texto em qualquer coluna.
* **Chaves com Acesso Restrito por Matrícula**: Adicionada a coluna `matriculas_permitidas` na tabela de chaves. Agora, os administradores podem definir uma lista de matrículas autorizadas (separadas por vírgula) no cadastro de chaves. Ao passar o crachá e a chave correspondente no Quiosque, a API valida em tempo real se a matrícula do usuário consta na lista de permitidas. Se não constar, a retirada é bloqueada de forma imediata emitindo alerta correspondente.
* **Câmera Espelhada (Efeito Espelho)**: Ajustado o espelhamento da webcam no Quiosque (`index.php`), fazendo com que a imagem se comporte como um espelho de forma natural (quando você levanta a mão esquerda, ela aparece no lado esquerdo do visor).

### 🔒 3. Segurança e Auditoria
* **Proteção contra CSRF**: Geração e validação de tokens de segurança únicos por sessão em todos os formulários administrativos POST (Login, Cadastro de Salas, Cadastro de Usuários e Configurações).
* **Limitação de Acesso (Rate Limiting)**: Máximo de 10 tentativas rápidas de escaneamento a cada 30 segundos no leitor para evitar ataques automatizados.
* **Arquivamento Seguro (Soft Delete)**: O botão de excluir usuário foi alterado para desativar e arquivar o registro. Isso mantém o histórico de logs e movimentações de chaves íntegro no banco de dados.
* **Aba de Auditoria com Senha**: Criação da tela `admin_usuarios_arquivados.php`, onde os administradores acessam os dados de usuários arquivados inserindo a sua senha de login.
* **Sistema de Migrações Automáticas**: O instalador `bin/install.php` agora possui controle de versões para aplicar alterações de estrutura (como novas colunas e tabelas) de forma incremental e sem perda de dados.
* **Validação Estrita de Dados & Geração de Hashes Automática**: Sanitização e validações avançadas (incluindo tipo, e-mail e expressões regulares de segurança) de todas as entradas do painel para cadastros e edições. Os campos de QR Code Hash de usuários e chaves foram alterados para "somente leitura" e são gerados automaticamente:
  * **Usuários**: `user_` + matrícula (ex: `user_101`).
  * **Chaves**: `chaves_` + código da sala (ex: `chaves_CPD`).
  * A geração ocorre dinamicamente no frontend (enquanto o administrador digita) e é forçada e validada no backend (PHP) por segurança.

### 📊 4. Relatórios e Exportações
* **Registro de Operador em Devoluções Manuais**: Ao efetuar uma devolução manual pelo painel administrativo, o sistema captura e armazena na coluna `observacao` da movimentação qual usuário administrativo (ex: Administrador, Recepcionista) realizou a ação. Essa informação é agora exibida na tabela do relatório de movimentações (`admin_relatorio.php`) e incluída na exportação CSV.
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

### 🔒 10. Matriz de Restrições de Acesso
* **Tabela de Restrições**: Criação da tabela `restricoes_acesso` no banco de dados via migração `005_cria_restricoes_acesso`.
* **Painel Administrativo (`admin_restricoes.php`)**: Criação da interface com matriz interativa de Perfis vs Salas, permitindo bloquear o acesso de determinados perfis de usuários a salas específicas (ex: impedir que Alunos ou Estagiários peguem a chave do CPD).
* **Validação no Quiosque**: Verificação integrada no processo de checkout da chave, bloqueando novas retiradas com mensagens claras de restrição caso o perfil do portador esteja bloqueado para a chave correspondente.

### 🛡️ 11. RBAC (Sistema de Controle de Acesso e Gestão de Operadores)
* **Gestão Dinâmica de Permissões (JSON)**: Implementação de cadastro de novos "Operadores" no sistema com permissões granulares gerenciadas via Checkboxes pelo Administrador Master. As permissões são salvas na coluna `permissoes` da tabela `administradores`.
* **Proteção Avançada (`AdminBaseController`)**: Interceptação em tempo de execução validando o perfil do operador da sessão antes de carregar qualquer página administrativa ou executar qualquer ação (CRUD).
* **Sidebar Componentizado e Dinâmico**: Extração do menu lateral estático para um componente único (`partials/sidebar.php`) que processa ocultação automática das opções baseadas nas permissões ativas. Fallback implementado para sessões legadas.

---

### ⚙️ 8. Limite de Chaves sob Posse
* **Configuração no Panel**: Adicionado input numérico em `admin_config.php` que permite ao administrador definir o limite máximo de chaves que um usuário pode ter em posse simultaneamente (deixando 0 para ilimitadas).
* **Travamento no Quiosque**: A API de escaneamento valida em tempo real se o usuário já atingiu seu limite de chaves e bloqueia novas retiradas caso necessário.

### 📡 10. Funcionamento Offline e Progressive Web App (PWA) Avançado
* **Migração de Fila Offline para IndexedDB**: Substituição do antigo `localStorage` frágil pelo robusto banco de dados do navegador `IndexedDB`. Isso garante alta performance, ausência de limites de armazenamento para leituras de chaves em fallback (semanas de trabalho offline sem perda) e segurança contra deleção acidental de cache da página.
* **Sincronização Invisível em Segundo Plano (Background Sync)**: Habilitação da API nativa do `Service Worker` para gerenciar a fila offline. Mesmo que o usuário feche a aba do navegador do Quiosque após a internet cair, o navegador aguardará o sinal de rede retornar e enviará todas as leituras silenciosamente ao servidor em *background*.
* **Feedback de Rede Aprimorado no Alpine.js**: As interações com o IndexedDB foram acopladas ao sistema reativo. O status da rede (Online/Offline) e o número de sincronizações pendentes são validados e atualizados em tempo real, sem refresh de página.

---

## 📜 Histórico de Commits (Git Log Atualizado)

Abaixo estão listados os commits oficiais do projeto em ordem cronológica reversa, traduzidos e padronizados para **Português (PT-BR)**:

1. **`fix: substitui sidebar fixo pelo componente dinamico na view gerar_qr`**
   * Resolve falha de renderização do menu na tela de QR codes.
2. **`fix: restaura ordem do menu e adiciona fallback de sessao para usuarios antigos`**
   * Restaura visual exato dos menus e arruma desaparecimento para quem estava logado antes do update.
3. **`refactor: extrai sidebar estatica para componente dinamico com RBAC e inclui em todas as telas`**
   * Limpeza de redundância. O menu agora é renderizado inteligentemente usando um único arquivo.
4. **`fix: corrige acesso indevido a propriedade protegida do pdo no AdminOperadoresController`**
   * Resolve crash Fatal Error ao tentar auditar log no momento da criação.
5. **`fix: corrige erro de sintaxe e codigo duplicado no AdminChavesController`**
   * Restaura funcionamento da página de chaves que estava quebrada.
6. **`feat: sistema de controle de acesso (RBAC) e gestao de operadores`**
   * Adiciona CRUD de usuários administrativos com caixas de seleção de permissão. 
7. **`docs: atualiza README.md com instruções de Docker, iniciar.bat e segurança`**
   * Atualização completa da documentação para novos usuários do projeto.
8. **`feat: adiciona suporte a contêineres Docker e script iniciar.bat`**
   * Configuração de ambiente portátil e instalável de forma isolada.
9. **`feat: implementa instalador de banco de dados, soft delete de usuários e tema escuro`**
   * Refatoração do modelo de dados e melhorias visuais e de UX.
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
