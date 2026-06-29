<?php
/** @var array $listing @var array $images */
$w = $listing['warmmiete']   ? number_format($listing['warmmiete']   / 100, 0, ',', '.') . ' €' : null;
$k = $listing['kaltmiete']   ? number_format($listing['kaltmiete']   / 100, 0, ',', '.') . ' €' : null;
$n = $listing['nebenkosten'] ? number_format($listing['nebenkosten'] / 100, 0, ',', '.') . ' €' : null;
$d = $listing['deposit']     ? number_format($listing['deposit']     / 100, 0, ',', '.') . ' €' : null;
?>
<div data-theme="modeiland" class="min-h-screen bg-base-100">

  <!-- Back nav -->
  <div class="bg-primary text-primary-content px-4 py-3">
    <div class="max-w-3xl mx-auto flex items-center gap-3">
      <a href="<?= site_url('inserate') ?>" class="link link-hover text-primary-content/70 text-sm">
        ← Alle Inserate
      </a>
      <span class="text-primary-content/40">|</span>
      <span class="font-bold text-sm">modeiland</span>
    </div>
  </div>

  <main class="max-w-3xl mx-auto px-4 py-6 space-y-6">

    <?php if (session()->has('success')): ?>
      <div class="alert alert-success"><span><?= esc(session('success')) ?></span></div>
    <?php endif ?>

    <!-- Gallery -->
    <div class="rounded-xl overflow-hidden bg-base-200 aspect-video flex items-center justify-center">
      <?php if (! empty($images)): ?>
        <img src="<?= esc($images[0]['path']) ?>" alt="Foto" class="object-cover w-full h-full">
      <?php else: ?>
        <div class="text-slate-400 text-sm p-6 text-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto mb-2 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4-4a3 3 0 014 0l4 4M14 12l2-2a3 3 0 014 0l2 2"/>
          </svg>
          Noch keine Fotos verfügbar
        </div>
      <?php endif ?>
    </div>

    <!-- Location header -->
    <div>
      <h1 class="text-2xl font-bold text-primary">
        <?= esc($listing['location_approx'] ?? 'Inserat') ?>
      </h1>
      <p class="text-slate-400 text-sm mt-0.5">
        <?= esc($listing['type'] === 'sale' ? 'Zum Kauf' : 'Zur Miete') ?> ·
        Privat, ohne Makler
      </p>
    </div>

    <!-- Fact strip (künye şeridi) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
      <?php
      $facts = [
        ['label' => 'Warmmiete',   'value' => $w,  'highlight' => true],
        ['label' => 'Kaltmiete',   'value' => $k],
        ['label' => 'Nebenkosten', 'value' => $n],
        ['label' => 'Kaution',     'value' => $d],
        ['label' => 'Zimmer',      'value' => $listing['rooms'] ? $listing['rooms'] . ' Zi.' : null],
        ['label' => 'Fläche',      'value' => $listing['m2'] ? $listing['m2'] . ' m²' : null],
        ['label' => 'Verfügbar',   'value' => esc($listing['available_from'] ?? null)],
        ['label' => 'Standort',    'value' => esc($listing['location_approx'] ?? null)],
      ];
      foreach ($facts as $f):
        if (! $f['value']) continue;
      ?>
        <div class="bg-base-200 rounded-lg p-3 <?= ! empty($f['highlight']) ? 'border-l-4 border-accent' : '' ?>">
          <div class="text-xs text-slate-500 mb-0.5"><?= $f['label'] ?></div>
          <div class="font-bold font-mono text-primary"><?= $f['value'] ?></div>
        </div>
      <?php endforeach ?>
    </div>

    <!-- AI description -->
    <?php if ($listing['ai_description']): ?>
    <div>
      <h2 class="text-lg font-bold text-primary mb-2">Beschreibung</h2>
      <p class="text-base-content/80 leading-relaxed text-base">
        <?= nl2br(esc($listing['ai_description'])) ?>
      </p>
    </div>
    <?php endif ?>

    <!-- Approximate location note -->
    <div class="bg-base-200 rounded-lg p-3 text-sm text-slate-500 flex gap-2 items-start">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
      </svg>
      <span>
        Ungefähre Lage: <strong><?= esc($listing['location_approx'] ?? 'Nicht angegeben') ?></strong>.
        Die genaue Adresse erhalten Sie nach Kontaktaufnahme mit dem Anbieter.
      </span>
    </div>

    <!-- CTA — contact -->
    <div class="card bg-primary text-primary-content shadow-lg">
      <div class="card-body items-center text-center py-8">
        <h2 class="card-title text-xl">Interesse? Nachricht senden</h2>
        <p class="text-primary-content/70 text-sm max-w-xs">
          Direktkontakt — ohne Makler, ohne Umwege.
          <?php if (! session()->get('estate_seeker_id')): ?>
          Kostenlos registrieren und sofort loslegen.
          <?php endif ?>
        </p>
        <a href="<?= site_url("inserate/{$listing['id']}/kontakt") ?>"
           class="btn btn-accent mt-2 min-h-[44px] px-8">
          Anbieter kontaktieren
        </a>
      </div>
    </div>

  </main>

  <footer class="text-center text-xs text-slate-400 py-6 border-t border-base-200 mt-8">
    <a href="<?= site_url('impressum') ?>" class="link">Impressum</a> ·
    <a href="<?= site_url('datenschutz') ?>" class="link">Datenschutz</a> ·
    <a href="<?= site_url('agb') ?>" class="link">AGB</a>
  </footer>

</div>
