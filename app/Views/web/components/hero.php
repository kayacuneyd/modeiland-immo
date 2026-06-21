<?php
$headline    = $headline    ?? '';
$subheadline = $subheadline ?? '';
$cta_label   = $cta_label   ?? '';
$cta_href    = $cta_href    ?? '#';
$cta2_label  = $cta2_label  ?? '';
$cta2_href   = $cta2_href   ?? '#';
$bg_image    = $bg_image    ?? '';
?>
<section class="hero min-h-[70vh] <?= $bg_image ? 'bg-cover bg-center' : 'bg-gradient-to-br from-primary to-secondary' ?>"
         <?= $bg_image ? "style=\"background-image:url('" . esc($bg_image) . "')\"" : '' ?>>
    <?php if ($bg_image): ?>
    <div class="hero-overlay bg-primary/70"></div>
    <?php endif; ?>
    <div class="hero-content text-center text-primary-content py-24">
        <div class="max-w-3xl">
            <?php if ($headline): ?>
            <h1 class="text-4xl md:text-6xl font-bold leading-tight mb-6">
                <?= esc($headline) ?>
            </h1>
            <?php endif; ?>

            <?php if ($subheadline): ?>
            <p class="text-lg md:text-xl text-primary-content/80 mb-10 max-w-xl mx-auto">
                <?= esc($subheadline) ?>
            </p>
            <?php endif; ?>

            <?php if ($cta_label || $cta2_label): ?>
            <div class="flex flex-wrap gap-4 justify-center">
                <?php if ($cta_label): ?>
                <a href="<?= esc($cta_href) ?>" class="ck-btn-accent">
                    <?= esc($cta_label) ?>
                </a>
                <?php endif; ?>
                <?php if ($cta2_label): ?>
                <a href="<?= esc($cta2_href) ?>" class="btn btn-outline btn-primary-content rounded-full px-8">
                    <?= esc($cta2_label) ?>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
