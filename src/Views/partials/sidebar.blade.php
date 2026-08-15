<?php
// src/Views/partials/sidebar.php

if (!function_exists('has_permission')) {
    function has_permission($perm) {
        $role = $_SESSION['admin_role'] ?? 'admin_master';
        if ($role === 'admin_master') return true;
        
        $perms = $_SESSION['admin_permissoes'] ?? [];
        return is_array($perms) && (in_array($perm, $perms) || in_array('all', $perms));
    }
}

// Determinar a página ativa baseada na URL
$currentUri = $_SERVER['REQUEST_URI'];
$isActive = function($path) use ($currentUri) {
    return strpos($currentUri, $path) !== false ? 'bg-primary/10 border-primary text-white' : 'border-transparent text-slate-400 hover:text-white hover:bg-white/5';
};
?>
<aside class="sidebar w-[260px] bg-secondary/95 backdrop-blur-md text-white flex flex-col fixed top-0 bottom-0 left-0 z-50 border-r border-white/10 transition-all duration-300 overflow-y-auto">
    <div class="p-6 text-xl font-extrabold border-b border-white/10 flex items-center gap-2.5">
        <span>🔑</span> PegaChave
    </div>
    <ul class="flex-1 py-5 list-none m-0">
        <li>
            <a href="<?= BASE_URL ?>/admin" class="flex items-center gap-3 px-6 py-3.5 text-[15px] font-semibold transition-all duration-200 border-l-4 <?= $currentUri === BASE_URL . '/admin' || $currentUri === BASE_URL . '/admin/' ? 'bg-primary/10 border-primary text-white' : 'border-transparent text-slate-400 hover:text-white hover:bg-white/5' ?>">
                📊 Dashboard
            </a>
        </li>
        
        <?php if(has_permission('gerenciar_chaves')): ?>
        <li>
            <a href="<?= BASE_URL ?>/admin/chaves" class="flex items-center gap-3 px-6 py-3.5 text-[15px] font-semibold transition-all duration-200 border-l-4 <?= $isActive('/admin/chaves') ?>">
                🔑 Chaves/Salas
            </a>
        </li>

        <?php endif; ?>
        
        <?php if(has_permission('gerenciar_usuarios')): ?>
        <li>
            <a href="<?= BASE_URL ?>/admin/usuarios" class="flex items-center gap-3 px-6 py-3.5 text-[15px] font-semibold transition-all duration-200 border-l-4 <?= $isActive('/admin/usuarios') ?>">
                👤 Usuários
            </a>
        </li>

        <?php endif; ?>
        
        <?php if(has_permission('gerenciar_chaves')): ?>
        <li>
            <a href="<?= BASE_URL ?>/admin/reservas" class="flex items-center gap-3 px-6 py-3.5 text-[15px] font-semibold transition-all duration-200 border-l-4 <?= $isActive('/admin/reservas') ?>">
                📅 Agendamentos
            </a>
        </li>
        <?php endif; ?>
        
        <?php if(has_permission('gerenciar_usuarios')): ?>
        <li>
            <a href="<?= BASE_URL ?>/admin/restricoes" class="flex items-center gap-3 px-6 py-3.5 text-[15px] font-semibold transition-all duration-200 border-l-4 <?= $isActive('/admin/restricoes') ?>">
                🔒 Restrições
            </a>
        </li>
        <?php endif; ?>

        <?php if(has_permission('gerenciar_operadores')): ?>
        <li>
            <a href="<?= BASE_URL ?>/admin/operadores" class="flex items-center gap-3 px-6 py-3.5 text-[15px] font-semibold transition-all duration-200 border-l-4 <?= $isActive('/admin/operadores') ?>">
                🛡️ Operadores
            </a>
        </li>
        <?php endif; ?>
        
        <?php if(has_permission('gerenciar_configuracoes')): ?>
        <li>
            <a href="<?= BASE_URL ?>/admin/gerar_qr" class="flex items-center gap-3 px-6 py-3.5 text-[15px] font-semibold transition-all duration-200 border-l-4 <?= $isActive('/admin/gerar_qr') ?>">
                🖨️ Gerar QR Codes
            </a>
        </li>
        <?php endif; ?>
        
        <li>
            <a href="<?= BASE_URL ?>/admin/consulta" class="flex items-center gap-3 px-6 py-3.5 text-[15px] font-semibold transition-all duration-200 border-l-4 <?= $isActive('/admin/consulta') ?>">
                🔍 Consultar Disp.
            </a>
        </li>
        
        <?php if(has_permission('ver_relatorios')): ?>
        <?php 
        $isAuditoriaOpen = strpos($currentUri, '/admin/relatorio') !== false || 
                           strpos($currentUri, '/admin/logs') !== false || 
                           strpos($currentUri, '/admin/chaves_arquivadas') !== false || 
                           strpos($currentUri, '/admin/usuarios_arquivados') !== false;
        ?>
        <li>
            <details class="group" <?= $isAuditoriaOpen ? 'open' : '' ?>>
                <summary class="flex items-center justify-between px-6 py-3.5 text-[15px] font-semibold text-slate-400 hover:text-white hover:bg-white/5 cursor-pointer list-none transition-colors border-l-4 border-transparent">
                    <div class="flex items-center gap-3">
                        🛡️ Auditoria & Dados
                    </div>
                    <span class="transition-transform duration-300 group-open:rotate-180 opacity-50 text-xs">▼</span>
                </summary>
                <ul class="bg-white/5 flex flex-col border-l-4 border-transparent">
                    <li>
                        <a href="<?= BASE_URL ?>/admin/relatorio" class="flex items-center gap-3 pl-12 pr-6 py-3 text-sm font-medium transition-all duration-200 <?= strpos($currentUri, '/admin/relatorio') !== false ? 'text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                            📝 Histórico de Uso
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/logs" class="flex items-center gap-3 pl-12 pr-6 py-3 text-sm font-medium transition-all duration-200 <?= strpos($currentUri, '/admin/logs') !== false ? 'text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                            📋 Logs do Sistema
                        </a>
                    </li>
                    <?php if(has_permission('gerenciar_chaves')): ?>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/chaves_arquivadas" class="flex items-center gap-3 pl-12 pr-6 py-3 text-sm font-medium transition-all duration-200 <?= strpos($currentUri, '/admin/chaves_arquivadas') !== false ? 'text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                            🗄️ Chaves Arquivadas
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if(has_permission('gerenciar_usuarios')): ?>
                    <li>
                        <a href="<?= BASE_URL ?>/admin/usuarios_arquivados" class="flex items-center gap-3 pl-12 pr-6 py-3 text-sm font-medium transition-all duration-200 <?= strpos($currentUri, '/admin/usuarios_arquivados') !== false ? 'text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                            🗄️ Usuários Arquivados
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </details>
        </li>
        <?php endif; ?>
        
        <?php if(has_permission('gerenciar_configuracoes')): ?>
        <li>
            <a href="<?= BASE_URL ?>/admin/config" class="flex items-center gap-3 px-6 py-3.5 text-[15px] font-semibold transition-all duration-200 border-l-4 <?= $isActive('/admin/config') ?>">
                ⚙️ Configurações
            </a>
        </li>
        <?php endif; ?>
        
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
