<?php /** @var array $leads @var array $active */ ?>

<div data-theme="modeiland">
<div class="p-6 max-w-5xl mx-auto space-y-8">

  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold text-primary">Anbieter-Leads</h1>
    <a href="<?= site_url('admin/estate/owners/new') ?>" class="btn btn-accent btn-sm">
      + Neuer Lead
    </a>
  </div>

  <?php if (session()->has('success')): ?>
    <div class="alert alert-success"><span><?= esc(session('success')) ?></span></div>
  <?php endif ?>

  <div class="card bg-base-100 shadow">
    <div class="card-body p-0">
      <table class="table table-zebra">
        <thead>
          <tr>
            <th>Name</th><th>Status</th><th>E-Mail</th><th>Quelle</th><th>Erstellt</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (array_merge($leads, $active) as $o): ?>
          <tr>
            <td><?= esc($o['display_name']) ?></td>
            <td>
              <span class="badge <?= $o['status'] === 'active' ? 'badge-success' : 'badge-warning' ?> badge-sm">
                <?= esc($o['status']) ?>
              </span>
            </td>
            <td><?= esc($o['email'] ?? '—') ?></td>
            <td class="max-w-xs truncate text-xs">
              <?php if ($o['source_url']): ?>
                <a href="<?= esc($o['source_url']) ?>" target="_blank" class="link link-primary">Link</a>
              <?php else: ?>—<?php endif ?>
            </td>
            <td class="text-xs text-slate-500"><?= esc($o['created_at']) ?></td>
            <td>
              <a href="<?= site_url("admin/estate/owners/{$o['id']}") ?>" class="btn btn-ghost btn-xs">
                Details
              </a>
            </td>
          </tr>
          <?php endforeach ?>
          <?php if (empty($leads) && empty($active)): ?>
          <tr><td colspan="6" class="text-center text-slate-400 py-8">Noch keine Leads.</td></tr>
          <?php endif ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
</div>
