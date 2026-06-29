<?php /** @var array $listing */ ?>

<div data-theme="modeiland">
<div class="p-6 max-w-4xl mx-auto space-y-6">

  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-primary">
        Inserat #<?= $listing['id'] ?>
        <span class="badge badge-sm ml-2
          <?= match($listing['status']) {
            'live'  => 'badge-success',
            'draft' => 'badge-warning',
            'removed' => 'badge-error',
            default => 'badge-ghost',
          } ?>">
          <?= esc($listing['status']) ?>
        </span>
      </h1>
      <p class="text-sm text-slate-500">Anbieter: <?= esc($listing['owner_name'] ?? '—') ?></p>
    </div>
    <a href="<?= site_url('admin/estate/listings') ?>" class="btn btn-ghost btn-sm">← Zurück</a>
  </div>

  <?php if (session()->has('success')): ?>
    <div class="alert alert-success"><span><?= esc(session('success')) ?></span></div>
  <?php endif ?>
  <?php if (session()->has('error')): ?>
    <div class="alert alert-error"><span><?= esc(session('error')) ?></span></div>
  <?php endif ?>

  <!-- KI-Import actions -->
  <div class="card bg-base-100 shadow">
    <div class="card-body">
      <h2 class="card-title text-base">KI-Import</h2>
      <p class="text-sm text-slate-500 mb-3">
        Status: <span class="font-mono badge badge-sm badge-outline"><?= esc($listing['ai_import_status']) ?></span>
      </p>

      <?php if ($listing['source_text_raw']): ?>
        <details class="mb-3">
          <summary class="cursor-pointer text-sm text-slate-500 hover:text-primary">Originaltext anzeigen</summary>
          <pre class="mt-2 bg-base-200 rounded p-3 text-xs overflow-auto max-h-48 whitespace-pre-wrap"><?= esc($listing['source_text_raw']) ?></pre>
        </details>
      <?php endif ?>

      <?php if (in_array($listing['ai_import_status'], ['pending', 'draft_pending', 'done'], true)): ?>
        <form method="post" action="<?= site_url("admin/estate/listings/{$listing['id']}/ai-import") ?>">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-accent btn-sm"
            onclick="return confirm('KI-Analyse starten? Das kann bis zu 25 Sekunden dauern.')">
            KI-Analyse starten
          </button>
        </form>
      <?php endif ?>
    </div>
  </div>

  <!-- Extracted data -->
  <div class="card bg-base-100 shadow">
    <div class="card-body">
      <h2 class="card-title text-base">Extrahierte Daten</h2>
      <dl class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
        <?php
        $fields = [
          'Kaltmiete'    => $listing['kaltmiete']   ? number_format($listing['kaltmiete'] / 100, 0, ',', '.') . ' €' : '—',
          'Warmmiete'    => $listing['warmmiete']   ? number_format($listing['warmmiete'] / 100, 0, ',', '.') . ' €' : '—',
          'Nebenkosten'  => $listing['nebenkosten'] ? number_format($listing['nebenkosten'] / 100, 0, ',', '.') . ' €' : '—',
          'Kaution'      => $listing['deposit']     ? number_format($listing['deposit'] / 100, 0, ',', '.') . ' €'  : '—',
          'Zimmer'       => $listing['rooms'] ?? '—',
          'm²'           => $listing['m2'] ? $listing['m2'] . ' m²' : '—',
          'Verfügbar'    => esc($listing['available_from'] ?? '—'),
          'Ort (exakt)'  => esc($listing['location_text'] ?? '—'),
          'Ort (ungefähr)' => esc($listing['location_approx'] ?? '—'),
        ];
        foreach ($fields as $label => $value):
        ?>
          <div>
            <dt class="text-slate-500 text-xs"><?= $label ?></dt>
            <dd class="font-medium"><?= $value ?></dd>
          </div>
        <?php endforeach ?>
      </dl>
    </div>
  </div>

  <!-- AI description -->
  <?php if ($listing['ai_description']): ?>
  <div class="card bg-base-100 shadow">
    <div class="card-body">
      <h2 class="card-title text-base">KI-generierte Beschreibung</h2>
      <p class="text-sm leading-relaxed"><?= nl2br(esc($listing['ai_description'])) ?></p>
    </div>
  </div>
  <?php endif ?>

  <!-- Actions -->
  <?php if ($listing['status'] === 'draft' && $listing['ai_import_status'] === 'done'): ?>
  <div class="card bg-base-100 shadow border border-success/30">
    <div class="card-body">
      <h2 class="card-title text-base text-success">Inserat veröffentlichen</h2>
      <p class="text-sm text-slate-500 mb-3">
        Nur veröffentlichen, wenn der Anbieter bereits zugestimmt hat (Einwilligung liegt vor).
      </p>
      <form method="post" action="<?= site_url("admin/estate/listings/{$listing['id']}/publish") ?>"
            onsubmit="return confirm('Inserat jetzt veröffentlichen?')">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-success btn-sm">Jetzt live schalten</button>
      </form>
    </div>
  </div>
  <?php endif ?>

  <!-- Danger -->
  <div class="card bg-base-100 shadow border border-error/30">
    <div class="card-body">
      <h2 class="card-title text-error text-base">Gefahrenzone</h2>
      <form method="post" action="<?= site_url("admin/estate/listings/{$listing['id']}/delete") ?>"
            onsubmit="return confirm('Inserat wirklich entfernen?')">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-error btn-sm">Inserat entfernen</button>
      </form>
    </div>
  </div>

  <!-- Back link to owner -->
  <?php if ($listing['owner_name']): ?>
  <div class="text-sm">
    <a href="<?= site_url("admin/estate/owners/{$listing['owner_id']}") ?>" class="link link-primary">
      ← Zurück zu <?= esc($listing['owner_name']) ?>
    </a>
  </div>
  <?php endif ?>

</div>
</div>
