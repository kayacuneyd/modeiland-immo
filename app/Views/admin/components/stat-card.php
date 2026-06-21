<?php
$title   = $title   ?? '';
$value   = $value   ?? '0';
$desc    = $desc    ?? '';
$icon    = $icon    ?? '';
$color   = $color   ?? 'primary';
?>
<div class="stat bg-base-100 rounded-2xl shadow-sm">
    <?php if ($icon): ?>
    <div class="stat-figure text-<?= esc($color) ?>">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="<?= $icon ?>"/>
        </svg>
    </div>
    <?php endif; ?>
    <div class="stat-title text-base-content/50 text-sm"><?= esc($title) ?></div>
    <div class="stat-value text-<?= esc($color) ?>"><?= esc($value) ?></div>
    <?php if ($desc): ?>
    <div class="stat-desc"><?= esc($desc) ?></div>
    <?php endif; ?>
</div>
