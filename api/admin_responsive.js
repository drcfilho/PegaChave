// api/admin_responsive.js

document.addEventListener('DOMContentLoaded', () => {
    // 1. Criar e injetar o cabeçalho móvel no início do body
    const mobileHeader = document.createElement('div');
    mobileHeader.className = 'mobile-admin-header';
    mobileHeader.innerHTML = `
        <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Menu">☰</button>
        <div class="mobile-brand"><span>🔑</span> PegaChave</div>
        <div style="width: 24px;"></div> <!-- Espaçador para centralizar a marca -->
    `;
    document.body.insertBefore(mobileHeader, document.body.firstChild);

    // 2. Criar e injetar o backdrop (fundo escuro)
    const backdrop = document.createElement('div');
    backdrop.className = 'sidebar-backdrop';
    backdrop.id = 'sidebar-backdrop';
    document.body.appendChild(backdrop);

    // 3. Obter elementos da barra lateral (aside ou .sidebar)
    const sidebar = document.querySelector('aside') || document.querySelector('.sidebar');
    const toggleBtn = document.getElementById('mobile-menu-toggle');

    if (sidebar && toggleBtn) {
        // Função para abrir o menu
        const openMenu = () => {
            sidebar.classList.add('active');
            backdrop.classList.add('active');
        };

        // Função para fechar o menu
        const closeMenu = () => {
            sidebar.classList.remove('active');
            backdrop.classList.remove('active');
        };

        // Ouvinte de clique no botão hambúrguer
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (sidebar.classList.contains('active')) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        // Fechar menu ao clicar no backdrop
        backdrop.addEventListener('click', closeMenu);

        // Fechar menu ao clicar em qualquer item da sidebar em telas menores
        const menuItems = sidebar.querySelectorAll('a');
        menuItems.forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth <= 1024) {
                    closeMenu();
                }
            });
        });
    }
});
