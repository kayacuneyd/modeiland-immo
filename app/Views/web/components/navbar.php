<?php
$locale   = $locale ?? 'tr';
$siteName = setting('site.title', 'CekirdekCMS');
$current  = '/' . uri_string();

$navLinks = [
    ['href' => site_url('/'),        'label' => lang('Common.nav_home')],
    ['href' => site_url('blog'),     'label' => lang('Common.nav_blog')],
    ['href' => site_url('contact'),  'label' => lang('Common.nav_contact')],
];
?>
<!-- DaisyUI Drawer (mobil hamburger) -->
<div class="drawer">
    <input id="ck-drawer" type="checkbox" class="drawer-toggle">
    <div class="drawer-content flex flex-col">
        <div class="navbar bg-primary text-primary-content shadow-lg">
            <div class="ck-container flex items-center justify-between w-full">

                <!-- Mobil hamburger -->
                <div class="flex-none lg:hidden">
                    <label for="ck-drawer" class="btn btn-ghost btn-square" aria-label="Menü">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </label>
                </div>

                <!-- Logo -->
                <a href="<?= site_url('/') ?>" class="text-xl font-bold tracking-tight">
                    <?= esc($siteName) ?>
                </a>

                <!-- Desktop nav -->
                <nav class="hidden lg:flex items-center gap-1">
                    <?php foreach ($navLinks as $link): ?>
                    <a href="<?= $link['href'] ?>"
                       class="btn btn-ghost btn-sm rounded-full text-primary-content/80 hover:text-primary-content <?= ($current === parse_url($link['href'], PHP_URL_PATH)) ? 'font-bold' : '' ?>">
                        <?= esc($link['label']) ?>
                    </a>
                    <?php endforeach; ?>
                </nav>

                <!-- CTA -->
                <a href="<?= site_url('admin') ?>" class="btn btn-accent btn-sm rounded-full hidden lg:flex">
                    <?= lang('Common.nav_admin') ?>
                </a>

            </div>
        </div>
    </div>

    <!-- Mobil drawer yan menü -->
    <div class="drawer-side z-50">
        <label for="ck-drawer" class="drawer-overlay"></label>
        <ul class="menu p-4 w-64 min-h-full bg-primary text-primary-content">
            <li class="mb-4">
                <a href="<?= site_url('/') ?>" class="text-lg font-bold"><?= esc($siteName) ?></a>
            </li>
            <?php foreach ($navLinks as $link): ?>
            <li><a href="<?= $link['href'] ?>"><?= esc($link['label']) ?></a></li>
            <?php endforeach; ?>
            <li class="mt-4"><a href="<?= site_url('admin') ?>" class="btn btn-accent btn-sm"><?= lang('Common.nav_admin') ?></a></li>
        </ul>
    </div>
</div>
