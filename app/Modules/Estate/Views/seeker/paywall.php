<?php
/** @var bool $billingEnabled @var int $seekerPriceCents @var bool $alreadyActive @var array|null $seeker */
$priceEuro = number_format($seekerPriceCents / 100, 0, ',', '.');
?>
<div data-theme="modeiland" class="min-h-screen bg-base-100">

  <!-- Header -->
  <header class="bg-primary text-primary-content py-4 px-6">
    <div class="max-w-4xl mx-auto flex items-center justify-between">
      <a href="<?= site_url('inserate') ?>" class="font-bold text-xl">modeiland</a>
      <span class="text-sm opacity-70">Maklerfrei wohnen</span>
    </div>
  </header>

  <main class="max-w-3xl mx-auto px-4 py-12">

    <?php if (session()->has('error')): ?>
      <div class="alert alert-error mb-6"><span><?= esc(session('error')) ?></span></div>
    <?php endif ?>

    <?php if ($alreadyActive): ?>
      <!-- Already subscribed -->
      <div class="text-center py-16">
        <div class="text-5xl mb-4">✓</div>
        <h1 class="text-2xl font-bold text-primary mb-2">Ihr Zugang ist aktiv</h1>
        <p class="text-slate-500 mb-6">Sie haben bereits ein aktives Abonnement.</p>
        <a href="<?= site_url('seeker/panel') ?>" class="btn btn-primary">Zum Panel</a>
      </div>
    <?php else: ?>

      <!-- Value header -->
      <div class="text-center mb-10">
        <p class="text-accent font-semibold text-sm uppercase tracking-widest mb-2">Kein Makler. Keine Provision.</p>
        <h1 class="text-3xl font-bold text-primary mb-3">
          Direkt mit Anbietern in Kontakt treten
        </h1>
        <p class="text-slate-500 max-w-xl mx-auto">
          Schreiben Sie Anbieter direkt an, erhalten Sie automatisch erstellte Bewerbungsunterlagen
          und sparen Sie wertvolle Zeit bei der Wohnungssuche.
        </p>
      </div>

      <!-- Tier comparison -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10">

        <!-- Free tier -->
        <div class="card border border-base-300 bg-base-100">
          <div class="card-body">
            <h2 class="card-title text-lg text-slate-500">Kostenlos</h2>
            <p class="text-3xl font-bold text-slate-400 mb-4">0 €</p>
            <ul class="space-y-2 text-sm text-slate-600">
              <li class="flex items-center gap-2">
                <span class="text-success">✓</span> Alle Inserate durchsuchen
              </li>
              <li class="flex items-center gap-2">
                <span class="text-success">✓</span> Detailseiten anzeigen
              </li>
              <li class="flex items-center gap-2 opacity-40">
                <span>✗</span> Anbieter kontaktieren
              </li>
              <li class="flex items-center gap-2 opacity-40">
                <span>✗</span> Suchalarme einrichten
              </li>
              <li class="flex items-center gap-2 opacity-40">
                <span>✗</span> Bewerbungsunterlagen
              </li>
            </ul>
          </div>
        </div>

        <!-- Plus tier (recommended) -->
        <div class="card border-2 border-primary bg-primary/5 relative">
          <div class="absolute -top-3 left-1/2 -translate-x-1/2">
            <span class="badge badge-primary px-4 py-3 text-xs font-bold">EMPFOHLEN</span>
          </div>
          <div class="card-body">
            <h2 class="card-title text-lg text-primary">Plus</h2>
            <div class="mb-4">
              <span class="text-3xl font-bold text-primary"><?= $priceEuro ?> €</span>
              <span class="text-slate-400 text-sm">/Monat</span>
              <p class="text-xs text-slate-400 mt-0.5">inkl. MwSt. · jederzeit kündbar</p>
            </div>
            <ul class="space-y-2 text-sm">
              <li class="flex items-center gap-2">
                <span class="text-success font-bold">✓</span> Alle Inserate durchsuchen
              </li>
              <li class="flex items-center gap-2">
                <span class="text-success font-bold">✓</span> Detailseiten anzeigen
              </li>
              <li class="flex items-center gap-2 font-medium">
                <span class="text-success font-bold">✓</span> Anbieter direkt kontaktieren
              </li>
              <li class="flex items-center gap-2 font-medium">
                <span class="text-success font-bold">✓</span> Suchalarme per E-Mail
              </li>
              <li class="flex items-center gap-2 font-medium">
                <span class="text-success font-bold">✓</span> Automatische Bewerbungsunterlagen
                <span class="badge badge-xs badge-accent">Bald</span>
              </li>
            </ul>

            <!-- Checkout form -->
            <form method="post" action="<?= site_url('abonnieren/checkout') ?>" class="mt-5">
              <?= csrf_field() ?>
              <?php if ($seeker): ?>
                <input type="hidden" name="email" value="<?= esc($seeker['email']) ?>">
                <button type="submit" class="btn btn-primary w-full">
                  Jetzt freischalten
                  <?php if (! $billingEnabled): ?>
                    <span class="badge badge-accent badge-sm ml-1">Kostenloser Test</span>
                  <?php endif ?>
                </button>
              <?php else: ?>
                <div class="form-control mb-3">
                  <input type="email" name="email" required placeholder="Ihre E-Mail-Adresse"
                         class="input input-bordered input-sm w-full"
                         value="<?= esc(old('email')) ?>">
                </div>
                <button type="submit" class="btn btn-primary w-full">
                  Zugang freischalten
                  <?php if (! $billingEnabled): ?>
                    <span class="badge badge-accent badge-sm ml-1">Kostenloser Test</span>
                  <?php endif ?>
                </button>
              <?php endif ?>
            </form>
          </div>
        </div>

      </div>

      <!-- Trust signals -->
      <div class="flex flex-wrap justify-center gap-6 text-xs text-slate-400 mb-8">
        <span class="flex items-center gap-1">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
          </svg>
          Sichere Zahlung via Stripe
        </span>
        <span class="flex items-center gap-1">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Keine versteckte Provision
        </span>
        <span class="flex items-center gap-1">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Jederzeit kündbar
        </span>
        <span class="flex items-center gap-1">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
          </svg>
          Keine Maklergebühr
        </span>
      </div>

    <?php endif ?>

  </main>

  <footer class="text-center text-xs text-slate-400 py-6 border-t border-base-200">
    <a href="<?= site_url('impressum') ?>" class="link">Impressum</a> ·
    <a href="<?= site_url('datenschutz') ?>" class="link">Datenschutz</a> ·
    <a href="<?= site_url('agb') ?>" class="link">AGB</a>
  </footer>

</div>
