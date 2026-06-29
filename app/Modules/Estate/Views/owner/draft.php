<?php
/** @var array $listing @var array $owner @var array $images @var array|null $existingConsent
 *  @var bool $alreadyApproved @var string $consentVersion
 */
$w = $listing['warmmiete']   ? number_format($listing['warmmiete']   / 100, 0, ',', '.') . ' €' : null;
$k = $listing['kaltmiete']   ? number_format($listing['kaltmiete']   / 100, 0, ',', '.') . ' €' : null;
$n = $listing['nebenkosten'] ? number_format($listing['nebenkosten'] / 100, 0, ',', '.') . ' €' : null;
$d = $listing['deposit']     ? number_format($listing['deposit']     / 100, 0, ',', '.') . ' €' : null;
?>
<div data-theme="modeiland" class="min-h-screen bg-base-100">

  <!-- Header -->
  <header class="bg-primary text-primary-content py-4 px-6">
    <div class="max-w-2xl mx-auto flex items-center justify-between">
      <span class="font-bold text-xl tracking-tight">modeiland</span>
      <div class="flex items-center gap-3 text-sm">
        <span class="opacity-70">Anbieter-Panel</span>
        <a href="<?= site_url('owner/panel') ?>"
           class="link link-hover text-primary-content/60 text-xs">Panel</a>
        <a href="<?= site_url('owner/logout') ?>"
           class="link link-hover text-primary-content/60 text-xs">Abmelden</a>
      </div>
    </div>
  </header>

  <main class="max-w-2xl mx-auto px-4 py-8 space-y-6">

    <?php if (session()->has('success')): ?>
      <div class="alert alert-success" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span><?= esc(session('success')) ?></span>
      </div>
    <?php endif ?>
    <?php if (session()->has('error')): ?>
      <div class="alert alert-error" role="alert"><span><?= esc(session('error')) ?></span></div>
    <?php endif ?>

    <?php if ($alreadyApproved): ?>
      <div class="alert alert-success" role="alert">
        <span>Ihr Inserat ist aktiv und online sichtbar.</span>
        <a href="<?= site_url("inserate/{$listing['id']}") ?>" class="btn btn-xs btn-ghost ml-auto" target="_blank">
          Inserat ansehen →
        </a>
      </div>
    <?php endif ?>

    <!-- Image gallery / placeholder -->
    <div class="rounded-xl overflow-hidden bg-base-200 aspect-video flex items-center justify-center">
      <?php if (! empty($images)): ?>
        <img src="<?= esc($images[0]['path']) ?>" alt="Foto" class="object-cover w-full h-full">
      <?php else: ?>
        <div class="text-center text-slate-400 p-8">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4-4a3 3 0 014 0l4 4M14 12l2-2a3 3 0 014 0l2 2"/></svg>
          <p class="text-sm">Fotos werden nach Freigabe hochgeladen.</p>
        </div>
      <?php endif ?>
    </div>

    <!-- Fact strip -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
      <?php
      $facts = [
        ['label' => 'Warmmiete',   'value' => $w,  'mono' => true],
        ['label' => 'Kaltmiete',   'value' => $k,  'mono' => true],
        ['label' => 'Nebenkosten', 'value' => $n,  'mono' => true],
        ['label' => 'Kaution',     'value' => $d,  'mono' => true],
        ['label' => 'Zimmer',      'value' => $listing['rooms'] ? $listing['rooms'] . ' Zi.' : null],
        ['label' => 'Fläche',      'value' => $listing['m2'] ? $listing['m2'] . ' m²' : null],
        ['label' => 'Verfügbar',   'value' => esc($listing['available_from'] ?? null)],
        ['label' => 'Standort',    'value' => esc($listing['location_approx'] ?? null)],
      ];
      foreach ($facts as $f):
        if (! $f['value']) continue;
      ?>
        <div class="bg-base-200 rounded-lg p-3">
          <div class="text-xs text-slate-500 mb-0.5"><?= $f['label'] ?></div>
          <div class="font-bold <?= ! empty($f['mono']) ? 'font-mono' : '' ?> text-primary">
            <?= $f['value'] ?>
          </div>
        </div>
      <?php endforeach ?>
    </div>

    <!-- AI description -->
    <?php if ($listing['ai_description']): ?>
    <div>
      <h2 class="text-lg font-bold text-primary mb-2">KI-generierte Beschreibung</h2>
      <p class="text-base-content/80 leading-relaxed text-base">
        <?= nl2br(esc($listing['ai_description'])) ?>
      </p>
    </div>
    <?php endif ?>

    <!-- ─── Consent form (yasal kritik ekran) ─────────────────────────────────── -->
    <?php if (! $alreadyApproved): ?>
    <div id="consent-card"
         class="card bg-base-100 shadow-xl border-2 border-primary/20"
         role="dialog" aria-modal="true" aria-labelledby="consent-title"
         tabindex="-1">
      <div class="card-body">

        <h2 id="consent-title" class="card-title text-primary text-lg">
          Einwilligung zur Veröffentlichung
        </h2>
        <p class="text-sm text-slate-600 mb-4">
          Bitte lesen und bestätigen Sie jede Erklärung einzeln.
          Mit Pflicht gekennzeichnete Felder (✱) sind erforderlich.
        </p>

        <form id="consent-form"
              method="post"
              action="<?= site_url("owner/draft/{$listing['id']}") ?>"
              novalidate>
          <?= csrf_field() ?>

          <fieldset class="space-y-4 mb-6">
            <legend class="sr-only">Einwilligungserklärungen</legend>

            <!-- 1. Eigentümer/Berechtigt -->
            <label class="flex gap-3 items-start cursor-pointer group">
              <input type="checkbox" name="consent_owner_auth" value="1"
                     class="consent-required checkbox checkbox-primary mt-0.5 min-w-[1.25rem] min-h-[1.25rem]"
                     aria-required="true">
              <span class="text-sm leading-snug">
                <span class="font-medium">✱ Ich bin Eigentümer oder bevollmächtigter Vertreter</span>
                dieser Immobilie und bin berechtigt, das Inserat zu veröffentlichen.
              </span>
            </label>

            <!-- 2. Veröffentlichung -->
            <label class="flex gap-3 items-start cursor-pointer group">
              <input type="checkbox" name="consent_publish" value="1"
                     class="consent-required checkbox checkbox-primary mt-0.5 min-w-[1.25rem] min-h-[1.25rem]"
                     aria-required="true">
              <span class="text-sm leading-snug">
                <span class="font-medium">✱ Ich stimme der Veröffentlichung</span>
                der oben angezeigten Inserat-Informationen auf der modeiland-Plattform zu.
              </span>
            </label>

            <!-- 3. KI-Neuformulierung -->
            <label class="flex gap-3 items-start cursor-pointer group">
              <input type="checkbox" name="consent_ai_rewrite" value="1"
                     class="consent-required checkbox checkbox-primary mt-0.5 min-w-[1.25rem] min-h-[1.25rem]"
                     aria-required="true">
              <span class="text-sm leading-snug">
                <span class="font-medium">✱ Ich akzeptiere die KI-gestützte Neuformulierung</span>
                meines Inseratstextes. Der Originaltext wird ausschließlich intern zur
                Dokumentation gespeichert und nicht veröffentlicht.
              </span>
            </label>

            <div class="divider my-2 text-xs text-slate-400">Optional</div>

            <!-- 4. Fotos -->
            <label class="flex gap-3 items-start cursor-pointer group">
              <input type="checkbox" name="consent_photos" value="1"
                     class="consent-optional checkbox checkbox-primary mt-0.5 min-w-[1.25rem] min-h-[1.25rem]">
              <span class="text-sm leading-snug">
                Ich erlaube die Verwendung und Anzeige von Fotos dieser Immobilie
                auf der Plattform.
                <span class="text-slate-400">(optional)</span>
              </span>
            </label>

            <!-- 5. Plattform-Kommunikation -->
            <label class="flex gap-3 items-start cursor-pointer group">
              <input type="checkbox" name="consent_platform_contact" value="1"
                     class="consent-optional checkbox checkbox-primary mt-0.5 min-w-[1.25rem] min-h-[1.25rem]">
              <span class="text-sm leading-snug">
                Ich stimme zu, dass Interessenten mich über die plattforminterne
                Nachrichtenfunktion kontaktieren können.
                <span class="text-slate-400">(optional — empfohlen)</span>
              </span>
            </label>

            <!-- 6. Direktkontakt -->
            <label class="flex gap-3 items-start cursor-pointer group">
              <input type="checkbox" name="consent_direct_contact" value="1"
                     class="consent-optional checkbox checkbox-primary mt-0.5 min-w-[1.25rem] min-h-[1.25rem]">
              <span class="text-sm leading-snug">
                Ich erlaube die Weitergabe meiner Kontaktdaten an Abonnenten nach
                erfolgter Kontaktaufnahme.
                <span class="text-slate-400">(optional)</span>
              </span>
            </label>

          </fieldset>

          <!-- Rights reminder -->
          <div class="bg-success/10 border border-success/30 rounded-lg p-3 text-sm text-slate-600 mb-4">
            <span class="font-medium">Ihre Rechte:</span>
            Sie können Ihr Inserat jederzeit bearbeiten, pausieren oder entfernen.
            Diese Einwilligungen sind jederzeit widerrufbar.
          </div>

          <!-- Consent meta -->
          <p class="text-xs text-slate-400 mb-4">
            Einwilligungsversion: <?= esc($consentVersion) ?> ·
            Datum/Zeit, IP-Adresse und Browser-Kennung werden protokolliert (DSGVO Art. 7).
          </p>

          <!-- Submit — disabled until all required boxes checked -->
          <button type="submit" id="consent-submit"
                  class="btn btn-primary w-full disabled:opacity-40 disabled:cursor-not-allowed"
                  style="min-height:44px"
                  disabled aria-disabled="true">
            Inserat freigeben &amp; online stellen
          </button>

        </form>
      </div>
    </div>

    <script>
    (function () {
      var required = document.querySelectorAll('.consent-required');
      var submitBtn = document.getElementById('consent-submit');

      function checkAll() {
        var allChecked = Array.from(required).every(function (cb) { return cb.checked; });
        submitBtn.disabled = !allChecked;
        submitBtn.setAttribute('aria-disabled', allChecked ? 'false' : 'true');
      }

      required.forEach(function (cb) { cb.addEventListener('change', checkAll); });

      // Focus trap: keep keyboard navigation within the consent card
      (function focusTrap() {
        var card    = document.getElementById('consent-card');
        var focusable = 'input, button, a[href], [tabindex]:not([tabindex="-1"])';
        card.addEventListener('keydown', function (e) {
          if (e.key !== 'Tab') return;
          var els   = Array.from(card.querySelectorAll(focusable)).filter(function(el) {
            return !el.disabled && el.offsetParent !== null;
          });
          if (els.length === 0) return;
          var first = els[0], last = els[els.length - 1];
          if (e.shiftKey) {
            if (document.activeElement === first) { e.preventDefault(); last.focus(); }
          } else {
            if (document.activeElement === last)  { e.preventDefault(); first.focus(); }
          }
        });
        // Auto-focus first required checkbox on page load
        if (required.length > 0) required[0].focus();
      })();

      // Respect prefers-reduced-motion (no scroll animation)
      var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      if (!prefersReduced) {
        document.getElementById('consent-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    })();
    </script>

    <?php endif ?>

    <div class="text-xs text-center text-slate-400 pb-4">
      <a href="<?= site_url('impressum') ?>" class="link">Impressum</a> ·
      <a href="<?= site_url('datenschutz') ?>" class="link">Datenschutz</a> ·
      <a href="<?= site_url('agb') ?>" class="link">AGB</a>
    </div>

  </main>
</div>
