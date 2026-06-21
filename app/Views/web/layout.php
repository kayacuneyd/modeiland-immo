<!DOCTYPE html>
<html lang="<?= esc($locale ?? 'tr') ?>" data-theme="cekirdekcms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= seo_tags($seoData ?? []) ?>
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>?v=<?= setting('app.css_version', '1') ?>">
</head>
<body class="min-h-screen bg-base-100 text-base-content flex flex-col">

    <?= component('navbar', ['locale' => $locale ?? 'tr']) ?>

    <main class="flex-1">
        <?= $content ?? '' ?>
    </main>

    <?= component('footer', ['locale' => $locale ?? 'tr']) ?>

</body>
</html>
