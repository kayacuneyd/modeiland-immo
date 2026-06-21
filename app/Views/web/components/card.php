<?php
$title      = $title      ?? '';
$body       = $body       ?? '';
$image      = $image      ?? '';
$image_alt  = $image_alt  ?? $title;
$cta_label  = $cta_label  ?? '';
$cta_href   = $cta_href   ?? '#';
$badge      = $badge      ?? '';
?>
<div class="ck-card">
    <?php if ($image): ?>
    <figure>
        <img src="<?= esc($image) ?>" alt="<?= esc($image_alt) ?>"
             class="w-full h-48 object-cover">
    </figure>
    <?php endif; ?>
    <div class="card-body">
        <?php if ($badge): ?>
        <span class="badge badge-accent badge-sm mb-1"><?= esc($badge) ?></span>
        <?php endif; ?>
        <?php if ($title): ?>
        <h3 class="card-title text-base-content"><?= esc($title) ?></h3>
        <?php endif; ?>
        <?php if ($body): ?>
        <p class="text-base-content/70 text-sm"><?= esc($body) ?></p>
        <?php endif; ?>
        <?php if ($cta_label): ?>
        <div class="card-actions justify-end mt-2">
            <a href="<?= esc($cta_href) ?>" class="btn btn-primary btn-sm rounded-full">
                <?= esc($cta_label) ?>
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>
