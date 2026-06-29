<div data-theme="modeiland" class="min-h-screen bg-base-100 flex items-center justify-center px-4">
  <div class="max-w-sm w-full text-center space-y-6">

    <div class="text-6xl">📬</div>
    <h1 class="text-2xl font-bold text-primary">E-Mail wurde gesendet</h1>
    <p class="text-slate-600 leading-relaxed">
      Falls ein Konto mit dieser E-Mail-Adresse vorhanden ist, haben wir Ihnen
      einen Anmelde-Link zugeschickt.
    </p>
    <p class="text-sm text-slate-400">
      Der Link ist <strong>15 Minuten</strong> gültig und kann nur einmal verwendet werden.
      Bitte prüfen Sie auch Ihren Spam-Ordner.
    </p>

    <a href="<?= site_url('owner/login') ?>" class="btn btn-ghost btn-sm">
      ← Neue Anfrage stellen
    </a>

    <div class="text-xs text-slate-400">
      <a href="<?= site_url('impressum') ?>" class="link">Impressum</a> ·
      <a href="<?= site_url('datenschutz') ?>" class="link">Datenschutz</a>
    </div>

  </div>
</div>
