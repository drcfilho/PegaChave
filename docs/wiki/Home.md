# Bem-vindo à Wiki do PegaChave 🔑

O **PegaChave** é um sistema inteligente e moderno para automatizar a retirada e devolução de chaves em portarias de instituições, utilizando leitura de QR Code.

Esta Wiki foi criada para auxiliar administradores, operadores de portaria e desenvolvedores a entender, configurar e utilizar o sistema em sua capacidade máxima.

## 📌 Índice de Conteúdos

* **[Guia de Instalação](Instalacao.md)**: Passo a passo de como subir o PegaChave do zero utilizando Docker ou Servidor Local (XAMPP/WAMP).
* **[Guia do Usuário & Administrador](Guia-do-Usuario.md)**: Como utilizar o Quiosque da Portaria, gerar etiquetas, cadastrar chaves/usuários e visualizar relatórios.
* **[Personalização (White Label)](Personalizacao.md)**: Como alterar as cores, o nome da instituição e outras configurações visuais do sistema sem mexer em código.
* **[Arquitetura e Desenvolvimento](Arquitetura.md)**: Documentação técnica do sistema (MVC, Repositories, BladeOne, Tailwind CSS) para desenvolvedores que queiram contribuir ou modificar o código fonte.

---

### 🌟 Visão Geral do Sistema

O sistema é dividido em duas partes principais:

1. **Quiosque da Portaria (`/`)**: Uma interface em Tela Cheia, otimizada para tablets e monitores touch, responsável por ler os QR Codes e validar entradas/saídas em milissegundos com feedback sonoro e visual.
2. **Painel de Administração (`/admin`)**: Um dashboard completo protegido por senha para gestão das chaves, restrições de acesso, arquivamento de usuários, agendamentos e emissão de relatórios em tempo real.
