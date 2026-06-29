<?php
/** @var array $owner @var array $liveListings @var array $draftListings
 *  @var array $messages @var int $unread @var bool $profileComplete @var int $pollingInterval
 */
?>
<div data-theme="modeiland" class="min-h-screen bg-base-100">

  <!-- Header -->
  <header class="bg-primary text-primary-content py-4 px-6">
    <div class="max-w-3xl mx-auto flex items-center justify-between">
      <span class="font-bold text-xl">modeiland</span>
      <div class="flex items-center gap-4 text-sm">
        <span class="opacity-70">Anbieter-Panel</span>
        <?php if ($unread > 0): ?>
          <span id="unread-badge" class="badge badge-accent font-mono"><?= $unread ?></span>
        <?php else: ?>
          <span id="unread-badge" class="badge badge-accent font-mono hidden"></span>
        <?php endif ?>
        <a href="<?= site_url('owner/profil') ?>" class="link link-hover text-primary-content/60 text-xs">Profil</a>
        <a href="<?= site_url('owner/logout') ?>" class="link link-hover text-primary-content/60 text-xs">Abmelden</a>
      </div>
    </div>
  </header>

  <main class="max-w-3xl mx-auto px-4 py-6 space-y-6">

    <?php if (session()->has('success')): ?>
      <div class="alert alert-success" role="alert"><span><?= esc(session('success')) ?></span></div>
    <?php endif ?>
    <?php if (session()->has('error')): ?>
      <div class="alert alert-error" role="alert"><span><?= esc(session('error')) ?></span></div>
    <?php endif ?>
    <?php if (session()->has('info')): ?>
      <div class="alert alert-info" role="alert"><span><?= esc(session('info')) ?></span></div>
    <?php endif ?>

    <!-- Profile completion banner (calm, non-aggressive) -->
    <?php if (! $profileComplete): ?>
    <div class="alert bg-base-200 border border-primary/20 text-sm" role="complementary">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <div>
        <p class="font-medium">Ihr Profil ist noch unvollständig</p>
        <p class="text-slate-500 text-xs mt-0.5">
          Fügen Sie eine E-Mail-Adresse hinzu, um sich künftig ohne Einladungslink anzumelden.
        </p>
      </div>
      <a href="<?= site_url('owner/profil') ?>" class="btn btn-sm btn-ghost ml-auto whitespace-nowrap">
        Profil ergänzen
      </a>
    </div>
    <?php endif ?>

    <!-- ─── Zone 1: Meine Inserate (live + paused) ─────────────────────────── -->
    <section aria-labelledby="zone-live">
      <h2 id="zone-live" class="text-lg font-bold text-primary mb-3">Meine Inserate</h2>

      <?php if (empty($liveListings)): ?>
        <p class="text-slate-400 text-sm">Noch keine aktiven Inserate.</p>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach ($liveListings as $l):
            $w = $l['warmmiete'] ? number_format($l['warmmiete'] / 100, 0, ',', '.') . ' €/Monat' : null;
          ?>
          <div class="card bg-base-100 shadow border border-base-300">
            <div class="card-body py-4 px-5 flex flex-row items-center gap-4">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <span class="badge badge-sm <?= $l['status'] === 'live' ? 'badge-success' : 'badge-warning' ?>">
                    <?= $l['status'] === 'live' ? 'Aktiv' : 'Pausiert' ?>
                  </span>
                  <?php if ($l['is_first_free']): ?>
                    <span class="badge badge-xs badge-ghost">kostenlos</span>
                  <?php endif ?>
                </div>
                <p class="font-medium text-sm truncate"><?= esc($l['location_approx'] ?? 'Kein Standort') ?></p>
                <?php if ($w): ?>
                  <p class="font-mono text-primary font-bold text-sm"><?= $w ?></p>
                <?php endif ?>
              </div>

              <div class="flex gap-2 shrink-0">
                <a href="<?= site_url("inserate/{$l['id']}") ?>" target="_blank"
                   class="btn btn-xs btn-ghost" title="Inserat ansehen">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>

                <form method="post" action="<?= site_url("owner/listings/{$l['id']}/pausieren") ?>">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-xs btn-ghost"
                          title="<?= $l['status'] === 'live' ? 'Pausieren' : 'Reaktivieren' ?>">
                    <?php if ($l['status'] === 'live'): ?>
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6"/></svg>
                    <?php else: ?>
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                    <?php endif ?>
                  </button>
                </form>

                <form method="post" action="<?= site_url("owner/listings/{$l['id']}/entfernen") ?>"
                      onsubmit="return confirm('Inserat wirklich entfernen? Diese Aktion erfordert eine Bestätigung Ihrer Identität.')">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-xs btn-ghost text-error" title="Entfernen">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </form>
              </div>
            </div>
          </div>
          <?php endforeach ?>
        </div>
      <?php endif ?>
    </section>

    <!-- ─── Zone 2: Freigabe ausstehend (drafts) ───────────────────────────── -->
    <?php if (! empty($draftListings)): ?>
    <section aria-labelledby="zone-draft">
      <h2 id="zone-draft" class="text-lg font-bold text-primary mb-3">
        Freigabe ausstehend
        <span class="badge badge-warning badge-sm ml-1"><?= count($draftListings) ?></span>
      </h2>
      <div class="space-y-3">
        <?php foreach ($draftListings as $l): ?>
        <div class="card bg-base-100 shadow border-l-4 border-accent border border-base-300">
          <div class="card-body py-4 px-5 flex flex-row items-center gap-4">
            <div class="flex-1 min-w-0">
              <span class="badge badge-warning badge-sm mb-1">Entwurf</span>
              <p class="font-medium text-sm truncate"><?= esc($l['location_approx'] ?? 'Kein Standort') ?></p>
              <p class="text-xs text-slate-400 mt-0.5">
                KI-Import: <span class="font-mono"><?= esc($l['ai_import_status']) ?></span>
              </p>
            </div>
            <a href="<?= site_url("owner/draft/{$l['id']}") ?>"
               class="btn btn-accent btn-sm shrink-0" style="min-height:40px">
              Vorschau &amp; Freigabe →
            </a>
          </div>
        </div>
        <?php endforeach ?>
      </div>
    </section>
    <?php endif ?>

    <!-- ─── Zone 3: Anfragen (messages) ────────────────────────────────────── -->
    <section aria-labelledby="zone-messages">
      <h2 id="zone-messages" class="text-lg font-bold text-primary mb-3">
        Anfragen
        <?php if ($unread > 0): ?>
          <span class="badge badge-accent badge-sm font-mono ml-1"><?= $unread ?> neu</span>
        <?php endif ?>
      </h2>

      <?php if (empty($messages)): ?>
        <div class="text-center py-12 text-slate-400">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto mb-2 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
          <p class="text-sm">Noch keine Anfragen.</p>
          <p class="text-xs mt-1">Sobald ein Interessent schreibt, erscheint die Nachricht hier.</p>
        </div>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach ($messages as $m): ?>
          <div class="card bg-base-100 shadow <?= ! $m['read_at'] ? 'border-l-4 border-accent border border-base-300' : 'border border-base-300' ?>">
            <div class="card-body py-4 px-5">
              <div class="flex items-start justify-between gap-2 mb-2">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="font-medium text-sm"><?= esc($m['seeker_email'] ?? '—') ?></span>
                  <?php if (! $m['read_at']): ?>
                    <span class="badge badge-accent badge-xs">Neu</span>
                  <?php endif ?>
                </div>
                <div class="text-xs text-slate-400 text-right shrink-0">
                  <div><?= esc($m['created_at']) ?></div>
                  <?php if ($m['listing_location']): ?>
                    <div><?= esc($m['listing_location']) ?></div>
                  <?php endif ?>
                </div>
              </div>
              <p class="text-sm text-base-content/80 whitespace-pre-line"><?= esc($m['body']) ?></p>
            </div>
          </div>
          <?php endforeach ?>
        </div>
      <?php endif ?>
    </section>

  </main>

  <footer class="text-center text-xs text-slate-400 py-6 border-t border-base-200 mt-8">
    <a href="<?= site_url('impressum') ?>" class="link">Impressum</a> ·
    <a href="<?= site_url('datenschutz') ?>" class="link">Datenschutz</a>
  </footer>

</div>

<!-- HTTP polling for new messages (30s interval, no WebSocket) -->
<script>
(function () {
  var interval = <?= (int) $pollingInterval ?> * 1000;
  var badge    = document.getElementById('unread-badge');

  function poll() {
    fetch('<?= site_url('owner/messages/poll') ?>', { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.unread > 0) {
          badge.textContent = data.unread;
          badge.classList.remove('hidden');
        } else {
          badge.classList.add('hidden');
        }
      })
      .catch(function () { /* network error — silently ignore */ });
  }

  setInterval(poll, interval);
})();
</script>
