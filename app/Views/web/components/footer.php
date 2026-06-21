<?php
$locale    = $locale ?? 'tr';
$siteName  = setting('site.title', 'CekirdekCMS');
$tagline   = setting('site.tagline', '');
$copyright = setting('footer.copyright', '© ' . date('Y') . ' ' . $siteName);
?>
<footer class="footer footer-center bg-primary text-primary-content p-10 mt-auto">
    <div class="ck-container w-full">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-left w-full">

            <!-- Marka -->
            <div>
                <a href="<?= site_url('/') ?>" class="text-xl font-bold"><?= esc($siteName) ?></a>
                <?php if ($tagline): ?>
                <p class="mt-2 text-primary-content/70 text-sm"><?= esc($tagline) ?></p>
                <?php endif; ?>
            </div>

            <!-- Navigasyon -->
            <div>
                <p class="font-semibold mb-3"><?= lang('Common.footer_nav') ?></p>
                <nav class="flex flex-col gap-1 text-sm text-primary-content/70">
                    <a href="<?= site_url('/') ?>" class="hover:text-accent"><?= lang('Common.nav_home') ?></a>
                    <a href="<?= site_url('blog') ?>" class="hover:text-accent"><?= lang('Common.nav_blog') ?></a>
                    <a href="<?= site_url('contact') ?>" class="hover:text-accent"><?= lang('Common.nav_contact') ?></a>
                </nav>
            </div>

            <!-- İletişim -->
            <div>
                <p class="font-semibold mb-3"><?= lang('Common.footer_contact') ?></p>
                <p class="text-sm text-primary-content/70"><?= esc(setting('site.email', '')) ?></p>
            </div>

        </div>

        <div class="divider my-6 opacity-20"></div>
        <p class="text-sm text-primary-content/60"><?= esc($copyright) ?></p>
    </div>
</footer>
