<?php /** @var string $reason */ ?>
<div data-theme="modeiland" class="min-h-screen bg-base-100 flex items-center justify-center px-4">
  <div class="max-w-md w-full text-center space-y-6">

    <div class="text-6xl">🔗</div>

    <h1 class="text-2xl font-bold text-primary">Dieser Link ist nicht mehr gültig</h1>

    <?php if ($reason === 'invalid'): ?>
      <p class="text-slate-600 leading-relaxed">
        Der Einladungslink wurde nicht gefunden, ist abgelaufen oder wurde bereits verwendet.
      </p>
      <p class="text-sm text-slate-400">
        Mögliche Ursachen: Der Link wurde mehrfach weitergeleitet, die Frist ist abgelaufen
        (60 Tage), oder Sie haben Ihr Konto bereits eingerichtet.
      </p>
    <?php endif ?>

    <div class="space-y-3 pt-2">
      <p class="text-sm text-slate-500">
        Falls Sie sich bereits registriert haben, können Sie sich per Magic-Link anmelden:
      </p>
      <a href="<?= site_url('owner/login') ?>"
         class="btn btn-primary w-full" style="min-height:44px">
        Magic-Link anfordern
      </a>
      <p class="text-xs text-slate-400">
        Oder wenden Sie sich an den Support, um einen neuen Einladungslink zu erhalten.
      </p>
    </div>

    <div class="text-xs text-slate-400">
      <a href="<?= site_url('impressum') ?>" class="link">Impressum</a> ·
      <a href="<?= site_url('datenschutz') ?>" class="link">Datenschutz</a>
    </div>

  </div>
</div>
