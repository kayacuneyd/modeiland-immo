<article class="ck-section">
    <div class="ck-container max-w-3xl">

        <!-- Breadcrumb -->
        <nav class="text-sm text-base-content/50 mb-6">
            <a href="<?= site_url('blog') ?>" class="hover:text-accent"><?= lang('Blog.title') ?></a>
            <span class="mx-2">/</span>
            <span><?= esc($post['title']) ?></span>
        </nav>

        <!-- Başlık & meta -->
        <header class="mb-8">
            <?php if ($post['category_name']): ?>
            <span class="badge badge-accent badge-sm mb-3"><?= esc($post['category_name']) ?></span>
            <?php endif; ?>
            <h1 class="text-4xl font-bold text-base-content mb-4"><?= esc($post['title']) ?></h1>
            <p class="text-sm text-base-content/50">
                <?= lang('Blog.published') ?>: <?= date('d.m.Y', strtotime($post['published_at'])) ?>
            </p>
        </header>

        <!-- İçerik -->
        <div class="prose prose-lg max-w-none text-base-content/80">
            <?= $post['content'] ?>
        </div>

        <!-- Önceki / Sonraki -->
        <footer class="mt-16 pt-8 border-t border-base-200 grid grid-cols-2 gap-4">
            <?php if ($adjacent['prev']): ?>
            <a href="<?= site_url('blog/' . $adjacent['prev']['slug']) ?>"
               class="text-left group">
                <p class="text-xs text-base-content/40 mb-1"><?= lang('Blog.prev_post') ?></p>
                <p class="font-medium text-base-content group-hover:text-accent transition-colors">
                    ← <?= esc($adjacent['prev']['title']) ?>
                </p>
            </a>
            <?php else: ?><div></div><?php endif; ?>

            <?php if ($adjacent['next']): ?>
            <a href="<?= site_url('blog/' . $adjacent['next']['slug']) ?>"
               class="text-right group">
                <p class="text-xs text-base-content/40 mb-1"><?= lang('Blog.next_post') ?></p>
                <p class="font-medium text-base-content group-hover:text-accent transition-colors">
                    <?= esc($adjacent['next']['title']) ?> →
                </p>
            </a>
            <?php endif; ?>
        </footer>
    </div>
</article>
