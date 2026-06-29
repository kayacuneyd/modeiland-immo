<?php
/**
 * @var array  $listings
 * @var int    $total
 * @var int    $page
 * @var int    $perPage
 * @var array  $filters
 * @var bool   $hasSavedPrefs
 * @var bool   $canSave
 * @var int    $seekerId
 */
?>
<div data-theme="modeiland" class="min-h-screen bg-base-100">

  <!-- Header -->
  <header class="bg-primary text-primary-content py-5 px-4">
    <div class="max-w-5xl mx-auto flex items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold tracking-tight mb-1">modeiland</h1>
        <p class="text-primary-content/70 text-sm">Wohnungen &amp; Häuser — direkt vom Eigentümer</p>
      </div>
      <?php if ($seekerId): ?>
        <a href="<?= site_url('seeker/panel') ?>" class="btn btn-ghost btn-sm text-primary-content/70">Mein Bereich</a>
      <?php else: ?>
        <a href="<?= site_url('abonnieren') ?>" class="btn btn-accent btn-sm">Plus freischalten</a>
      <?php endif ?>
    </div>
  </header>

  <!-- Filter bar -->
  <div class="bg-base-200 border-b border-base-300 sticky top-0 z-10">
    <form method="get" action="<?= site_url('inserate') ?>"
          class="max-w-5xl mx-auto px-4 py-3 flex flex-wrap gap-2 items-end">
      <div class="form-control">
        <label class="label py-0"><span class="label-text text-xs">Warmmiete max (€)</span></label>
        <input type="number" name="max_warmmiete" value="<?= esc($filters['max_warmmiete'] ?? '') ?>"
               class="input input-sm input-bordered w-28" placeholder="z.B. 1200">
      </div>
      <div class="form-control">
        <label class="label py-0"><span class="label-text text-xs">Zimmer min.</span></label>
        <input type="number" name="min_rooms" value="<?= esc($filters['min_rooms'] ?? '') ?>"
               step="0.5" class="input input-sm input-bordered w-20" placeholder="z.B. 2">
      </div>
      <div class="form-control">
        <label class="label py-0"><span class="label-text text-xs">m² min.</span></label>
        <input type="number" name="min_m2" value="<?= esc($filters['min_m2'] ?? '') ?>"
               class="input input-sm input-bordered w-20" placeholder="50">
      </div>
      <div class="form-control">
        <label class="label py-0"><span class="label-text text-xs">Standort</span></label>
        <input type="text" name="location" value="<?= esc($filters['location'] ?? '') ?>"
               class="input input-sm input-bordered w-36" placeholder="z.B. Schwabing">
      </div>
      <button type="submit" class="btn btn-accent btn-sm">Suchen</button>
      <?php if (array_filter($filters)): ?>
        <a href="<?= site_url('inserate') ?>" class="btn btn-ghost btn-sm">Zurücksetzen</a>
      <?php endif ?>
    </form>
  </div>

  <main class="max-w-5xl mx-auto px-4 py-6">

    <?php if (session()->has('info')): ?>
      <div class="alert alert-info mb-4"><span><?= esc(session('info')) ?></span></div>
    <?php endif ?>
    <?php if (session()->has('success')): ?>
      <div class="alert alert-success mb-4"><span><?= esc(session('success')) ?></span></div>
    <?php endif ?>

    <!-- Results bar -->
    <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
      <p class="text-sm text-slate-500">
        <?= $total ?> Inserat<?= $total !== 1 ? 'e' : '' ?> gefunden
        <?php if ($hasSavedPrefs): ?>
          <span class="badge badge-primary badge-sm ml-2">Gespeicherte Suche aktiv</span>
        <?php endif ?>
        <?php if ($seekerId && ! empty(array_filter($filters))): ?>
          <span class="text-xs text-success ml-2">· nach Übereinstimmung sortiert</span>
        <?php endif ?>
      </p>

      <!-- Save search button (seeker logged in + active filters) -->
      <?php if ($canSave && ! empty(array_filter($filters))): ?>
      <form method="post" action="<?= site_url('seeker/suche/speichern') ?>" class="flex items-center gap-2">
        <?= csrf_field() ?>
        <?php
        // Map URL filter keys → saved search filter keys
        $saveFilters = array_filter([
            'location'  => $filters['location'] ?? '',
            'rent_max'  => $filters['max_warmmiete'] ?? '',
            'rooms_min' => $filters['min_rooms'] ?? '',
            'min_m2'    => $filters['min_m2'] ?? '',
            'type'      => $filters['type'] ?? '',
        ]);
        foreach ($saveFilters as $k => $v): ?>
          <input type="hidden" name="filters[<?= esc($k) ?>]" value="<?= esc($v) ?>">
        <?php endforeach ?>
        <button type="submit" class="btn btn-ghost btn-xs border border-base-300">
          🔖 Suche speichern
        </button>
      </form>
      <?php endif ?>
    </div>

    <?php if (empty($listings)): ?>
      <div class="text-center py-20 text-slate-400">
        <p class="text-lg">Keine Inserate gefunden.</p>
        <p class="text-sm mt-1">Versuchen Sie andere Filtereinstellungen.</p>
      </div>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php foreach ($listings as $l):
        $warmmiete = $l['warmmiete'] ? number_format($l['warmmiete'] / 100, 0, ',', '.') . ' €/Mo.' : null;
        $score     = $l['_score'] ?? null;
      ?>
      <a href="<?= site_url("inserate/{$l['id']}") ?>"
         class="card bg-base-100 shadow hover:shadow-md transition-shadow border border-base-300 hover:border-accent/50 relative">

        <!-- Fit score badge -->
        <?php if ($score !== null && $score >= 50): ?>
          <div class="absolute top-2 right-2 z-10">
            <div class="badge <?= $score >= 80 ? 'badge-success' : 'badge-primary' ?> badge-sm font-mono font-bold shadow">
              <?= $score ?>%
            </div>
          </div>
        <?php endif ?>

        <!-- Thumbnail -->
        <figure class="aspect-video bg-base-200 flex items-center justify-center rounded-t-xl overflow-hidden">
          <?php if (! empty($l['_thumb'])): ?>
            <img src="<?= esc($l['_thumb']) ?>" alt="<?= esc($l['location_approx'] ?? '') ?>"
                 class="object-cover w-full h-full" loading="lazy">
          <?php else: ?>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
            </svg>
          <?php endif ?>
        </figure>

        <div class="card-body p-4">
          <div class="text-xs text-slate-400 mb-1"><?= esc($l['location_approx'] ?? 'Ort nicht angegeben') ?></div>
          <?php if ($warmmiete): ?>
            <div class="font-bold font-mono text-primary text-lg"><?= $warmmiete ?></div>
          <?php endif ?>
          <div class="flex gap-3 text-xs text-slate-500 mt-1 flex-wrap">
            <?php if ($l['rooms']): ?><span><?= $l['rooms'] ?> Zi.</span><?php endif ?>
            <?php if ($l['m2']):    ?><span><?= $l['m2'] ?> m²</span><?php endif ?>
            <?php if ($l['available_from']): ?><span>ab <?= esc($l['available_from']) ?></span><?php endif ?>
          </div>
          <?php if ($l['ai_description']): ?>
            <p class="text-xs text-slate-500 mt-2 line-clamp-2">
              <?= esc(mb_substr(strip_tags($l['ai_description']), 0, 120)) ?>
            </p>
          <?php endif ?>
        </div>
      </a>
      <?php endforeach ?>
    </div>

    <!-- Pagination -->
    <?php if ($total > $perPage):
      $pages = (int) ceil($total / $perPage);
    ?>
    <div class="flex justify-center mt-8 gap-1 flex-wrap">
      <?php for ($p = 1; $p <= $pages; $p++): ?>
        <a href="?<?= http_build_query(array_merge(array_filter($filters), ['page' => $p])) ?>"
           class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-ghost' ?>">
          <?= $p ?>
        </a>
      <?php endfor ?>
    </div>
    <?php endif ?>

    <?php endif ?>

    <!-- Upsell for non-logged-in seekers -->
    <?php if (! $seekerId): ?>
    <div class="mt-10 card bg-primary/5 border border-primary/20">
      <div class="card-body py-5 flex-row items-center gap-4 flex-wrap">
        <div class="flex-1 min-w-0">
          <p class="font-semibold text-primary text-sm">Direkt mit Anbietern in Kontakt treten</p>
          <p class="text-xs text-slate-500 mt-0.5">Plus-Mitglieder können Anbieter schreiben, Suchalarme einrichten und Bewerbungsunterlagen erstellen.</p>
        </div>
        <a href="<?= site_url('abonnieren') ?>" class="btn btn-primary btn-sm shrink-0">Plus freischalten</a>
      </div>
    </div>
    <?php endif ?>

  </main>

  <footer class="text-center text-xs text-slate-400 py-6 border-t border-base-200">
    <a href="<?= site_url('impressum') ?>" class="link">Impressum</a> ·
    <a href="<?= site_url('datenschutz') ?>" class="link">Datenschutz</a> ·
    <a href="<?= site_url('agb') ?>" class="link">AGB</a>
  </footer>

</div>
