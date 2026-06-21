<?php
$stats = $stats ?? [];
?>
<?= admin_component('page-header', ['title' => lang('Common.nav_dashboard')]) ?>

<!-- İstatistikler -->
<div class="stats stats-vertical lg:stats-horizontal shadow-sm w-full bg-base-100 rounded-2xl mb-8">
    <?= admin_component('stat-card', [
        'title' => lang('Common.stat_pages'),
        'value' => $stats['pages'] ?? 0,
        'desc'  => lang('Common.stat_published'),
        'color' => 'primary',
        'icon'  => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    ]) ?>
    <?= admin_component('stat-card', [
        'title' => lang('Common.stat_posts'),
        'value' => $stats['posts'] ?? 0,
        'desc'  => lang('Common.stat_published'),
        'color' => 'accent',
        'icon'  => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z',
    ]) ?>
    <?= admin_component('stat-card', [
        'title' => lang('Common.stat_messages'),
        'value' => $stats['messages'] ?? 0,
        'desc'  => lang('Common.stat_unread'),
        'color' => 'secondary',
        'icon'  => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    ]) ?>
    <?= admin_component('stat-card', [
        'title' => lang('Common.stat_media'),
        'value' => $stats['media'] ?? 0,
        'desc'  => lang('Common.stat_files'),
        'color' => 'neutral',
        'icon'  => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
    ]) ?>
</div>

<!-- Hızlı erişim -->
<h2 class="text-lg font-semibold text-base-content mb-4"><?= lang('Common.quick_actions') ?></h2>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <a href="<?= site_url('admin/pages/new') ?>" class="ck-card p-4 flex items-center gap-3 hover:bg-primary hover:text-primary-content group transition-colors">
        <svg class="w-6 h-6 text-primary group-hover:text-primary-content" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span class="font-medium text-sm"><?= lang('Common.new_page') ?></span>
    </a>
    <a href="<?= site_url('admin/blog/new') ?>" class="ck-card p-4 flex items-center gap-3 hover:bg-accent hover:text-accent-content group transition-colors">
        <svg class="w-6 h-6 text-accent group-hover:text-accent-content" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span class="font-medium text-sm"><?= lang('Common.new_post') ?></span>
    </a>
    <a href="<?= site_url('admin/media') ?>" class="ck-card p-4 flex items-center gap-3 hover:bg-neutral hover:text-neutral-content group transition-colors">
        <svg class="w-6 h-6 text-neutral group-hover:text-neutral-content" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
        </svg>
        <span class="font-medium text-sm"><?= lang('Common.upload_media') ?></span>
    </a>
    <a href="<?= site_url('admin/settings') ?>" class="ck-card p-4 flex items-center gap-3 hover:bg-secondary hover:text-secondary-content group transition-colors">
        <svg class="w-6 h-6 text-secondary group-hover:text-secondary-content" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <span class="font-medium text-sm"><?= lang('Common.nav_settings') ?></span>
    </a>
</div>
