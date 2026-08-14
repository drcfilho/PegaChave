# 🔑 PegaChave - Claviculário Digital por QR Code

O **PegaChave** é uma aplicação web moderna e inteligente projetada para automatizar o controle de retirada e devolução de chaves de salas e ambientes em instituições de ensino e empresas de maneira extremamente rápida usando escaneamento de códigos QR.

---

## 🌟 Principais Funcionalidades

### 🖥️ 1. Tela do Quiosque (Portaria)
* **Scanner Integrado:** Utiliza a webcam ou câmera frontal do dispositivo para ler QR Codes de crachás de usuários e chaveiros físicos.
* **Modo Inteligente (Smart Scan):** Reconhece automaticamente o tipo do QR Code escaneado (Usuário ou Chave) e executa a ação correspondente sem necessidade de cliques.
* **Entrada Manual de Redundância:** Permite digitar o código da sala manualmente em caso de falhas na câmera ou no código impresso.
* **Tela de Sucesso Animada:** Exibe um feedback visual em tela cheia com ícones animados e os dados do portador da chave para conferência rápida.

### 📊 2. Painel de Administração Integrado
* **Dashboard em Tempo Real:** Gráficos e contadores de chaves Disponíveis, Em Uso, Atrasadas e estatísticas diárias de movimentação (devoluções e retiradas).
* **Gerenciamento Completo (CRUD):** Cadastro, edição e exclusão de **Salas/Chaves** e **Usuários** (estagiários, docentes, TAEs, limpeza, segurança, externos, alunos).
* **Filtros e Geração de QR Code:** Permite filtrar chaves ou usuários com busca em tempo real e selecionar itens específicos para gerar e imprimir grades de etiquetas QR Code.
* **Relatório Geral de Auditoria:** Histórico detalhado de movimentações com filtros de data, usuário e chaves, com folha de impressão limpa formatada para relatórios físicos.
* **Logs de Auditoria Administrativa:** Registro de log de todas as ações tomadas pelos administradores (exclusão, cadastros ou alteração de configurações).
* **Personalização Dinâmica:** Opção de alterar o nome da instituição e as cores principais da interface diretamente pelas configurações do painel admin.

### 🔍 3. Consulta Pública de Disponibilidade
* Página dedicada com busca rápida para verificar quais salas estão disponíveis ou com quem (e desde quando) está a chave em tempo real.

---

## 🛠️ Tecnologias Utilizadas
* **Backend:** PHP 8.1+ (PDO com MySQL)
* **Banco de Dados:** MySQL 8.0+ / MariaDB
* **Frontend:** HTML5, Vanilla CSS (Design premium responsivo com suporte a variáveis dinâmicas de cores), JavaScript nativo.
* **Bibliotecas:** `html5-qrcode` para leitura do scanner via câmera.

---

## 🚀 Como Executar Localmente

### 1. Pré-requisitos
Certifique-se de ter instalado em seu ambiente local:
* Servidor Web (Apache/Nginx ou servidor embutido do PHP)
* Banco de Dados MySQL 8.0+

### 2. Configurando o Banco de Dados
Importe o arquivo do esquema SQL localizado em `bin/mysql/schema.sql`:
```sql
CREATE DATABASE controle_chaves;
USE controle_chaves;
-- execute as queries contidas no schema.sql
```

### 3. Configurações de Conexão
Caso precise alterar os dados de acesso ao banco (host, usuário ou senha), edite o arquivo:
* `api/db.php`

### 4. Executando o Servidor de Desenvolvimento
Inicie o servidor embutido do PHP na pasta raiz do projeto:
```bash
php -S localhost:8000
```
Acesse **`http://localhost:8000`** no seu navegador. O painel administrativo estará acessível em **`http://localhost:8000/admin_login.php`** (Credenciais padrão: Usuário `admin` / Senha `admin`).

---

## 👥 Contribuição / Contributors
Este projeto foi desenvolvido e é mantido por:
* **Daniel Filho / Drcfilho** - [GitHub Profile](https://github.com/drcfilho)

---

## 📝 Licença
Este projeto é de uso institucional interno. Consulte a equipe de TI da instituição para obter informações sobre direitos de cópia e implantação em outras unidades.
