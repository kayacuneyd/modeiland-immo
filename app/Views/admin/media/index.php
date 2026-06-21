<?= admin_component('page-header', ['title' => lang('Common.nav_media')]) ?>

<!-- Yükleme formu -->
<div class="card bg-base-100 shadow-sm rounded-2xl mb-6">
    <div class="card-body">
        <h2 class="font-semibold text-base-content mb-3"><?= lang('Common.upload_media') ?></h2>
        <form action="<?= site_url('admin/media') ?>" method="post" enctype="multipart/form-data"
              class="flex items-end gap-4">
            <?= csrf_field() ?>
            <div class="form-control flex-1">
                <label class="label"><span class="ck-label"><?= lang('Common.file') ?></span></label>
                <input type="file" name="file" accept="image/*"
                       class="file-input file-input-bordered file-input-primary w-full" required>
            </div>
            <?= component('button', ['label' => lang('Common.upload'), 'type' => 'submit']) ?>
        </form>
        <p class="text-xs text-base-content/40 mt-2"><?= lang('Common.media_hint') ?></p>
    </div>
</div>

<!-- Medya galerisi -->
<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-base-content/50">
        <?= sprintf(lang('Common.media_count'), $total ?? 0) ?>
    </p>
</div>

<?php if (empty($items)): ?>
<div class="text-center py-20 text-base-content/40">
    <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
    </svg>
    <p><?= lang('Common.no_media') ?></p>
</div>
<?php else: ?>
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
    <?php foreach ($items as $item): ?>
    <div class="group relative aspect-square rounded-xl overflow-hidden bg-base-200 shadow-sm">
        <img src="<?= esc(media_url($item, 'thumb')) ?>"
             alt="<?= esc($item['alt_text'] ?? $item['original_name']) ?>"
             class="w-full h-full object-cover">

        <!-- Overlay -->
        <div class="absolute inset-0 bg-primary/80 opacity-0 group-hover:opacity-100 transition-opacity
                    flex flex-col items-center justify-center gap-2 p-2">
            <a href="<?= esc(base_url($item['path'])) ?>" target="_blank"
               class="btn btn-ghost btn-xs text-primary-content">
                <?= lang('Common.view') ?>
            </a>
            <form action="<?= site_url('admin/media/' . $item['id'] . '/delete') ?>"
                  method="post" class="m-0"
                  onsubmit="return confirm('<?= lang('Common.delete_confirm') ?>')">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-error btn-xs"><?= lang('Common.delete') ?></button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
