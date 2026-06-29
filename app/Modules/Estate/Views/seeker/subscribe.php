<?php /** @var bool $billingEnabled @var int $seekerPriceCents */ ?>

<div data-theme="modeiland" class="min-h-screen bg-base-100">

  <div class="bg-primary text-primary-content px-4 py-3">
    <div class="max-w-xl mx-auto">
      <a href="<?= site_url('inserate') ?>" class="link link-hover text-primary-content/70 text-sm">
        ← Zurück zur Suche
      </a>
    </div>
  </div>

  <main class="max-w-xl mx-auto px-4 py-10 space-y-8">

    <div class="text-center">
      <h1 class="text-3xl font-bold text-primary mb-2">modeiland Abonnement</h1>
      <p class="text-slate-500">Direktkontakt zu Anbietern — ohne Makler</p>
    </div>

    <?php if (session()->has('info')): ?>
      <div class="alert alert-info"><span><?= esc(session('info')) ?></span></div>
    <?php endif ?>

    <!-- Pricing card -->
    <div class="card bg-primary text-primary-content shadow-xl">
      <div class="card-body items-center text-center py-10">
        <div class="text-5xl font-bold font-mono mb-1">
          <?= number_format($seekerPriceCents / 100, 0, ',', '.') ?> €
        </div>
        <div class="text-primary-content/60 text-sm mb-6">pro Monat · jederzeit kündbar</div>

        <ul class="text-sm space-y-2 mb-8 text-left w-full max-w-xs">
          <?php
          $features = [
            'Unbegrenzt Nachrichten an Anbieter',
            'Direkter Kontakt — kein Makler',
            'Alle Inserate ohne Einschränkung',
            'Plattforminterne Kommunikation',
          ];
          foreach ($features as $f): ?>
            <li class="flex gap-2 items-start">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-accent shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              <?= esc($f) ?>
            </li>
          <?php endforeach ?>
        </ul>

        <?php if ($billingEnabled): ?>
          <form method="post" action="<?= site_url('abonnement') ?>" class="w-full">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-accent w-full min-h-[44px] text-base">
              Jetzt abonnieren
            </button>
          </form>
        <?php else: ?>
          <div class="alert alert-success text-sm">
            <span>Beta-Phase: Alle Funktionen kostenlos verfügbar. Kein Abonnement nötig.</span>
          </div>
          <a href="<?= site_url('inserate') ?>" class="btn btn-accent mt-4 min-h-[44px]">
            Jetzt Inserate durchsuchen
          </a>
        <?php endif ?>
      </div>
    </div>

    <p class="text-xs text-center text-slate-400">
      Der Abonnementpreis ist ein Software-Nutzungsentgelt und keine Maklergebühr.
      Kein Erfolgshonorar — Sie zahlen für den Plattformzugang, nicht für eine Vermittlung.
    </p>

    <div class="text-center text-xs text-slate-400">
      <a href="<?= site_url('agb') ?>" class="link">AGB</a> ·
      <a href="<?= site_url('datenschutz') ?>" class="link">Datenschutz</a> ·
      <a href="<?= site_url('impressum') ?>" class="link">Impressum</a>
    </div>

  </main>
</div>
