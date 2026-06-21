<?php
$label   = $label   ?? 'Button';
$href    = $href    ?? null;
$type    = $type    ?? 'button';
$variant = $variant ?? 'primary';   // primary|accent|ghost|outline|error
$size    = $size    ?? 'md';        // sm|md|lg
$full    = $full    ?? false;
$extra   = $extra   ?? '';

$sizeClass = match ($size) {
    'sm'    => 'btn-sm',
    'lg'    => 'btn-lg',
    default => '',
};

$variantClass = match ($variant) {
    'accent'  => 'btn-accent',
    'ghost'   => 'btn-ghost',
    'outline' => 'btn-outline btn-primary',
    'error'   => 'btn-error',
    default   => 'btn-primary',
};

$classes = "btn {$variantClass} {$sizeClass} rounded-full" . ($full ? ' w-full' : '') . ($extra ? " {$extra}" : '');
?>
<?php if ($href): ?>
<a href="<?= esc($href) ?>" class="<?= $classes ?>"><?= esc($label) ?></a>
<?php else: ?>
<button type="<?= esc($type) ?>" class="<?= $classes ?>"><?= esc($label) ?></button>
<?php endif; ?>
