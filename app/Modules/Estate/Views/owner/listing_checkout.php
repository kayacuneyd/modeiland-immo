<?php /** @var array $listing @var string $amountEuro */ ?>
<div data-theme="modeiland" class="min-h-screen bg-base-100 flex flex-col items-center justify-center px-4">

  <div class="card bg-base-100 shadow-lg max-w-md w-full">
    <div class="card-body">

      <h1 class="card-title text-primary text-xl mb-1">Zweites Inserat freischalten</h1>
      <p class="text-slate-500 text-sm mb-4">
        Ihr erstes Inserat war kostenlos. Für jedes weitere Inserat fällt eine einmalige Gebühr an —
        kein Abonnement, keine Provision.
      </p>

      <?php if (session()->has('error')): ?>
        <div class="alert alert-error mb-4"><span><?= esc(session('error')) ?></span></div>
      <?php endif ?>
      <?php if (session()->has('info')): ?>
        <div class="alert alert-info mb-4"><span><?= esc(session('info')) ?></span></div>
      <?php endif ?>

      <div class="bg-base-200 rounded-xl p-4 mb-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="font-medium text-sm">Inserat freischalten</p>
            <p class="text-xs text-slate-400 mt-0.5"><?= esc($listing['location_approx'] ?? "Inserat #{$listing['id']}") ?></p>
          </div>
          <span class="text-2xl font-bold text-primary"><?= esc($amountEuro) ?> €</span>
        </div>
        <p class="text-xs text-slate-400 mt-2">Einmalige Gebühr · inkl. MwSt. · keine weiteren Kosten</p>
      </div>

      <form method="post" action="<?= site_url("owner/listing-checkout/{$listing['id']}/start") ?>">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-primary w-full">
          Jetzt bezahlen — <?= esc($amountEuro) ?> €
        </button>
      </form>

      <div class="flex justify-center gap-4 mt-4 text-xs text-slate-400">
        <span class="flex items-center gap-1">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
          </svg>
          Sichere Zahlung via Stripe
        </span>
        <span>Kein Abo</span>
        <span>Keine Provision</span>
      </div>

      <div class="text-center mt-3">
        <a href="<?= site_url('owner/panel') ?>" class="link link-hover text-xs text-slate-400">Zurück zum Panel</a>
      </div>

    </div>
  </div>

</div>
