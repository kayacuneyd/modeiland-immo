<?php /** @var array $seeker @var array $owner @var string $body @var array $listing */ ?>
Guten Tag,

Sie haben eine neue Nachricht über modeiland erhalten:

Von: <?= $owner['display_name'] ?? 'Anbieter' ?>

Bezüglich Inserat: <?= $listing['location_approx'] ?? "Inserat #{$listing['id']}" ?>


"<?= wordwrap($body, 72, "\n", true) ?>"


Antworten Sie über Ihr modeiland-Panel:
<?= site_url('seeker/panel') ?>


Mit freundlichen Grüßen
Ihr modeiland-Team

---
<?= site_url('impressum') ?> · <?= site_url('datenschutz') ?>
