<?php /** @var array $owner @var string $link */ ?>
<!DOCTYPE html>
<html lang="de">
<head><meta charset="utf-8"><title>Anmelde-Link — modeiland</title></head>
<body style="font-family:sans-serif;max-width:600px;margin:0 auto;padding:24px;color:#1B2A4A;background:#F6F1E7;">
  <h2 style="color:#1B2A4A;border-bottom:2px solid #C7841A;padding-bottom:8px;">
    Ihr Anmelde-Link
  </h2>
  <p>Guten Tag <?= esc($owner['display_name']) ?>,</p>
  <p>Sie haben einen Anmelde-Link für modeiland angefordert.</p>

  <div style="text-align:center;margin:32px 0;">
    <a href="<?= esc($link) ?>"
       style="background:#1B2A4A;color:#F6F1E7;padding:14px 28px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:16px;display:inline-block;">
      Jetzt anmelden
    </a>
  </div>

  <p style="font-size:13px;color:#44506B;">
    Dieser Link ist <strong>15 Minuten</strong> gültig und kann nur einmal verwendet werden.
  </p>
  <p style="font-size:13px;color:#44506B;">
    Falls Sie keinen Anmelde-Link angefordert haben, ignorieren Sie diese E-Mail.
    Ihr Konto bleibt unberührt.
  </p>
  <p style="font-size:12px;color:#9B9B9B;word-break:break-all;">
    Link: <?= esc($link) ?>
  </p>

  <hr style="border:none;border-top:1px solid #DDD7CA;margin:24px 0;">
  <p style="font-size:12px;color:#44506B;">
    modeiland · <a href="<?= site_url('impressum') ?>">Impressum</a> ·
    <a href="<?= site_url('datenschutz') ?>">Datenschutz</a>
  </p>
</body>
</html>
