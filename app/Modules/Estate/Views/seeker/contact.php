<?php
/** @var array $listing @var array|null $seeker @var bool $canSend @var bool $billing */
$w = $listing['warmmiete'] ? number_format($listing['warmmiete'] / 100, 0, ',', '.') . ' €/Monat' : null;
?>
<div data-theme="modeiland" class="min-h-screen bg-base-100">

  <div class="bg-primary text-primary-content px-4 py-3">
    <div class="max-w-xl mx-auto flex items-center gap-3">
      <a href="<?= site_url("inserate/{$listing['id']}") ?>" class="link link-hover text-primary-content/70 text-sm">
        ← Zurück zum Inserat
      </a>
    </div>
  </div>

  <main class="max-w-xl mx-auto px-4 py-8 space-y-6">

    <h1 class="text-2xl font-bold text-primary">Anbieter kontaktieren</h1>
    <div class="text-sm text-slate-500">
      <?= esc($listing['location_approx'] ?? 'Inserat') ?>
      <?php if ($w): ?> · <span class="font-mono font-bold text-primary"><?= $w ?></span><?php endif ?>
    </div>

    <?php if (session()->has('error')): ?>
      <div class="alert alert-error"><span><?= esc(session('error')) ?></span></div>
    <?php endif ?>
    <?php if (session()->has('info')): ?>
      <div class="alert alert-info"><span><?= esc(session('info')) ?></span></div>
    <?php endif ?>

    <?php if (! $canSend && $billing): ?>
      <!-- Paywall -->
      <div class="card bg-primary text-primary-content shadow">
        <div class="card-body items-center text-center py-8">
          <div class="text-4xl mb-2">🔒</div>
          <h2 class="card-title text-lg">Abonnement erforderlich</h2>
          <p class="text-primary-content/70 text-sm max-w-xs">
            Um Nachrichten an Anbieter zu senden, benötigen Sie ein modeiland-Abonnement.
          </p>
          <a href="<?= site_url('abonnement') ?>" class="btn btn-accent mt-3">
            Jetzt abonnieren
          </a>
          <p class="text-xs text-primary-content/50 mt-2">Ab 5 €/Monat · Jederzeit kündbar</p>
        </div>
      </div>
    <?php else: ?>
      <!-- Message form (trial mode OR subscribed) -->
      <?php if (! $billing): ?>
        <div class="alert alert-info text-sm">
          <span>Beta-Phase: Nachrichten kostenlos senden.</span>
        </div>
      <?php endif ?>

      <form method="post" action="<?= site_url("inserate/{$listing['id']}/kontakt") ?>"
            class="card bg-base-100 shadow space-y-4 p-6">
        <?= csrf_field() ?>

        <div class="form-control">
          <label class="label"><span class="label-text font-medium">Ihre E-Mail-Adresse *</span></label>
          <input type="email" name="email"
                 value="<?= esc(old('email', $seeker['email'] ?? '')) ?>"
                 class="input input-bordered" required maxlength="200"
                 placeholder="max@beispiel.de">
        </div>

        <div class="form-control">
          <label class="label">
            <span class="label-text font-medium">Ihre Nachricht *</span>
            <span class="label-text-alt text-slate-400">max. 2000 Zeichen</span>
          </label>
          <textarea name="body" class="textarea textarea-bordered" rows="5" required
                    maxlength="2000"
                    placeholder="Guten Tag, ich interessiere mich für Ihre Wohnung..."><?= esc(old('body')) ?></textarea>
        </div>

        <p class="text-xs text-slate-400">
          Mit dem Absenden stimmen Sie zu, dass Ihre Nachricht über die modeiland-Plattform
          an den Anbieter weitergeleitet wird. Bitte geben Sie keine sensiblen persönlichen
          Daten in der Nachricht an.
        </p>

        <button type="submit" class="btn btn-accent w-full" style="min-height: 44px">
          Nachricht senden
        </button>
      </form>
    <?php endif ?>

  </main>

  <footer class="text-center text-xs text-slate-400 py-6 border-t border-base-200">
    <a href="<?= site_url('impressum') ?>" class="link">Impressum</a> ·
    <a href="<?= site_url('datenschutz') ?>" class="link">Datenschutz</a> ·
    <a href="<?= site_url('agb') ?>" class="link">AGB</a>
  </footer>

</div>
