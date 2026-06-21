<?php
$title     = $title     ?? '';
$subtitle  = $subtitle  ?? '';
$content   = $content   ?? '';
$align     = $align     ?? 'center';   // center|left
$bg        = $bg        ?? '';         // '' | 'base-200' | 'primary'
$id        = $id        ?? '';
?>
<section class="ck-section <?= $bg ? "bg-{$bg}" : '' ?>" <?= $id ? "id=\"{$id}\"" : '' ?>>
    <div class="ck-container">
        <?php if ($title || $subtitle): ?>
        <div class="text-<?= esc($align) ?> mb-12">
            <?php if ($title): ?>
            <h2 class="text-3xl md:text-4xl font-bold text-base-content mb-4">
                <?= esc($title) ?>
            </h2>
            <?php endif; ?>
            <?php if ($subtitle): ?>
            <p class="text-lg text-base-content/60 max-w-2xl <?= $align === 'center' ? 'mx-auto' : '' ?>">
                <?= esc($subtitle) ?>
            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?= $content ?>
    </div>
</section>
