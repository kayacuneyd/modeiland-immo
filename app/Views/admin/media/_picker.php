<!-- Modal tabanlı medya seçici — JS ile iframe veya fetch ile yüklenir -->
<div class="p-4">
    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
        <?php foreach ($items as $item): ?>
        <div class="aspect-square rounded-lg overflow-hidden bg-base-200 cursor-pointer
                    hover:ring-4 ring-primary transition-all"
             onclick="window.parent.ckMediaPick(<?= $item['id'] ?>, '<?= esc(base_url($item['path'])) ?>', '<?= esc(media_url($item, 'thumb')) ?>')">
            <img src="<?= esc(media_url($item, 'thumb')) ?>"
                 alt="<?= esc($item['original_name']) ?>"
                 class="w-full h-full object-cover">
        </div>
        <?php endforeach; ?>
    </div>
</div>
