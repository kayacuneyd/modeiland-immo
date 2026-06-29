<?php /** @var array $owner */ ?>

<div data-theme="modeiland">
<div class="p-6 max-w-xl mx-auto">

  <h1 class="text-2xl font-bold text-primary mb-1">Neues Inserat</h1>
  <p class="text-sm text-slate-500 mb-6">
    Anbieter: <strong><?= esc($owner['display_name']) ?></strong>
  </p>

  <?php if (session()->has('errors')): ?>
    <div class="alert alert-error mb-4">
      <ul class="list-disc list-inside text-sm">
        <?php foreach (session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach ?>
      </ul>
    </div>
  <?php endif ?>

  <form method="post" action="<?= site_url('admin/estate/listings') ?>" class="space-y-4">
    <?= csrf_field() ?>
    <input type="hidden" name="owner_id" value="<?= $owner['id'] ?>">

    <div class="form-control">
      <label class="label"><span class="label-text font-medium">Quell-URL des Inserats</span></label>
      <input type="url" name="source_url" value="<?= old('source_url', $owner['source_url'] ?? '') ?>"
             class="input input-bordered" placeholder="https://..." maxlength="2000">
    </div>

    <div class="form-control">
      <label class="label">
        <span class="label-text font-medium">Quelltext (für KI-Import)</span>
        <span class="label-text-alt text-slate-400">Kopierter Anzeigentext</span>
      </label>
      <textarea name="source_text_raw" class="textarea textarea-bordered font-mono text-xs" rows="10"
                placeholder="Originaltext des Inserats hier einfügen..."><?= old('source_text_raw') ?></textarea>
    </div>

    <div class="form-control">
      <label class="label"><span class="label-text font-medium">Typ</span></label>
      <select name="type" class="select select-bordered">
        <option value="rent" <?= old('type') === 'rent' ? 'selected' : '' ?>>Miete</option>
        <option value="sale" <?= old('type') === 'sale' ? 'selected' : '' ?>>Kauf</option>
      </select>
    </div>

    <div class="flex gap-3 pt-2">
      <button type="submit" class="btn btn-accent">Entwurf anlegen</button>
      <a href="<?= site_url("admin/estate/owners/{$owner['id']}") ?>" class="btn btn-ghost">Abbrechen</a>
    </div>
  </form>

</div>
</div>
