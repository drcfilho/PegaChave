# 🔑 PegaChave - Claviculário Digital por QR Code

O **PegaChave** é uma aplicação web moderna, segura e inteligente projetada para automatizar o controle de retirada e devolução de chaves de salas e ambientes em instituições de ensino e empresas de maneira extremamente rápida usando escaneamento de códigos QR.

---

## 🌟 Principais Funcionalidades

### 🖥️ 1. Tela do Quiosque (Portaria)
* **Scanner Integrado:** Utiliza a webcam ou câmera frontal do dispositivo para ler QR Codes de crachás de usuários e chaveiros físicos.
* **Modo Inteligente (Smart Scan):** Reconhece automaticamente o tipo do QR Code escaneado (Usuário ou Chave) e executa a ação correspondente sem necessidade de cliques.
* **Feedback de Áudio (Beep):** Retorno sonoro nativo para operações com sucesso, leitura de usuário ou erros.
* **Suporte a PWA (Progressive Web App):** Aplicativo instalável com Service Worker e manifesto para carregamento rápido e robustez offline.
* **Funcionamento Offline & Sincronização:** Se a rede cair, o Quiosque salva as leituras localmente no IndexedDB e as sincroniza em segundo plano assim que a conexão com o servidor retorna.
* **Entrada Manual & Tema:** Permite digitar o código da sala manualmente e alternar entre Tema Claro e Escuro.
* **Alertas Integrados Elegantes**: O quiosque não utiliza popups nativos (`alert()`). Todos os avisos e mensagens de erro são renderizados na própria tela de forma reativa.

### 📊 2. Painel de Administração Integrado
* **Dashboard com Analytics:** Métricas de chaves Disponíveis, Em Uso, logs de movimentação em tempo real e gráficos interativos (fluxo semanal e salas mais utilizadas) com Chart.js.
* **Personalização de Marca (White Label):** Configuração do Nome da Instituição e Cores (Primária/Secundária) diretamente pela interface de configuração.
* **Gerador de Etiquetas QR:** Geração em lote com filtros, seleção e download de imagens prontas para impressão.
* **Gerenciamento Completo com Soft Delete:** Cadastro de Salas/Chaves, Usuários e Operadores com desativação lógica (não exclui registros de forma permanente se houver histórico).
* **Arquivamento Seguro:** Visualização de usuários e chaves arquivadas em abas protegidas por senha extra.
* **Sistema de Reservas:** Agendamento de chaves para horários específicos com prevenção de conflitos e bloqueio no quiosque se reservado.
* **Limites e Restrições de Acesso:** Defina o número máximo de chaves por usuário, monte matrizes interativas de acesso (quais perfis podem retirar quais chaves) e crie listas de permissões exclusivas (laboratórios restritos).
* **Logs de Auditoria e CSV:** Registro detalhado das ações dos administradores e exportação de dados para Excel.

---

## 🏗️ Arquitetura e Tecnologias

A aplicação evoluiu para os mais rígidos padrões de mercado, estruturada 100% no modelo **MVC Puro (Model-View-Controller)** com arquitetura moderna:

* **Backend:** PHP 8.3+ Orientado a Objetos.
* **Padrão de Roteamento:** Ponto de entrada único (`index.php`), que despacha as requisições para os Controladores correspondentes via um Router dedicado.
* **Acesso a Dados (Repository Pattern):** Todo o acesso ao banco de dados foi abstraído em Repositórios (`ChaveRepository`, `UsuarioRepository`, etc). Os controladores estão limpos e não contém SQL bruto.
* **Banco de Dados & Migrations:** MySQL 8.4+ / MariaDB. O versionamento do banco de dados (Criação de Tabelas) é feito via **Phinx** (ferramenta padrão da indústria).
* **Template Engine:** Utilização do `BladeOne` (um port do poderoso Blade do Laravel) para separar a lógica PHP do HTML de forma super limpa.
* **Segurança Profissional:** Middleware de Autenticação e CSRF global. O arquivo `db.php` e todas as senhas ficam protegidas e as conexões usam PDO.
* **Frontend:** HTML5, **Tailwind CSS CLI** (gerando CSS compilado e minificado sem CDN), e **Alpine.js** (trazendo reatividade no Quiosque sem a complexidade do React ou Vue).
* **Leitor QR:** `html5-qrcode` para leitura via câmera no Quiosque.

---

## 📈 Últimas Atualizações e Melhorias Recentes

* **⚠️ Menu de Ocorrências e Avarias**: Criação de um painel de auditoria centralizado para registrar, visualizar e filtrar danos, chaves retidas ou salas deixadas abertas. Exibe por padrão os últimos 30 logs e permite busca refinada por período, sala ou usuário.
* **🛡️ Perfil de Operadores - Recepção**: Inclusão de um novo nível de acesso restrito (`recepcao`) com badge visual colorido, configurável através da interface de gerenciamento de operadores.
* **👥 Novos Perfis de Usuários**: Expansão do catálogo de funções de usuários, adicionando os perfis de **Recepção**, **Terceirizados** e **Manutenção** para cobrir todos os fluxos operacionais da instituição.
* **🛠️ Correções de Sessão e CSRF**: Resolução de bugs de token de segurança na área de Agendamentos e normalização das validações de sessão (`admin_role` fallback) para evitar bloqueios acidentais no menu administrativo.

---

## 🚀 Como Executar o Projeto

### Opção A: Execução via Docker (Recomendado)
Certifique-se de ter o Docker e Docker Compose instalados e execute na raiz do projeto:
```bash
docker compose up -d
```
O Docker compilará a imagem do PHP, configurará o contêiner do MySQL e instalará automaticamente. Acesse **`http://localhost:8000`** e você será redirecionado para o Instalador Automático.

---

### Opção B: Execução Manual com Servidor PHP Local
1. Instale o XAMPP ou outro servidor PHP com MySQL. Certifique-se que as dependências do Composer estão instaladas (`composer install`).
2. Copie o arquivo `.env.example` para `.env` e configure suas credenciais de conexão do MySQL e a URL Base:
   ```bash
   cp .env.example .env
   ```
3. Crie um banco de dados vazio com o nome escolhido no `.env` (ex: `controle_chaves`).
4. Inicie o servidor PHP integrado (ou utilize o XAMPP acessando pelo navegador):
   ```bash
   php -S localhost:8000
   ```
5. Acesse **`http://localhost:8000/install`** pelo navegador. 
   O Instalador Inteligente criará a estrutura do banco através do **Phinx Migrations** automaticamente. Após a instalação concluída, a rota será bloqueada por segurança.
6. Faça login em `/admin` com usuário `admin_master` e senha `admin` (recomendamos alterar imediatamente).

### 🔧 Compilação de Estilos (Modo de Desenvolvimento Frontend)
Caso queira modificar as classes do Tailwind no HTML (os arquivos `.blade.php`), será necessário recompilar o CSS.
Para compilar:
```bash
php bin/build_assets.php
```

---

## 👥 Contribuição
Este projeto foi desenvolvido e é mantido por:
* **Daniel Filho / Drcfilho** - [GitHub Profile](https://github.com/drcfilho)

---

## 📝 Licença
Este projeto é de código aberto e livre. Consulte as documentações para diretrizes de customização visual para sua escola ou instituição.
