<?php
$title        = $title        ?? '';
$breadcrumbs  = $breadcrumbs  ?? [];  // [['label' => '...', 'href' => '...']]
$action_label = $action_label ?? '';
$action_href  = $action_href  ?? '';
?>
<div class="flex items-start justify-between mb-6">
    <div>
        <?php if ($breadcrumbs): ?>
        <nav class="text-sm breadcrumbs mb-1 text-base-content/50">
            <ul>
                <li><a href="<?= site_url('admin/dashboard') ?>"><?= lang('Common.nav_dashboard') ?></a></li>
                <?php foreach ($breadcrumbs as $crumb): ?>
                <li>
                    <?php if (! empty($crumb['href'])): ?>
                    <a href="<?= esc($crumb['href']) ?>"><?= esc($crumb['label']) ?></a>
                    <?php else: ?>
                    <?= esc($crumb['label']) ?>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <?php endif; ?>
        <h1 class="text-2xl font-bold text-base-content"><?= esc($title) ?></h1>
    </div>

    <?php if ($action_label && $action_href): ?>
    <a href="<?= esc($action_href) ?>" class="btn btn-primary btn-sm rounded-full gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <?= esc($action_label) ?>
    </a>
    <?php endif; ?>
</div>
