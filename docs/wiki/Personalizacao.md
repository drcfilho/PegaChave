# 🎨 Personalização do Sistema (White Label)

O PegaChave foi pensado para ser integrado de forma transparente em qualquer escola ou empresa. Você não precisa alterar o código-fonte para mudar as cores ou o nome do sistema!

## Como mudar as Cores e o Nome

1. Acesse o Painel de Administração (`/admin`).
2. No menu lateral, clique em **⚙️ Configurações**.
3. Na seção "Identidade Visual":
   * **Nome da Instituição:** Será exibido no topo do Quiosque e nos relatórios impressos.
   * **Cor Primária:** Escolha a cor principal da sua instituição usando o seletor de cores (ex: Azul Marinho, Verde Escuro, etc).
   * **Cor Secundária:** Escolha uma cor contrastante para fundos, barra de navegação e botões no Tema Escuro.
4. Clique em "Salvar".
5. **Mágica!** Graças às variáveis CSS dinâmicas injetadas diretamente no Tailwind do projeto, o sistema reescreve as cores do site inteiro instantaneamente, sem precisar recompilar código. As etiquetas do gerador de QR Code também herdarão essas cores.

## O Tema do Quiosque

O botão "🌞/🌙" no canto inferior direito do Quiosque alterna entre o Modo Claro e Escuro.
Esta preferência do porteiro/operador é salva no navegador (LocalStorage) e lembrada toda vez que a página for recarregada.

Você pode combinar a Cor Primária da sua Instituição para que fique legível tanto no Tema Claro quanto no Tema Escuro. Recomendamos cores fortes, já que elas se adaptam lindamente a ambas as condições visuais.
