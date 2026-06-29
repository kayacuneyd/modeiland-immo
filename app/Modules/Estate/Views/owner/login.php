<div data-theme="modeiland" class="min-h-screen bg-base-100 flex items-center justify-center px-4">
  <div class="max-w-sm w-full space-y-6">

    <div class="text-center">
      <a href="<?= site_url('inserate') ?>" class="font-bold text-2xl text-primary tracking-tight">modeiland</a>
      <p class="text-slate-500 text-sm mt-1">Anbieter-Anmeldung</p>
    </div>

    <?php if (session()->has('info')): ?>
      <div class="alert alert-info text-sm"><span><?= esc(session('info')) ?></span></div>
    <?php endif ?>
    <?php if (session()->has('success')): ?>
      <div class="alert alert-success text-sm"><span><?= esc(session('success')) ?></span></div>
    <?php endif ?>
    <?php if (session()->has('error')): ?>
      <div class="alert alert-error text-sm"><span><?= esc(session('error')) ?></span></div>
    <?php endif ?>

    <div class="card bg-base-100 shadow-lg">
      <div class="card-body">
        <h1 class="card-title text-primary text-lg mb-1">Magic-Link anfordern</h1>
        <p class="text-sm text-slate-500 mb-4">
          Geben Sie Ihre E-Mail-Adresse ein. Wir senden Ihnen einen sicheren
          Anmelde-Link — kein Passwort nötig.
        </p>

        <form method="post" action="<?= site_url('owner/login') ?>" class="space-y-4">
          <?= csrf_field() ?>

          <div class="form-control">
            <label class="label"><span class="label-text font-medium">E-Mail-Adresse</span></label>
            <input type="email" name="email" value="<?= old('email') ?>"
                   class="input input-bordered" required maxlength="200"
                   placeholder="ihre@email.de" autocomplete="email" autofocus>
          </div>

          <button type="submit" class="btn btn-primary w-full" style="min-height:44px">
            Magic-Link senden
          </button>
        </form>

        <p class="text-xs text-slate-400 text-center mt-3">
          Nur für bereits eingeladene Anbieter. Noch kein Konto?
          Wenden Sie sich an den Support.
        </p>
      </div>
    </div>

    <div class="text-xs text-center text-slate-400">
      <a href="<?= site_url('impressum') ?>" class="link">Impressum</a> ·
      <a href="<?= site_url('datenschutz') ?>" class="link">Datenschutz</a>
    </div>

  </div>
</div>
