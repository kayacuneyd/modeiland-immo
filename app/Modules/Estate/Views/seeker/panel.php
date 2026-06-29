<?php
/**
 * @var array  $seeker
 * @var array  $messages
 * @var int    $unread
 * @var array  $savedSearches
 * @var array|null $subscription
 * @var bool   $billingEnabled
 * @var int    $pollingInterval
 */
$subActive = $subscription && in_array($subscription['status'], ['active', 'trial'], true);
$isTrial   = $subscription && $subscription['status'] === 'trial';
?>
<div data-theme="modeiland" class="min-h-screen bg-base-100">

  <!-- Header -->
  <header class="bg-primary text-primary-content py-4 px-6">
    <div class="max-w-3xl mx-auto flex items-center justify-between">
      <a href="<?= site_url('inserate') ?>" class="font-bold text-xl">modeiland</a>
      <div class="flex items-center gap-4 text-sm">
        <span class="opacity-70">Mein Bereich</span>
        <?php if ($unread > 0): ?>
          <span id="seeker-unread-badge" class="badge badge-accent font-mono"><?= $unread ?></span>
        <?php else: ?>
          <span id="seeker-unread-badge" class="badge badge-accent font-mono hidden"></span>
        <?php endif ?>
        <a href="<?= site_url('seeker/logout') ?>" class="link link-hover text-primary-content/60 text-xs">Abmelden</a>
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

    <!-- Subscription status banner -->
    <div class="card bg-base-200 border border-base-300">
      <div class="card-body py-4 px-5 flex flex-row items-center gap-4">
        <div class="flex-1">
          <p class="font-medium text-sm">
            <?= esc($seeker['email']) ?>
            <?php if ($subActive): ?>
              <span class="badge badge-success badge-sm ml-2">
                <?= $isTrial ? 'Test-Modus' : 'Plus aktiv' ?>
              </span>
            <?php else: ?>
              <span class="badge badge-ghost badge-sm ml-2">Kostenlos</span>
            <?php endif ?>
          </p>
          <?php if ($subActive && ! $isTrial && $subscription['current_period_end']): ?>
            <p class="text-xs text-slate-400 mt-0.5">
              Verlängerung: <?= esc(date('d.m.Y', strtotime($subscription['current_period_end']))) ?>
            </p>
          <?php endif ?>
        </div>
        <?php if ($subActive && $billingEnabled && ! $isTrial && ! empty($subscription['stripe_customer_id'])): ?>
          <form method="post" action="<?= site_url('abonnieren/portal') ?>">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-ghost btn-xs">Abonnement verwalten</button>
          </form>
        <?php elseif (! $subActive): ?>
          <a href="<?= site_url('abonnieren') ?>" class="btn btn-primary btn-sm">Jetzt abonnieren</a>
        <?php endif ?>
      </div>
    </div>

    <!-- ─── Zone 1: Nachrichten ──────────────────────────────────────────── -->
    <section aria-labelledby="seeker-messages">
      <h2 id="seeker-messages" class="text-lg font-bold text-primary mb-3">
        Nachrichten
        <?php if ($unread > 0): ?>
          <span class="badge badge-accent badge-sm font-mono ml-1"><?= $unread ?> neu</span>
        <?php endif ?>
      </h2>

      <?php if (empty($messages)): ?>
        <div class="text-center py-10 text-slate-400">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto mb-2 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
          </svg>
          <p class="text-sm">Noch keine Nachrichten.</p>
          <p class="text-xs mt-1">
            <a href="<?= site_url('inserate') ?>" class="link link-primary">Inserate durchsuchen</a> und Anbieter kontaktieren.
          </p>
        </div>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach ($messages as $m): ?>
          <div class="card bg-base-100 shadow border border-base-300">
            <div class="card-body py-4 px-5">
              <div class="flex items-start justify-between gap-2 mb-1">
                <div class="text-sm">
                  <span class="font-medium"><?= esc($m['owner_name'] ?? 'Anbieter') ?></span>
                  <?php if ($m['listing_location']): ?>
                    <span class="text-slate-400"> · <?= esc($m['listing_location']) ?></span>
                  <?php endif ?>
                </div>
                <span class="text-xs text-slate-400 shrink-0"><?= esc($m['created_at']) ?></span>
              </div>
              <p class="text-sm text-base-content/80 whitespace-pre-line"><?= esc($m['body']) ?></p>
              <?php if ($m['listing_location']): ?>
                <a href="<?= site_url("inserate/{$m['listing_id']}") ?>"
                   class="text-xs link link-primary mt-1 inline-block">Inserat ansehen →</a>
              <?php endif ?>
            </div>
          </div>
          <?php endforeach ?>
        </div>
      <?php endif ?>
    </section>

    <!-- ─── Zone 2: Gespeicherte Suchen ─────────────────────────────────── -->
    <section aria-labelledby="seeker-searches">
      <div class="flex items-center justify-between mb-3">
        <h2 id="seeker-searches" class="text-lg font-bold text-primary">Gespeicherte Suchen</h2>
        <a href="<?= site_url('inserate') ?>" class="btn btn-ghost btn-xs">+ Neue Suche</a>
      </div>

      <?php if (empty($savedSearches)): ?>
        <p class="text-slate-400 text-sm">
          Keine gespeicherten Suchen.
          <a href="<?= site_url('inserate') ?>" class="link link-primary">Jetzt suchen</a> und Suche speichern.
        </p>
      <?php else: ?>
        <div class="space-y-2">
          <?php foreach ($savedSearches as $s):
            $filters = json_decode($s['filters_json'] ?? '{}', true);
          ?>
          <div class="card bg-base-100 shadow border border-base-300">
            <div class="card-body py-3 px-5 flex flex-row items-center gap-3">
              <div class="flex-1 min-w-0">
                <p class="font-medium text-sm truncate"><?= esc($s['label'] ?? 'Suche') ?></p>
                <p class="text-xs text-slate-400 mt-0.5">
                  <?php
                  $tags = [];
                  if (! empty($filters['location'])) $tags[] = esc($filters['location']);
                  if (! empty($filters['rent_max']))  $tags[] = 'max ' . number_format($filters['rent_max'] / 100, 0, ',', '.') . ' €';
                  if (! empty($filters['rooms_min'])) $tags[] = 'ab ' . $filters['rooms_min'] . ' Zi.';
                  echo implode(' · ', $tags) ?: '—';
                  ?>
                </p>
              </div>
              <div class="flex items-center gap-2 shrink-0">
                <!-- Alert toggle -->
                <form method="post" action="<?= site_url("seeker/suche/{$s['id']}/alarm") ?>">
                  <?= csrf_field() ?>
                  <button type="submit"
                          class="btn btn-xs <?= $s['alert_enabled'] ? 'btn-accent' : 'btn-ghost' ?>"
                          title="<?= $s['alert_enabled'] ? 'Alarm deaktivieren' : 'Alarm aktivieren' ?>">
                    <?= $s['alert_enabled'] ? '🔔' : '🔕' ?>
                  </button>
                </form>
                <!-- Open search -->
                <?php
                $qs = http_build_query($filters);
                ?>
                <a href="<?= site_url('inserate?' . $qs) ?>" class="btn btn-ghost btn-xs">Suchen</a>
                <!-- Delete -->
                <form method="post" action="<?= site_url("seeker/suche/{$s['id']}/loeschen") ?>"
                      onsubmit="return confirm('Gespeicherte Suche löschen?')">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-ghost btn-xs text-error">✕</button>
                </form>
              </div>
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

<!-- HTTP polling for new messages (seeker side) -->
<script>
(function () {
  var interval = <?= (int) $pollingInterval ?> * 1000;
  var badge    = document.getElementById('seeker-unread-badge');

  function poll() {
    fetch('<?= site_url('seeker/messages/poll') ?>', { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.unread > 0) {
          badge.textContent = data.unread;
          badge.classList.remove('hidden');
        } else {
          badge.classList.add('hidden');
        }
      })
      .catch(function () {});
  }

  setInterval(poll, interval);
})();
</script>
