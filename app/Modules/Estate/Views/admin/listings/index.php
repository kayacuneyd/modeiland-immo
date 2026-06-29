<?php /** @var array $listings */ ?>

<div data-theme="modeiland">
<div class="p-6 max-w-6xl mx-auto space-y-6">

  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold text-primary">Inserate</h1>
  </div>

  <?php if (session()->has('success')): ?>
    <div class="alert alert-success"><span><?= esc(session('success')) ?></span></div>
  <?php endif ?>

  <div class="card bg-base-100 shadow">
    <div class="card-body p-0">
      <table class="table table-zebra">
        <thead>
          <tr>
            <th>#</th><th>Anbieter</th><th>Status</th><th>KI-Import</th>
            <th>Ort</th><th>Warmmiete</th><th>Erstellt</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($listings as $l): ?>
          <tr>
            <td class="font-mono text-xs"><?= $l['id'] ?></td>
            <td><?= esc($l['owner_name'] ?? '—') ?></td>
            <td>
              <span class="badge badge-sm
                <?= match($l['status']) {
                  'live'    => 'badge-success',
                  'draft'   => 'badge-warning',
                  'removed' => 'badge-error',
                  default   => 'badge-ghost',
                } ?>">
                <?= esc($l['status']) ?>
              </span>
            </td>
            <td>
              <span class="badge badge-sm badge-outline
                <?= $l['ai_import_status'] === 'done' ? 'badge-success' : '' ?>">
                <?= esc($l['ai_import_status']) ?>
              </span>
            </td>
            <td><?= esc($l['location_approx'] ?? '—') ?></td>
            <td><?= $l['warmmiete'] ? number_format($l['warmmiete'] / 100, 0, ',', '.') . ' €' : '—' ?></td>
            <td class="text-xs text-slate-400"><?= esc($l['created_at']) ?></td>
            <td>
              <a href="<?= site_url("admin/estate/listings/{$l['id']}") ?>"
                 class="btn btn-ghost btn-xs">Details</a>
            </td>
          </tr>
          <?php endforeach ?>
          <?php if (empty($listings)): ?>
          <tr><td colspan="8" class="text-center text-slate-400 py-8">Noch keine Inserate.</td></tr>
          <?php endif ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
</div>
