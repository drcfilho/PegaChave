<?php
// src/Views/partials/sidebar.php

// Helper para renderizar item de menu principal com base na permissão
if (!function_exists('render_menu_item')) {
    function render_menu_item($perm, $url, $label, $icon, $isActiveClass) {
        $hasPerm = has_permission($perm);
        if ($hasPerm) {
            $href = BASE_URL . $url;
            $class = "flex items-center gap-3 px-6 py-3.5 text-[15px] font-semibold transition-all duration-200 border-l-4 " . $isActiveClass;
            $title = "";
        } else {
            $href = "javascript:void(0)";
            $class = "flex items-center gap-3 px-6 py-3.5 text-[15px] font-semibold border-l-4 border-red-600 text-red-500 opacity-60 cursor-not-allowed hover:bg-red-500/5";
            $title = "Acesso Bloqueado";
        }
        
        echo "<li>
            <a href=\"$href\" class=\"$class\" title=\"$title\">
                <span>$icon</span> $label
            </a>
        </li>";
    }
}

// Helper para renderizar subitem de menu com base na permissão
if (!function_exists('render_submenu_item')) {
    function render_submenu_item($perm, $url, $label, $icon, $currentUri) {
        $hasPerm = has_permission($perm);
        $fullUrl = BASE_URL . $url;
        $isCurrent = strpos($currentUri, $url) !== false;
        
        if ($hasPerm) {
            $href = $fullUrl;
            $class = "flex items-center gap-3 pl-4 pr-6 py-2 text-sm font-medium transition-all duration-200 " . ($isCurrent ? 'text-white font-bold bg-white/5' : 'text-slate-400 hover:text-white hover:bg-white/5');
            $title = "";
        } else {
            $href = "javascript:void(0)";
            $class = "flex items-center gap-3 pl-4 pr-6 py-2 text-sm font-medium border-l-4 border-red-600 text-red-500 opacity-60 cursor-not-allowed hover:bg-red-500/5";
            $title = "Acesso Bloqueado";
        }
        
        echo "<li>
            <a href=\"$href\" class=\"$class\" title=\"$title\">
                <span>$icon</span> $label
            </a>
        </li>";
    }
}

// Determinar a página ativa baseada na URL
$currentUri = $_SERVER['REQUEST_URI'];
$isActive = function($path) use ($currentUri) {
    return strpos($currentUri, $path) !== false ? 'bg-primary/10 border-primary text-white font-bold' : 'border-transparent text-slate-400 hover:text-white hover:bg-white/5';
};
?>
<aside class="sidebar w-[260px] bg-secondary/95 backdrop-blur-md text-white flex flex-col fixed top-0 bottom-0 left-0 z-50 border-r border-white/10 transition-all duration-300 overflow-y-auto">
    <div class="p-6 text-xl font-extrabold border-b border-white/10 flex items-center gap-2.5">
        <span>🔑</span> PegaChave
    </div>
    <ul class="flex-1 py-5 list-none m-0">
        <li>
            <a href="<?= BASE_URL ?>/admin" class="flex items-center gap-3 px-6 py-3.5 text-[15px] font-semibold transition-all duration-200 border-l-4 <?= $currentUri === BASE_URL . '/admin' || $currentUri === BASE_URL . '/admin/' ? 'bg-primary/10 border-primary text-white font-bold' : 'border-transparent text-slate-400 hover:text-white hover:bg-white/5' ?>">
                📊 Dashboard
            </a>
        </li>
        
        <?php 
        render_menu_item('gerenciar_chaves', '/admin/chaves', 'Chaves/Salas', '🔑', $isActive('/admin/chaves'));
        render_menu_item('gerenciar_usuarios', '/admin/usuarios', 'Usuários', '👤', $isActive('/admin/usuarios'));
        render_menu_item('gerenciar_chaves', '/admin/reservas', 'Agendamentos', '📅', $isActive('/admin/reservas'));
        render_menu_item('gerenciar_usuarios', '/admin/restricoes', 'Restrições', '🔒', $isActive('/admin/restricoes'));
        render_menu_item('gerenciar_operadores', '/admin/operadores', 'Operadores', '🛡️', $isActive('/admin/operadores'));
        render_menu_item('gerenciar_configuracoes', '/admin/gerar_qr', 'Gerar QR Codes', '🖨️', $isActive('/admin/gerar_qr'));
        ?>
        
        <li>
            <a href="<?= BASE_URL ?>/admin/consulta" class="flex items-center gap-3 px-6 py-3.5 text-[15px] font-semibold transition-all duration-200 border-l-4 <?= $isActive('/admin/consulta') ?>">
                🔍 Consultar Disp.
            </a>
        </li>
        
        <?php 
        render_menu_item('ver_relatorios', '/admin/ocorrencias', 'Ocorrências', '⚠️', $isActive('/admin/ocorrencias'));
        ?>
        
        <?php 
        $hasAuditoriaPerm = has_permission('ver_relatorios');
        $isAuditoriaOpen = strpos($currentUri, '/admin/relatorio') !== false || 
                           strpos($currentUri, '/admin/logs') !== false || 
                           strpos($currentUri, '/admin/chaves_arquivadas') !== false || 
                           strpos($currentUri, '/admin/usuarios_arquivados') !== false;
        ?>
        <li>
            <details class="group" <?= $isAuditoriaOpen ? 'open' : '' ?>>
                <summary class="flex items-center justify-between px-6 py-3.5 text-[15px] font-semibold cursor-pointer list-none [&::-webkit-details-marker]:hidden transition-colors border-l-4 border-transparent <?= $hasAuditoriaPerm ? 'text-slate-400 hover:text-white hover:bg-white/5' : 'border-red-600 text-red-500 opacity-60 cursor-not-allowed hover:bg-red-500/5' ?>" title="<?= !$hasAuditoriaPerm ? 'Acesso Bloqueado' : '' ?>">
                    <div class="flex items-center gap-3">
                        🛡️ Auditoria & Dados
                    </div>
                    <span class="transition-transform duration-300 group-open:rotate-180 opacity-50 text-xs">▼</span>
                </summary>
                <ul class="bg-black/15 flex flex-col pl-4 border-l border-white/10 ml-14 my-1 gap-1">
                    <?php 
                    render_submenu_item('ver_relatorios', '/admin/relatorio', 'Histórico de Uso', '📝', $currentUri);
                    render_submenu_item('ver_relatorios', '/admin/logs', 'Logs do Sistema', '📋', $currentUri);
                    render_submenu_item('gerenciar_chaves', '/admin/chaves_arquivadas', 'Chaves Arquivadas', '🗄️', $currentUri);
                    render_submenu_item('gerenciar_usuarios', '/admin/usuarios_arquivados', 'Usuários Arquivados', '🗄️', $currentUri);
                    ?>
                </ul>
            </details>
        </li>
        
        <?php 
        render_menu_item('gerenciar_configuracoes', '/admin/config', 'Configurações', '⚙️', $isActive('/admin/config'));
        ?>
        
        <li>
            <a href="<?= BASE_URL ?>/admin_logout.php" class="flex items-center gap-3 px-6 py-3.5 text-[15px] font-semibold transition-all duration-200 border-l-4 border-transparent text-red-400 hover:text-red-300 hover:bg-white/5">
                🚪 Sair
            </a>
        </li>
    </ul>
    <div class="p-5 border-t border-white/10">
        <a href="<?= BASE_URL ?>/" class="flex items-center justify-center gap-2 bg-white/5 hover:bg-primary text-white text-sm font-bold py-2.5 px-4 rounded-lg transition-colors">
            🖥️ Voltar ao Quiosque
        </a>
    </div>
</aside>
