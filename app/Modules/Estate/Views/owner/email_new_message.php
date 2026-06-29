<?php /** @var array $owner @var array $seeker @var string $body @var array $listing */ ?>
<!DOCTYPE html>
<html lang="de">
<head><meta charset="utf-8"><title>Neue Nachricht — modeiland</title></head>
<body style="font-family:sans-serif;max-width:600px;margin:0 auto;padding:24px;color:#1B2A4A;background:#F6F1E7;">
  <h2 style="color:#1B2A4A;border-bottom:2px solid #C7841A;padding-bottom:8px;">
    Neue Nachricht auf modeiland
  </h2>
  <p>Guten Tag <?= esc($owner['display_name']) ?>,</p>
  <p>Sie haben eine neue Nachricht zu Ihrem Inserat erhalten.</p>

  <div style="background:#EDE7DA;border-left:4px solid #C7841A;padding:12px 16px;border-radius:4px;margin:16px 0;">
    <p style="margin:0 0 4px;font-size:12px;color:#44506B;">
      Von: <?= esc($seeker['email'] ?? '—') ?> ·
      Inserat: <?= esc($listing['location_approx'] ?? '#' . $listing['id']) ?>
    </p>
    <p style="margin:0;white-space:pre-line;font-size:15px;"><?= esc($body) ?></p>
  </div>

  <p>
    <a href="<?= site_url('owner/panel') ?>"
       style="background:#1B2A4A;color:#F6F1E7;padding:10px 20px;border-radius:6px;text-decoration:none;display:inline-block;">
      Alle Nachrichten ansehen
    </a>
  </p>

  <hr style="border:none;border-top:1px solid #DDD7CA;margin:24px 0;">
  <p style="font-size:12px;color:#44506B;">
    modeiland · <a href="<?= site_url('impressum') ?>">Impressum</a> ·
    <a href="<?= site_url('datenschutz') ?>">Datenschutz</a>
  </p>
</body>
</html>
