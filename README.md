# 🔑 PegaChave - Claviculário Digital por QR Code

O **PegaChave** é uma aplicação web moderna e inteligente projetada para automatizar o controle de retirada e devolução de chaves de salas e ambientes em instituições de ensino e empresas de maneira extremamente rápida usando escaneamento de códigos QR.

---

## 🌟 Principais Funcionalidades

### 🖥️ 1. Tela do Quiosque (Portaria)
* **Scanner Integrado:** Utiliza a webcam ou câmera frontal do dispositivo para ler QR Codes de crachás de usuários e chaveiros físicos.
* **Modo Inteligente (Smart Scan):** Reconhece automaticamente o tipo do QR Code escaneado (Usuário ou Chave) e executa a ação correspondente sem necessidade de cliques.
* **Feedback de Áudio (Beep):** Retorno sonoro nativo via Web Audio API para operações com sucesso (bip agudo duplo), leitura de usuário (bip médio) ou erros (bip grave) para facilitar a operação do quiosque.
* **Suporte a PWA (Progressive Web App):** Aplicativo instalável com Service Worker e manifesto para carregamento rápido e robustez mesmo com conexão oscilante.
* **Funcionamento Offline & Sincronização:** Se a rede cair, o Quiosque salva as leituras localmente no LocalStorage e as sincroniza em segundo plano de forma automática assim que a conexão com o servidor retorna.
* **Entrada Manual de Redundância e Tema:** Permite digitar o código da sala manualmente e alternar entre Tema Claro e Escuro.
* **Alertas Integrados Sem Popup**: O quiosque não utiliza popups nativos (`alert()`). Todos os avisos e mensagens de erro são renderizados na própria tela através de um banner de status elegante que fecha sozinho após 5 segundos.

### 📊 2. Painel de Administração Integrado
* **Dashboard com Analytics:** Métricas de chaves Disponíveis, Em Uso, logs de movimentação em tempo real e gráficos interativos (fluxo semanal e salas mais utilizadas) com Chart.js.
* **Layout 100% Responsivo:** O painel foi otimizado para ser totalmente usável em tablets na portaria, com barra lateral ocultável e navegação fluida em telas touch.
* **Gerador de Etiquetas QR**: Geração em lote com filtros de busca e seleção por checkboxes, com suporte para impressão de etiquetas e download de imagens PNG compostas contendo o QR Code e os detalhes do item (como localização e nome) geradas em alta resolução (300x410 px).
* **Gerenciamento Completo (CRUD) com Soft Delete:** Cadastro de Salas/Chaves (contendo informações de Bloco e Andar para melhor organização) e Usuários com desativação lógica.
* **Arquivamento e Auditoria Protegida:** O arquivamento de usuários mantém o histórico de chaves seguras no banco de dados. Os dados dos usuários arquivados são visualizados em aba especial protegida por senha.
* **Logs de Auditoria Administrativa:** Registro detalhado de todas as ações tomadas pelos administradores.
* **Exportação para CSV**: Possibilidade de exportar relatórios de movimentações e logs de auditoria administrativa diretamente para arquivos CSV (compatíveis com Excel).
* **Sistema de Reservas & Histórico**: Agendamento de chaves para datas e horários específicos pelo painel admin com prevenção de conflitos, bloqueio no quiosque se reservado, e auditoria do histórico de agendamentos passados.
* **Alertas de Atraso**: Envio automatizado de e-mails para usuários que excederem o período limite de posse de chaves (através de execução periódica do script de alerta).
* **Limite de Chaves**: Controle de limite máximo de chaves em posse simultânea por usuário (configurável pelo administrador), bloqueando novas retiradas no quiosque se excedido.
* **Matriz de Restrições de Acesso**: Interface de matriz interativa de Perfis vs Salas para bloquear a retirada de salas específicas por determinados perfis de usuários, com validação e travamento em tempo real no quiosque.
* **Acesso Restrito por Matrícula**: Cadastro de lista de matrículas permitidas para chaves críticas (como CPD ou laboratórios específicos), garantindo que apenas usuários cadastrados com tais matrículas consigam realizar a retirada no quiosque.
* **Segurança CSRF:** Proteção ativa em todos os formulários administrativos contra falsificação de requisições.

### 🔍 3. Consulta Pública de Disponibilidade
* Página dedicada com busca rápida em tempo real para verificar a disponibilidade de salas.
* **Alternador de Visualização**: Suporte a modo Cards (Grid) e modo Lista compacta, salvando a preferência do usuário localmente no navegador.
* **Planejamento de Uso**: Exibição em tempo real de quem está com a chave (se em uso) e dos agendamentos programados para o dia de hoje.

---

## 🛠️ Tecnologias Utilizadas
* **Backend:** PHP 8.3+ (PDO com MySQL)
* **Banco de Dados:** MySQL 8.4+ / MariaDB
* **Frontend:** HTML5, CSS3 nativo, JavaScript Vanilla (sem bibliotecas externas pesadas).
* **Leitor QR:** `html5-qrcode` para leitura via câmera.
* **Ambiente:** Dockerizado e portátil.

---

## 🚀 Como Executar o Projeto

### Opção A: Execução via Docker (Recomendado)
Certifique-se de ter o Docker e Docker Compose instalados e execute na raiz do projeto:
```bash
docker compose up -d
```
O Docker compilará a imagem do PHP, configurará o contêiner do MySQL e executará o script de instalação automática. Acesse **`http://localhost:8000`** diretamente.

---

### Opção B: Execução Local no Windows (Executáveis Locais)
Se você utiliza os executáveis locais presentes na pasta `bin/`:
1. Dê um clique duplo no arquivo **`iniciar.bat`** na raiz do projeto.
2. Ele iniciará o banco de dados MySQL e o servidor embutido do PHP em segundo plano e abrirá o navegador na aplicação automaticamente.

---

### Opção C: Execução Manual com Servidor PHP Local
1. Copie o arquivo `.env.example` para `.env` e configure suas credenciais de conexão do MySQL:
   ```bash
   cp .env.example .env
   ```
2. Inicialize o banco de dados e aplique as migrações automáticas executando o script instalador:
   ```bash
   php bin/install.php
   ```
3. Inicie o servidor PHP integrado:
   ```bash
   php -S localhost:8000
   ```
4. Acesse **`http://localhost:8000`** (Admin em `/admin_login.php` com usuário `admin` / senha `admin`).

---

## 👥 Contribuição / Contributors
Este projeto foi desenvolvido e é mantido por:
* **Daniel Filho / Drcfilho** - [GitHub Profile](https://github.com/drcfilho)

---

## 📝 Licença
Este projeto é de uso institucional interno. Consulte a equipe de TI da instituição para obter informações sobre direitos de cópia e implantação em outras unidades.
