<?php
$headline  = $headline  ?? '';
$body      = $body      ?? '';
$cta_label = $cta_label ?? '';
$cta_href  = $cta_href  ?? '#';
$bg        = $bg        ?? 'accent';    // accent|primary|base-200
?>
<section class="bg-<?= esc($bg) ?> py-20">
    <div class="ck-container text-center">
        <?php if ($headline): ?>
        <h2 class="text-3xl md:text-4xl font-bold <?= $bg === 'accent' ? 'text-accent-content' : 'text-primary-content' ?> mb-4">
            <?= esc($headline) ?>
        </h2>
        <?php endif; ?>
        <?php if ($body): ?>
        <p class="text-lg mb-8 opacity-80 max-w-xl mx-auto <?= $bg === 'accent' ? 'text-accent-content' : 'text-primary-content' ?>">
            <?= esc($body) ?>
        </p>
        <?php endif; ?>
        <?php if ($cta_label): ?>
        <a href="<?= esc($cta_href) ?>"
           class="btn btn-primary btn-lg rounded-full px-12">
            <?= esc($cta_label) ?>
        </a>
        <?php endif; ?>
    </div>
</section>
