<?php
$seo      = $seo ?? [];
$title    = esc($seo['title'] ?? setting('site.title', 'CekirdekCMS'));
$desc     = esc($seo['description'] ?? setting('site.description', ''));
$robots   = esc($seo['robots'] ?? 'index,follow');
$canonical = esc($seo['canonical'] ?? current_url());
$ogType   = esc($seo['og_type'] ?? 'website');
$ogImage  = esc($seo['og_image'] ?? setting('seo.og_image', ''));
$ogTitle  = esc($seo['og_title'] ?? $seo['title'] ?? setting('site.title', 'CekirdekCMS'));
$ogDesc   = esc($seo['og_description'] ?? $seo['description'] ?? setting('site.description', ''));
$locale   = $seo['locale'] ?? 'tr';
$siteTitle = setting('site.title', 'CekirdekCMS');
?>
<title><?= $title ?> | <?= esc($siteTitle) ?></title>
<meta name="description" content="<?= $desc ?>">
<meta name="robots" content="<?= $robots ?>">
<link rel="canonical" href="<?= $canonical ?>">

<meta property="og:type"        content="<?= $ogType ?>">
<meta property="og:title"       content="<?= $ogTitle ?>">
<meta property="og:description" content="<?= $ogDesc ?>">
<meta property="og:url"         content="<?= $canonical ?>">
<?php if ($ogImage): ?>
<meta property="og:image"       content="<?= $ogImage ?>">
<?php endif; ?>

<?php if (! empty($seo['hreflang'])): ?>
<?php foreach ($seo['hreflang'] as $lang => $url): ?>
<link rel="alternate" hreflang="<?= esc($lang) ?>" href="<?= esc($url) ?>">
<?php endforeach; ?>
<link rel="alternate" hreflang="x-default" href="<?= esc($seo['hreflang']['tr'] ?? current_url()) ?>">
<?php endif; ?>
