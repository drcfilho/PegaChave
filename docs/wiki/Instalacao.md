# 🚀 Guia de Instalação

O PegaChave foi construído de forma portável, o que significa que pode ser rodado em qualquer ambiente que suporte PHP 8.3+ e MySQL/MariaDB 8+.

## 🐳 Método 1: Instalação via Docker (Recomendado)

O Docker cria um ambiente totalmente isolado (contêiner) que garante que o projeto funcionará sem conflitos com outras versões de PHP ou Banco de Dados na sua máquina.

**Pré-requisitos:**
* Docker e Docker Compose instalados no seu servidor ou máquina.

**Passo a passo:**
1. Faça o clone do repositório ou baixe o ZIP do projeto.
2. Abra o terminal na raiz do projeto.
3. Execute o comando:
   ```bash
   docker compose up -d
   ```
4. Aguarde o Docker baixar as imagens do PHP e MySQL e ligar os serviços.
5. Acesse no navegador: `http://localhost:8000`.
6. Você será redirecionado para o **Instalador Web**. Siga os passos na tela para criar as tabelas do banco e o usuário Administrador Mestre.

---

## 💻 Método 2: Instalação Local (XAMPP / WAMP / Linux)

Se preferir rodar em um servidor web tradicional ou local.

**Pré-requisitos:**
* PHP 8.3 ou superior.
* Extensão PDO MySQL ativada no `php.ini`.
* MySQL 8.4+ ou MariaDB.
* Composer instalado na máquina.

**Passo a passo:**
1. Abra a pasta do projeto no terminal e instale as dependências:
   ```bash
   composer install
   ```
2. Faça uma cópia do arquivo de configuração de exemplo:
   ```bash
   cp .env.example .env
   ```
3. Abra o arquivo `.env` e configure os dados de conexão do seu MySQL (usuário, senha, porta).
4. **Importante:** Crie um banco de dados vazio no seu MySQL com o nome que você colocou no `.env` (o padrão é `controle_chaves`).
5. Aponte o Document Root do seu servidor web (Apache/Nginx) para a pasta raiz do projeto.
   * *Alternativa rápida para testes locais:* Use o servidor nativo do PHP rodando no terminal: `php -S localhost:8000`.
6. Acesse o sistema pelo navegador (ex: `http://localhost:8000`).
7. Como o banco está vazio, o sistema redirecionará você para `http://localhost:8000/install`. 
8. Clique em **"Instalar Banco de Dados"**. O sistema criará as tabelas sozinho (via Phinx Migrations) e fornecerá um usuário Admin inicial.

---

## 🔐 O Primeiro Login

Após a instalação, o instalador web será **bloqueado permanentemente** por razões de segurança.
Acesse o painel de administração em: `http://localhost:8000/admin`.

* **Usuário Padrão:** `admin_master`
* **Senha Padrão:** `admin` (Você será obrigado a trocar de senha assim que quiser, recomendamos fazer isso imediatamente).
