<?php /** @var array $owner @var array $listings @var string $outreachTemplate */ ?>

<div data-theme="modeiland">
<div class="p-6 max-w-4xl mx-auto space-y-6">

  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-primary"><?= esc($owner['display_name']) ?></h1>
      <span class="badge <?= $owner['status'] === 'active' ? 'badge-success' : 'badge-warning' ?> mt-1">
        <?= esc($owner['status']) ?>
      </span>
    </div>
    <a href="<?= site_url('admin/estate/owners') ?>" class="btn btn-ghost btn-sm">← Zurück</a>
  </div>

  <?php if (session()->has('success')): ?>
    <div class="alert alert-success"><span><?= esc(session('success')) ?></span></div>
  <?php endif ?>

  <!-- Owner details -->
  <div class="card bg-base-100 shadow">
    <div class="card-body">
      <h2 class="card-title text-lg">Kontaktdaten</h2>
      <dl class="grid grid-cols-2 gap-2 text-sm">
        <dt class="font-medium text-slate-500">E-Mail</dt>
        <dd><?= esc($owner['email'] ?? '—') ?></dd>
        <dt class="font-medium text-slate-500">Telefon</dt>
        <dd><?= esc($owner['phone'] ?? '—') ?></dd>
        <dt class="font-medium text-slate-500">Quelle</dt>
        <dd>
          <?php if ($owner['source_url']): ?>
            <a href="<?= esc($owner['source_url']) ?>" target="_blank" class="link link-primary break-all">
              <?= esc($owner['source_url']) ?>
            </a>
          <?php else: ?>—<?php endif ?>
        </dd>
        <dt class="font-medium text-slate-500">Angelegt</dt>
        <dd><?= esc($owner['created_at']) ?></dd>
      </dl>
    </div>
  </div>

  <!-- Invite link -->
  <div class="card bg-base-100 shadow">
    <div class="card-body">
      <h2 class="card-title text-base">Einladungslink</h2>

      <?php if (session()->has('invite_url')): ?>
        <div class="alert alert-warning text-sm mb-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.929 4.929l14.142 14.142M4.929 19.071l14.142-14.142"/></svg>
          <div>
            <p class="font-medium">Nur einmal sichtbar — jetzt sichern!</p>
            <p class="text-xs">Dieser Link wird nach dem Verlassen der Seite nicht mehr angezeigt.</p>
          </div>
        </div>
        <div class="relative">
          <input id="invite-url-field" type="text" readonly
                 value="<?= esc(session('invite_url')) ?>"
                 class="input input-bordered w-full font-mono text-xs pr-24">
          <button onclick="copyInviteUrl()" class="btn btn-xs btn-accent absolute right-2 top-1/2 -translate-y-1/2">
            Kopieren
          </button>
        </div>
        <p class="text-xs text-slate-400 mt-1">
          Gültig: 60 Tage. Nur über einen sicheren Kanal senden (WhatsApp, E-Mail mit HTTPS, o.Ä.).
        </p>
      <?php else: ?>
        <p class="text-sm text-slate-500 mb-3">
          Generieren Sie einen sicheren Einladungslink für diesen Anbieter.
          Der Link gilt 60 Tage und erstellt beim ersten Öffnen eine Sitzung.
        </p>
        <form method="post" action="<?= site_url("admin/estate/owners/{$owner['id']}/generate-invite") ?>"
              onsubmit="return confirm('Neuen Einladungslink generieren? Der bisherige Link wird ungültig.')">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-primary btn-sm">
            🔗 Einladungslink generieren
          </button>
        </form>
      <?php endif ?>
    </div>
  </div>

  <!-- Outreach template -->
  <div class="card bg-base-100 shadow">
    <div class="card-body">
      <h2 class="card-title text-lg">Outreach-Nachricht (kopieren)</h2>
      <div class="relative">
        <textarea id="outreach-text" class="textarea textarea-bordered w-full font-mono text-sm" rows="10"
                  readonly><?= esc($outreachTemplate) ?></textarea>
        <button onclick="copyOutreach()" class="btn btn-xs btn-accent absolute top-2 right-2">
          Kopieren
        </button>
      </div>
      <p class="text-xs text-slate-400 mt-1">Text vor dem Versenden individuell anpassen.</p>
    </div>
  </div>

  <!-- Listings for this owner -->
  <div class="card bg-base-100 shadow">
    <div class="card-body">
      <div class="flex items-center justify-between mb-3">
        <h2 class="card-title text-lg">Inserate</h2>
        <a href="<?= site_url("admin/estate/listings/new/{$owner['id']}") ?>"
           class="btn btn-accent btn-sm">+ Neues Inserat</a>
      </div>
      <?php if (empty($listings)): ?>
        <p class="text-slate-400 text-sm">Noch keine Inserate.</p>
      <?php else: ?>
        <table class="table table-sm">
          <thead><tr><th>ID</th><th>Status</th><th>Ort</th><th>Warmmiete</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($listings as $l): ?>
            <tr>
              <td class="font-mono text-xs">#<?= $l['id'] ?></td>
              <td><span class="badge badge-sm"><?= esc($l['status']) ?></span></td>
              <td><?= esc($l['location_approx'] ?? '—') ?></td>
              <td><?= $l['warmmiete'] ? number_format($l['warmmiete'] / 100, 0, ',', '.') . ' €' : '—' ?></td>
              <td>
                <a href="<?= site_url("admin/estate/listings/{$l['id']}") ?>"
                   class="btn btn-ghost btn-xs">Details</a>
              </td>
            </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      <?php endif ?>
    </div>
  </div>

  <!-- Danger zone -->
  <div class="card bg-base-100 shadow border border-error/30">
    <div class="card-body">
      <h2 class="card-title text-error text-base">Gefahrenzone</h2>
      <form method="post" action="<?= site_url("admin/estate/owners/{$owner['id']}/delete") ?>"
            onsubmit="return confirm('Anbieter wirklich löschen?')">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-error btn-sm">Anbieter löschen</button>
      </form>
    </div>
  </div>

</div>
</div>

<script>
function copyOutreach() {
  const el = document.getElementById('outreach-text');
  el.select();
  document.execCommand('copy');
  const btn = event.target;
  btn.textContent = 'Kopiert!';
  setTimeout(() => btn.textContent = 'Kopieren', 2000);
}

function copyInviteUrl() {
  const el = document.getElementById('invite-url-field');
  el.select();
  el.setSelectionRange(0, 99999);
  navigator.clipboard ? navigator.clipboard.writeText(el.value) : document.execCommand('copy');
  const btn = event.target;
  const orig = btn.textContent;
  btn.textContent = 'Kopiert ✓';
  btn.disabled = true;
  setTimeout(() => { btn.textContent = orig; btn.disabled = false; }, 3000);
}
</script>
