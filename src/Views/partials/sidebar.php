<?php
// src/Views/partials/sidebar.php

if (!function_exists('has_permission')) {
    function has_permission($perm) {
        if (($_SESSION['admin_role'] ?? '') === 'admin_master') return true;
        $perms = $_SESSION['admin_permissoes'] ?? [];
        return is_array($perms) && (in_array($perm, $perms) || in_array('all', $perms));
    }
}

// Determinar a página ativa baseada na URL
$currentUri = $_SERVER['REQUEST_URI'];
$isActive = function($path) use ($currentUri) {
    // Retorna 'active' se o path estiver contido na URI (ajuste simples)
    return strpos($currentUri, $path) !== false ? 'active' : '';
};
?>
<aside>
    <div class="sidebar-header">
        <span>🔑</span> PegaChave
    </div>
    <ul class="sidebar-menu">
        <li class="sidebar-item <?= $currentUri === BASE_URL . '/admin' || $currentUri === BASE_URL . '/admin/' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/admin">📊 Dashboard</a>
        </li>
        <?php if(has_permission('gerenciar_chaves')): ?>
        <li class="sidebar-item <?= $isActive('/admin/chaves') ?>">
            <a href="<?= BASE_URL ?>/admin/chaves">🔑 Chaves/Salas</a>
        </li>
        <li class="sidebar-item <?= $isActive('/admin/reservas') ?>">
            <a href="<?= BASE_URL ?>/admin/reservas">📅 Agendamentos</a>
        </li>
        <?php endif; ?>
        
        <?php if(has_permission('gerenciar_usuarios')): ?>
        <li class="sidebar-item <?= $isActive('/admin/usuarios') ?>">
            <a href="<?= BASE_URL ?>/admin/usuarios">👤 Usuários</a>
        </li>
        <li class="sidebar-item <?= $isActive('/admin/usuarios_arquivados') ?>">
            <a href="<?= BASE_URL ?>/admin/usuarios_arquivados">🗄️ Arquivados</a>
        </li>
        <li class="sidebar-item <?= $isActive('/admin/restricoes') ?>">
            <a href="<?= BASE_URL ?>/admin/restricoes">🔒 Restrições</a>
        </li>
        <?php endif; ?>
        
        <?php if(has_permission('gerenciar_operadores')): ?>
        <li class="sidebar-item <?= $isActive('/admin/operadores') ?>">
            <a href="<?= BASE_URL ?>/admin/operadores">🛡️ Operadores</a>
        </li>
        <?php endif; ?>
        
        <li class="sidebar-item <?= $isActive('/admin/consulta') ?>">
            <a href="<?= BASE_URL ?>/admin/consulta">🔍 Consultar Disp.</a>
        </li>
        
        <?php if(has_permission('ver_relatorios')): ?>
        <li class="sidebar-item <?= $isActive('/admin/relatorio') ?>">
            <a href="<?= BASE_URL ?>/admin/relatorio">📝 Relatório Geral</a>
        </li>
        <li class="sidebar-item <?= $isActive('/admin/logs') ?>">
            <a href="<?= BASE_URL ?>/admin/logs">📋 Logs de Auditoria</a>
        </li>
        <?php endif; ?>
        
        <?php if(has_permission('gerenciar_configuracoes')): ?>
        <li class="sidebar-item <?= $isActive('/admin/gerar_qr') ?>">
            <a href="<?= BASE_URL ?>/admin/gerar_qr">🖨️ Gerar QR Codes</a>
        </li>
        <li class="sidebar-item <?= $isActive('/admin/config') ?>">
            <a href="<?= BASE_URL ?>/admin/config">⚙️ Configurações</a>
        </li>
        <?php endif; ?>
        
        <li class="sidebar-item">
            <a href="<?= BASE_URL ?>/admin_logout.php" style="color: #ef4444;">🚪 Sair</a>
        </li>
    </ul>
    <div class="sidebar-footer">
        <a href="<?= BASE_URL ?>/" class="btn-kiosk">🖥️ Voltar ao Quiosque</a>
    </div>
</aside>
