<?php /** @var array $owner @var string $otp */ ?>
<!DOCTYPE html>
<html lang="de">
<head><meta charset="utf-8"><title>Bestätigungscode — modeiland</title></head>
<body style="font-family:sans-serif;max-width:600px;margin:0 auto;padding:24px;color:#1B2A4A;background:#F6F1E7;">
  <h2 style="color:#1B2A4A;border-bottom:2px solid #C7841A;padding-bottom:8px;">
    Ihr Bestätigungscode
  </h2>
  <p>Guten Tag <?= esc($owner['display_name']) ?>,</p>
  <p>Geben Sie diesen Code auf modeiland ein, um Ihre Identität zu bestätigen:</p>

  <div style="text-align:center;margin:32px 0;">
    <div style="font-size:40px;font-weight:bold;font-family:monospace;letter-spacing:12px;color:#1B2A4A;background:#EDE7DA;border-radius:8px;padding:16px 24px;display:inline-block;">
      <?= esc($otp) ?>
    </div>
  </div>

  <p style="font-size:13px;color:#44506B;">
    Der Code ist <strong>10 Minuten</strong> gültig.
    Falls Sie keine Bestätigung angefordert haben, ignorieren Sie diese E-Mail.
  </p>

  <hr style="border:none;border-top:1px solid #DDD7CA;margin:24px 0;">
  <p style="font-size:12px;color:#44506B;">
    modeiland · <a href="<?= site_url('impressum') ?>">Impressum</a>
  </p>
</body>
</html>
