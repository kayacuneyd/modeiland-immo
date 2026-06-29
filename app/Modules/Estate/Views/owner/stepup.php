<?php /** @var array $owner @var string $action @var bool $hasPassword @var bool $hasEmail @var bool $otpSent */ ?>
<div data-theme="modeiland" class="min-h-screen bg-base-100 flex items-center justify-center px-4">
  <div class="max-w-sm w-full space-y-6"
       role="dialog" aria-modal="true" aria-labelledby="stepup-title">

    <div class="text-center">
      <div class="text-4xl mb-2">🔐</div>
      <h1 id="stepup-title" class="text-xl font-bold text-primary">Identität bestätigen</h1>
      <p class="text-slate-500 text-sm mt-1">
        Um <strong><?= esc($action) ?></strong> durchzuführen,
        bestätigen Sie bitte Ihre Identität.
      </p>
    </div>

    <?php if (session()->has('error')): ?>
      <div class="alert alert-error text-sm"><span><?= esc(session('error')) ?></span></div>
    <?php endif ?>

    <div class="card bg-base-100 shadow-lg">
      <div class="card-body">

        <?php if ($hasPassword): ?>
          <!-- Password verification -->
          <p class="text-sm text-slate-500 mb-3">Geben Sie Ihr Passwort ein:</p>
          <form method="post" action="<?= site_url('owner/stepup') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="method" value="password">
            <div class="form-control mb-4">
              <input type="password" name="credential"
                     class="input input-bordered" required
                     placeholder="Ihr Passwort"
                     autocomplete="current-password" autofocus
                     style="min-height:44px">
            </div>
            <button type="submit" class="btn btn-primary w-full" style="min-height:44px">
              Bestätigen
            </button>
          </form>

        <?php elseif ($hasEmail): ?>
          <!-- OTP verification -->
          <?php if ($otpSent): ?>
            <p class="text-sm text-slate-500 mb-3">
              Wir haben einen 6-stelligen Code an
              <strong><?= esc(substr($owner['email'], 0, 3)) ?>***</strong> gesendet.
              Der Code ist 10 Minuten gültig.
            </p>
          <?php else: ?>
            <div class="alert alert-warning text-sm mb-3">
              <span>Code konnte nicht gesendet werden. Bitte Seite neu laden.</span>
            </div>
          <?php endif ?>
          <form method="post" action="<?= site_url('owner/stepup') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="method" value="otp">
            <div class="form-control mb-4">
              <input type="text" name="credential" inputmode="numeric" pattern="[0-9]{6}"
                     maxlength="6" class="input input-bordered text-center font-mono text-2xl tracking-widest"
                     placeholder="000000" required autofocus
                     autocomplete="one-time-code"
                     style="min-height:44px; letter-spacing:.4em">
            </div>
            <button type="submit" class="btn btn-primary w-full" style="min-height:44px">
              Code bestätigen
            </button>
          </form>

        <?php else: ?>
          <!-- No email, no password — prompt to add email first -->
          <div class="alert alert-warning text-sm">
            <span>
              Für diese Aktion benötigen Sie eine hinterlegte E-Mail-Adresse oder ein Passwort.
              Bitte vervollständigen Sie zuerst Ihr Profil.
            </span>
          </div>
          <a href="<?= site_url('owner/profil') ?>" class="btn btn-primary w-full mt-3" style="min-height:44px">
            Profil vervollständigen
          </a>
        <?php endif ?>

      </div>
    </div>

    <div class="text-center">
      <a href="<?= site_url('owner/panel') ?>" class="link text-sm text-slate-400">
        Abbrechen — zurück zum Panel
      </a>
    </div>

  </div>
</div>
