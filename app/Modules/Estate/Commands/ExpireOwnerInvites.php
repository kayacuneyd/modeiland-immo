<?php

namespace App\Modules\Estate\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * spark estate:expire-invites
 *
 * Marks overdue invite tokens as 'expired' and sends a warning email
 * 7 days before expiry to owners who have not yet accepted their invite.
 *
 * Run via Hostinger Scheduled Tasks:
 *   php /path/to/public_html/spark estate:expire-invites
 *   Frequency: daily (e.g. 02:00 UTC)
 */
class ExpireOwnerInvites extends BaseCommand
{
    protected $group       = 'estate';
    protected $name        = 'estate:expire-invites';
    protected $description = 'Expires overdue owner invite tokens and warns 7 days before expiry.';

    public function run(array $params): void
    {
        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        // ── 1. Hard-expire tokens past their expires_at ──────────────────────
        $expired = $db->table('owner_invites')
            ->where('status', 'active')
            ->where('expires_at <', $now)
            ->countAllResults(false);

        if ($expired > 0) {
            $db->table('owner_invites')
                ->where('status', 'active')
                ->where('expires_at <', $now)
                ->update(['status' => 'expired', 'updated_at' => $now]);

            CLI::write("[estate:expire-invites] Marked {$expired} token(s) as expired.", 'yellow');
        } else {
            CLI::write('[estate:expire-invites] No tokens to expire.', 'green');
        }

        // ── 2. 7-day warning: active tokens expiring within 7 days, not yet warned ──
        $warnBefore = date('Y-m-d H:i:s', strtotime('+7 days'));

        $soonExpiring = $db->table('owner_invites oit')
            ->select('oit.owner_id, oit.expires_at, o.email, o.display_name')
            ->join('owners o', 'o.id = oit.owner_id')
            ->where('oit.status', 'active')
            ->where('oit.expires_at >', $now)
            ->where('oit.expires_at <', $warnBefore)
            ->where('oit.warning_sent_at IS NULL', null, false)
            ->groupBy('oit.owner_id')
            ->get()->getResultArray();

        $warned = 0;
        foreach ($soonExpiring as $row) {
            if (empty($row['email'])) {
                continue; // no email address on file — cannot warn
            }

            $sent = $this->sendExpiryWarning($row);

            if ($sent) {
                $db->table('owner_invites')
                    ->where('owner_id', $row['owner_id'])
                    ->where('status', 'active')
                    ->where('expires_at <', $warnBefore)
                    ->update(['warning_sent_at' => $now]);
                $warned++;
            }
        }

        if ($warned > 0) {
            CLI::write("[estate:expire-invites] Sent {$warned} expiry warning(s).", 'yellow');
        }

        CLI::write('[estate:expire-invites] Done.', 'green');
    }

    private function sendExpiryWarning(array $row): bool
    {
        $email       = \Config\Services::email();
        $siteUrl     = rtrim(base_url(), '/');
        $ownerName   = $row['display_name'] ?? 'Anbieter';
        $expiresDate = date('d.m.Y', strtotime($row['expires_at']));

        $email->setFrom(env('EMAIL_FROM', 'noreply@modeiland.de'), 'modeiland');
        $email->setTo($row['email']);
        $email->setSubject('Ihr Einladungslink läuft bald ab — modeiland');
        $email->setMessage(
            "Guten Tag {$ownerName},\n\n" .
            "Ihr persönlicher Einladungslink für modeiland läuft am {$expiresDate} ab.\n\n" .
            "Falls Sie den Link noch nicht genutzt haben, bitten wir Sie, sich bei uns zu melden,\n" .
            "damit wir Ihnen einen neuen Link zusenden können.\n\n" .
            "Bei Fragen antworten Sie einfach auf diese E-Mail.\n\n" .
            "Mit freundlichen Grüßen\nIhr modeiland-Team\n\n{$siteUrl}"
        );

        return (bool) $email->send(false);
    }
}
