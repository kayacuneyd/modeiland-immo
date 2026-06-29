<?php ?>

<div data-theme="modeiland">
<div class="p-6 max-w-xl mx-auto">

  <h1 class="text-2xl font-bold text-primary mb-6">Neuen Anbieter-Lead anlegen</h1>

  <?php if (session()->has('errors')): ?>
    <div class="alert alert-error mb-4">
      <ul class="list-disc list-inside text-sm">
        <?php foreach (session('errors') as $e): ?>
          <li><?= esc($e) ?></li>
        <?php endforeach ?>
      </ul>
    </div>
  <?php endif ?>

  <form method="post" action="<?= site_url('admin/estate/owners') ?>" class="space-y-4">
    <?= csrf_field() ?>

    <div class="form-control">
      <label class="label"><span class="label-text font-medium">Name / Alias *</span></label>
      <input type="text" name="display_name" value="<?= old('display_name') ?>"
             class="input input-bordered" required maxlength="150">
    </div>

    <div class="form-control">
      <label class="label"><span class="label-text font-medium">Quell-URL des Inserats</span></label>
      <input type="url" name="source_url" value="<?= old('source_url') ?>"
             class="input input-bordered" placeholder="https://..." maxlength="2000">
    </div>

    <div class="form-control">
      <label class="label"><span class="label-text font-medium">E-Mail</span></label>
      <input type="email" name="email" value="<?= old('email') ?>"
             class="input input-bordered" maxlength="200">
    </div>

    <div class="form-control">
      <label class="label"><span class="label-text font-medium">Telefon</span></label>
      <input type="text" name="phone" value="<?= old('phone') ?>"
             class="input input-bordered" maxlength="50">
    </div>

    <div class="form-control">
      <label class="label"><span class="label-text font-medium">Interne Notiz (Outreach)</span></label>
      <textarea name="outreach_note" class="textarea textarea-bordered" rows="3"
                maxlength="2000"><?= old('outreach_note') ?></textarea>
    </div>

    <div class="flex gap-3 pt-2">
      <button type="submit" class="btn btn-accent">Lead anlegen</button>
      <a href="<?= site_url('admin/estate/owners') ?>" class="btn btn-ghost">Abbrechen</a>
    </div>
  </form>

</div>
</div>
