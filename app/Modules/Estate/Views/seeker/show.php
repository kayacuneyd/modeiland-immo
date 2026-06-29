<?php
/**
 * @var array      $listing
 * @var array      $images
 * @var array|null $fitResult   null|{score:int, reason:string}
 * @var bool       $canApply    seeker is logged in + has active subscription
 */
use App\Modules\Estate\Models\ListingImageModel;

$w = $listing['warmmiete']   ? number_format($listing['warmmiete']   / 100, 0, ',', '.') . ' €' : null;
$k = $listing['kaltmiete']   ? number_format($listing['kaltmiete']   / 100, 0, ',', '.') . ' €' : null;
$n = $listing['nebenkosten'] ? number_format($listing['nebenkosten'] / 100, 0, ',', '.') . ' €' : null;
$d = $listing['deposit']     ? number_format($listing['deposit']     / 100, 0, ',', '.') . ' €' : null;

$imageCount = count($images);
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

    <!-- Fit score badge (shown when seeker has active search preferences) -->
    <?php if (! empty($fitResult)): ?>
    <div class="alert bg-success/10 border border-success/30 text-sm py-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <div>
        <span class="font-semibold text-success"><?= (int) $fitResult['score'] ?> % Übereinstimmung</span>
        <?php if (! empty($fitResult['reason'])): ?>
          <span class="text-slate-500 ml-2">— <?= esc($fitResult['reason']) ?></span>
        <?php endif ?>
      </div>
    </div>
    <?php endif ?>

    <!-- Image gallery -->
    <?php if ($imageCount > 0): ?>
    <div>
      <!-- Main image -->
      <div class="rounded-xl overflow-hidden bg-base-200 aspect-video relative" id="gallery-main">
        <img id="gallery-main-img"
             src="<?= esc(ListingImageModel::displayUrl($images[0])) ?>"
             alt="Foto 1 von <?= esc($listing['location_approx'] ?? 'Inserat') ?>"
             class="object-cover w-full h-full"
             loading="eager">
        <?php if ($imageCount > 1): ?>
          <button onclick="galleryPrev()" aria-label="Vorheriges Foto"
                  class="absolute left-2 top-1/2 -translate-y-1/2 btn btn-circle btn-sm bg-black/40 border-0 text-white hover:bg-black/60">‹</button>
          <button onclick="galleryNext()" aria-label="Nächstes Foto"
                  class="absolute right-2 top-1/2 -translate-y-1/2 btn btn-circle btn-sm bg-black/40 border-0 text-white hover:bg-black/60">›</button>
          <span class="absolute bottom-2 right-3 text-white text-xs bg-black/50 rounded px-2 py-0.5">
            <span id="gallery-current">1</span> / <?= $imageCount ?>
          </span>
        <?php endif ?>
      </div>

      <!-- Thumbnail strip -->
      <?php if ($imageCount > 1): ?>
      <div class="flex gap-2 mt-2 overflow-x-auto pb-1" role="list" aria-label="Galerie Vorschaubilder">
        <?php foreach ($images as $i => $img): ?>
        <button onclick="galleryGoto(<?= $i ?>)"
                id="gallery-thumb-<?= $i ?>"
                role="listitem"
                aria-label="Foto <?= $i + 1 ?>"
                class="shrink-0 w-16 h-16 rounded-lg overflow-hidden border-2 <?= $i === 0 ? 'border-primary' : 'border-transparent' ?> focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary transition-all">
          <img src="<?= esc(ListingImageModel::displayUrl($img)) ?>"
               alt="Vorschau <?= $i + 1 ?>"
               class="object-cover w-full h-full"
               loading="lazy">
        </button>
        <?php endforeach ?>
      </div>
      <?php endif ?>
    </div>

    <?php else: ?>
    <div class="rounded-xl overflow-hidden bg-base-200 aspect-video flex items-center justify-center">
      <div class="text-slate-400 text-sm p-6 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto mb-2 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4-4a3 3 0 014 0l4 4M14 12l2-2a3 3 0 014 0l2 2"/>
        </svg>
        Noch keine Fotos verfügbar
      </div>
    </div>
    <?php endif ?>

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

    <!-- Fact strip -->
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

    <!-- CTA — contact + Bewerbungspaket -->
    <div class="card bg-primary text-primary-content shadow-lg">
      <div class="card-body items-center text-center py-8 gap-3">
        <h2 class="card-title text-xl">Interesse? Jetzt handeln</h2>
        <p class="text-primary-content/70 text-sm max-w-xs">
          Direktkontakt — ohne Makler, ohne Umwege.
        </p>
        <div class="flex flex-wrap justify-center gap-3 mt-1">
          <a href="<?= site_url("inserate/{$listing['id']}/kontakt") ?>"
             class="btn btn-accent min-h-[44px] px-6">
            Anbieter kontaktieren
          </a>
          <?php if ($canApply ?? false): ?>
            <a href="<?= site_url("inserate/{$listing['id']}/bewerben") ?>"
               class="btn btn-outline border-primary-content/40 text-primary-content hover:bg-primary-content/10 min-h-[44px] px-6">
              Bewerbungspaket erstellen
            </a>
          <?php else: ?>
            <a href="<?= site_url('abonnieren') ?>"
               class="btn btn-ghost border-primary-content/20 text-primary-content/70 min-h-[44px] px-4 text-sm">
              Plus: Bewerbungsunterlagen
            </a>
          <?php endif ?>
        </div>
      </div>
    </div>

  </main>

  <footer class="text-center text-xs text-slate-400 py-6 border-t border-base-200 mt-8">
    <a href="<?= site_url('impressum') ?>" class="link">Impressum</a> ·
    <a href="<?= site_url('datenschutz') ?>" class="link">Datenschutz</a> ·
    <a href="<?= site_url('agb') ?>" class="link">AGB</a>
  </footer>

</div>

<?php if ($imageCount > 1): ?>
<script>
(function () {
  var images = <?= json_encode(array_map(
    fn($img) => ListingImageModel::displayUrl($img),
    $images
  )) ?>;
  var current = 0;

  function galleryGoto(idx) {
    current = idx;
    document.getElementById('gallery-main-img').src = images[idx];
    document.getElementById('gallery-main-img').alt = 'Foto ' + (idx + 1);
    document.getElementById('gallery-current').textContent = idx + 1;
    images.forEach(function (_, i) {
      var thumb = document.getElementById('gallery-thumb-' + i);
      if (thumb) {
        thumb.classList.toggle('border-primary', i === idx);
        thumb.classList.toggle('border-transparent', i !== idx);
      }
    });
  }

  window.galleryNext = function () { galleryGoto((current + 1) % images.length); };
  window.galleryPrev = function () { galleryGoto((current - 1 + images.length) % images.length); };
  window.galleryGoto = galleryGoto;

  // Keyboard navigation
  document.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowRight') galleryNext();
    if (e.key === 'ArrowLeft')  galleryPrev();
  });
})();
</script>
<?php endif ?>
