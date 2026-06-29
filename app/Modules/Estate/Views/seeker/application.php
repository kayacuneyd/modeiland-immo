<?php
/**
 * @var array       $listing
 * @var array       $seeker
 * @var array       $profile
 * @var string      $coverLetter
 * @var array       $checklist
 * @var bool        $cached
 */
?>
<div data-theme="modeiland" class="min-h-screen bg-base-100">

  <header class="bg-primary text-primary-content py-4 px-6">
    <div class="max-w-2xl mx-auto flex items-center justify-between">
      <span class="font-bold text-xl">modeiland</span>
      <a href="<?= site_url("inserate/{$listing['id']}") ?>" class="link link-hover text-primary-content/60 text-sm">
        ← Zurück zum Inserat
      </a>
    </div>
  </header>

  <main class="max-w-2xl mx-auto px-4 py-8 space-y-6">

    <?php if (session()->has('success')): ?>
      <div class="alert alert-success"><span><?= esc(session('success')) ?></span></div>
    <?php endif ?>

    <div>
      <h1 class="text-2xl font-bold text-primary mb-1">Bewerbungspaket</h1>
      <p class="text-slate-500 text-sm">
        <?= esc($listing['location_approx'] ?? "Inserat #{$listing['id']}") ?>
        <?php if ($cached): ?>
          <span class="badge badge-ghost badge-sm ml-2">gespeichert</span>
        <?php else: ?>
          <span class="badge badge-success badge-sm ml-2">neu generiert</span>
        <?php endif ?>
      </p>
    </div>

    <!-- Profile completeness hint -->
    <?php if (empty($profile['name']) || empty($profile['occupation'])): ?>
    <div class="alert bg-base-200 border border-primary/20 text-sm">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <div>
        <p class="font-medium">Profil unvollständig</p>
        <p class="text-slate-500 text-xs mt-0.5">
          Ergänzen Sie Name und Beruf für ein persönlicheres Anschreiben.
          <a href="<?= site_url('seeker/profil') ?>" class="link link-primary">Profil bearbeiten →</a>
        </p>
      </div>
    </div>
    <?php endif ?>

    <!-- Cover letter -->
    <div class="card bg-base-100 shadow border border-base-300">
      <div class="card-body">
        <div class="flex items-center justify-between mb-3">
          <h2 class="card-title text-base">Anschreiben</h2>
          <button onclick="copyLetter()" class="btn btn-ghost btn-xs border border-base-300">
            Kopieren
          </button>
        </div>

        <textarea id="cover-letter-text"
                  class="textarea textarea-bordered w-full font-mono text-sm leading-relaxed"
                  rows="14" readonly><?= esc($coverLetter) ?></textarea>

        <p class="text-xs text-slate-400 mt-2">
          KI-generierter Text — bitte vor dem Versenden prüfen und individuell anpassen.
        </p>
      </div>
    </div>

    <!-- Document checklist -->
    <div class="card bg-base-100 shadow border border-base-300">
      <div class="card-body">
        <h2 class="card-title text-base mb-3">Unterlagen-Checklist</h2>
        <ul class="space-y-2">
          <?php foreach ($checklist as $item): ?>
          <li class="flex items-start gap-3 text-sm">
            <input type="checkbox" class="checkbox checkbox-sm mt-0.5 shrink-0">
            <span><?= esc($item) ?></span>
          </li>
          <?php endforeach ?>
        </ul>
        <p class="text-xs text-slate-400 mt-3">
          Hinweis: Diese Liste dient als Orientierung — kein Rechtsrat.
          Individuelle Anforderungen je Vermieter können abweichen.
        </p>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-wrap gap-3 items-center">
      <form method="post" action="<?= site_url("inserate/{$listing['id']}/bewerben/neu") ?>">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-ghost btn-sm border border-base-300"
                onclick="return confirm('Neues Anschreiben generieren? Das aktuelle wird überschrieben.')">
          Neu generieren
        </button>
      </form>
      <a href="<?= site_url('seeker/profil') ?>" class="btn btn-ghost btn-sm border border-base-300">
        Profil bearbeiten
      </a>
      <a href="<?= site_url("inserate/{$listing['id']}/kontakt") ?>" class="btn btn-primary btn-sm">
        Anbieter kontaktieren →
      </a>
    </div>

  </main>

  <footer class="text-center text-xs text-slate-400 py-6 border-t border-base-200 mt-8">
    <a href="<?= site_url('impressum') ?>" class="link">Impressum</a> ·
    <a href="<?= site_url('datenschutz') ?>" class="link">Datenschutz</a>
  </footer>

</div>

<script>
function copyLetter() {
  var el  = document.getElementById('cover-letter-text');
  el.select();
  el.setSelectionRange(0, 99999);
  navigator.clipboard
    ? navigator.clipboard.writeText(el.value)
    : document.execCommand('copy');
  var btn = event.target;
  btn.textContent = 'Kopiert ✓';
  btn.disabled = true;
  setTimeout(function () { btn.textContent = 'Kopieren'; btn.disabled = false; }, 2500);
}
</script>
