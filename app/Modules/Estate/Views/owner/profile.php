<?php /** @var array $owner */ ?>
<div data-theme="modeiland" class="min-h-screen bg-base-100">

  <header class="bg-primary text-primary-content py-4 px-6">
    <div class="max-w-xl mx-auto flex items-center justify-between">
      <span class="font-bold text-xl">modeiland</span>
      <a href="<?= site_url('owner/panel') ?>" class="link link-hover text-primary-content/70 text-sm">
        ← Zurück zum Panel
      </a>
    </div>
  </header>

  <main class="max-w-xl mx-auto px-4 py-8 space-y-6">

    <div>
      <h1 class="text-2xl font-bold text-primary">Mein Profil</h1>
      <p class="text-slate-500 text-sm mt-1">
        Fügen Sie E-Mail und Passwort hinzu, um Ihren Einladungslink zu ersetzen.
        Danach können Sie sich sicher per Magic-Link oder Passwort anmelden.
      </p>
    </div>

    <?php if (session()->has('success')): ?>
      <div class="alert alert-success"><span><?= esc(session('success')) ?></span></div>
    <?php endif ?>
    <?php if (session()->has('errors')): ?>
      <div class="alert alert-error">
        <ul class="list-disc list-inside text-sm">
          <?php foreach (session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach ?>
        </ul>
      </div>
    <?php endif ?>

    <!-- Current status -->
    <div class="bg-base-200 rounded-lg p-4 text-sm space-y-1">
      <div class="flex gap-2">
        <span class="text-slate-500 w-28">Name:</span>
        <span><?= esc($owner['display_name']) ?></span>
      </div>
      <div class="flex gap-2">
        <span class="text-slate-500 w-28">E-Mail:</span>
        <span><?= $owner['email'] ? esc($owner['email']) : '<span class="text-slate-400">nicht hinterlegt</span>' ?></span>
      </div>
      <div class="flex gap-2">
        <span class="text-slate-500 w-28">Anmelde-Methode:</span>
        <span class="badge badge-sm"><?= esc($owner['login_method']) ?></span>
      </div>
    </div>

    <!-- Upgrade form -->
    <form method="post" action="<?= site_url('owner/profil') ?>" class="card bg-base-100 shadow space-y-4 p-6">
      <?= csrf_field() ?>

      <div class="form-control">
        <label class="label">
          <span class="label-text font-medium">E-Mail-Adresse</span>
          <span class="label-text-alt text-slate-400">für Magic-Link Anmeldung</span>
        </label>
        <input type="email" name="email"
               value="<?= old('email', $owner['email'] ?? '') ?>"
               class="input input-bordered" maxlength="200"
               placeholder="ihre@email.de" autocomplete="email">
      </div>

      <div class="form-control">
        <label class="label"><span class="label-text font-medium">Telefon</span></label>
        <input type="text" name="phone"
               value="<?= old('phone', $owner['phone'] ?? '') ?>"
               class="input input-bordered" maxlength="50"
               placeholder="+49 ...">
      </div>

      <div class="divider text-xs text-slate-400">Optional: Passwort setzen</div>

      <div class="form-control">
        <label class="label"><span class="label-text font-medium">Neues Passwort</span></label>
        <input type="password" name="password"
               class="input input-bordered" maxlength="200"
               placeholder="Mindestens 10 Zeichen" autocomplete="new-password">
      </div>

      <div class="form-control">
        <label class="label"><span class="label-text font-medium">Passwort bestätigen</span></label>
        <input type="password" name="password_confirm"
               class="input input-bordered" maxlength="200"
               autocomplete="new-password">
      </div>

      <div class="bg-warning/10 rounded-lg p-3 text-sm text-slate-600 border border-warning/30">
        ⚠️ Nach dem Speichern wird Ihr <strong>Einladungslink deaktiviert</strong>.
        Sie melden sich künftig per Magic-Link oder Passwort an.
      </div>

      <button type="submit" class="btn btn-primary w-full" style="min-height:44px">
        Profil speichern
      </button>
    </form>

    <div class="text-center">
      <a href="<?= site_url('owner/logout') ?>" class="link link-error text-sm">
        Abmelden
      </a>
    </div>

  </main>
</div>
