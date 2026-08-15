# 🏗️ Arquitetura e Desenvolvimento

Para os desenvolvedores, o PegaChave foi construído utilizando os mais robustos e rígidos padrões de Engenharia de Software no ecossistema PHP.

## Padrão MVC e Roteamento
O projeto possui um único ponto de entrada: `index.php`. 
Toda requisição é roteada por ele e interceptada pelo `App\Core\Router` e middlewares (como CSRF e Auth), sendo então despachada para os Controladores correspondentes dentro da pasta `src/Controllers/`.
A estrutura de pastas é:
```
/src
  /Controllers     -> Lógica de negócio e coordenação (Ex: ScanController)
  /Models          -> Regras de acesso a dados e Repository Pattern (Ex: ChaveRepository)
  /Views           -> Interface do Usuário processada pelo Blade (ex: admin_dashboard.blade.php)
  /Core            -> Router, Middlewares e componentes centrais.
/bin               -> Scripts de manutenção e build
/phinx             -> Arquivos e seeds das migrações do banco.
```

## Banco de Dados e Repository Pattern
Diferente dos sistemas PHP legados, nenhum Controlador possui SQL Bruto (Raw SQL). Todo o banco de dados é acessado por classes localizadas em `src/Models/` injetando instâncias do `PDO`. 
Isso permite fácil testabilidade e isolamento.

O gerenciamento da estrutura de dados é feito pelo **Phinx**. Ao rodar o Instalador Web (`/install`), o código PHP chama a API do Phinx por trás dos panos rodando as migrações em `phinx/migrations/`. 
Para gerar uma nova migração localmente:
```bash
php vendor/bin/phinx create NovaTabela
```

## Frontend (Blade + Tailwind + Alpine.js)
As páginas renderizadas no navegador não são PHP cru, mas sim arquivos `.blade.php`.
Utilizamos a biblioteca `BladeOne`, uma versão super rápida e independente do famoso Blade do Laravel. Os arquivos gerados e cacheados ficam na pasta `/cache`.

O CSS é feito com **Tailwind CSS**. O binário autônomo dele está mapeado em `bin/build_assets.php`. Ele escaneia todos os arquivos `.blade.php` e `.js` da aplicação e gera o arquivo `assets/css/tailwind.css` apenas com as classes utilizadas, otimizando o carregamento e removendo o "peso" na rede da escola.
Sempre que editar uma classe no Blade, rode no terminal:
```bash
php bin/build_assets.php
```

A reatividade sem complexidade (como abrir/fechar modais e interações rápidas no DOM) é feita nativamente com o Framework Minimalista **Alpine.js**. E a lógica pesada do Scanner QR é gerenciada através de Vanilla JS limpo no `assets/js/quiosque.js`.

---

Este projeto é super escalável! Fique à vontade para olhar os repositórios em `src/Models/` e os Controladores em `src/Controllers/` para entender o fluxo completo.
