<!DOCTYPE html>
<html lang="tr" data-theme="cekirdekcms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'Admin') ?> — <?= esc(setting('site.title', 'CekirdekCMS')) ?></title>
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>?v=<?= setting('app.css_version', '1') ?>">
    <meta name="robots" content="noindex,nofollow">
</head>
<body class="min-h-screen bg-base-200">

<div class="drawer lg:drawer-open">
    <input id="admin-drawer" type="checkbox" class="drawer-toggle">

    <!-- Ana içerik -->
    <div class="drawer-content flex flex-col min-h-screen">

        <!-- Top bar -->
        <header class="navbar bg-primary text-primary-content shadow-md px-4 lg:px-6">
            <div class="flex-none lg:hidden">
                <label for="admin-drawer" class="btn btn-ghost btn-square">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </label>
            </div>

            <div class="flex-1">
                <span class="text-lg font-semibold hidden lg:block">
                    <?= esc($pageTitle ?? 'Panel') ?>
                </span>
            </div>

            <!-- Kullanıcı dropdown -->
            <div class="dropdown dropdown-end">
                <label tabindex="0" class="btn btn-ghost btn-sm gap-2 normal-case">
                    <div class="avatar placeholder">
                        <div class="bg-primary-content text-primary rounded-full w-8">
                            <span class="text-xs font-bold">
                                <?= strtoupper(substr($adminUser['name'] ?? 'A', 0, 1)) ?>
                            </span>
                        </div>
                    </div>
                    <span class="hidden md:inline text-sm"><?= esc($adminUser['name'] ?? '') ?></span>
                </label>
                <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 text-base-content rounded-box w-48 z-50">
                    <li class="menu-title text-xs"><?= esc($adminUser['role_name'] ?? '') ?></li>
                    <li>
                        <form action="<?= site_url('admin/logout') ?>" method="post" class="m-0 p-0">
                            <?= csrf_field() ?>
                            <button type="submit" class="w-full text-left text-error">
                                <?= lang('Auth.logout') ?>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        <!-- Flash mesajlar -->
        <?= admin_component('flash') ?>

        <!-- İçerik alanı -->
        <main class="flex-1 p-4 lg:p-8">
            <?= $content ?? '' ?>
        </main>

    </div>

    <!-- Sidebar -->
    <div class="drawer-side z-40">
        <label for="admin-drawer" class="drawer-overlay"></label>
        <aside class="w-64 min-h-full bg-primary text-primary-content flex flex-col">

            <!-- Logo -->
            <div class="p-4 border-b border-primary-content/20">
                <a href="<?= site_url('admin/dashboard') ?>" class="text-xl font-bold tracking-tight">
                    <?= esc(setting('site.title', 'CekirdekCMS')) ?>
                </a>
                <p class="text-xs text-primary-content/50 mt-1">Admin Panel</p>
            </div>

            <!-- Nav -->
            <nav class="flex-1 p-4 space-y-1">
                <?php
                $currentUri = '/' . uri_string();
                $navItems = $adminNav ?? [];
                foreach ($navItems as $item):
                    $isActive = str_starts_with($currentUri, parse_url($item['href'], PHP_URL_PATH));
                ?>
                <a href="<?= $item['href'] ?>"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          <?= $isActive ? 'bg-primary-content/20 text-primary-content font-semibold' : 'text-primary-content/70 hover:bg-primary-content/10 hover:text-primary-content' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $item['icon'] ?>"/>
                    </svg>
                    <?= esc($item['label']) ?>
                </a>
                <?php endforeach; ?>
            </nav>

            <!-- Ön yüze git -->
            <div class="p-4 border-t border-primary-content/20">
                <a href="<?= site_url('/') ?>" target="_blank"
                   class="flex items-center gap-2 text-xs text-primary-content/50 hover:text-primary-content">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    <?= lang('Common.view_site') ?>
                </a>
            </div>

        </aside>
    </div>
</div>

</body>
</html>
