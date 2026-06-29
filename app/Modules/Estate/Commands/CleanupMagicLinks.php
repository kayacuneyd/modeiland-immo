<?php

namespace App\Modules\Estate\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * spark estate:cleanup-magic-links
 *
 * Deletes magic login tokens whose 15-minute TTL has elapsed.
 * Used_at-stamped tokens (consumed) and expired tokens are both removed.
 *
 * Run via Hostinger Scheduled Tasks:
 *   php /path/to/public_html/spark estate:cleanup-magic-links
 *   Frequency: hourly (safe to run more often)
 */
class CleanupMagicLinks extends BaseCommand
{
    protected $group       = 'estate';
    protected $name        = 'estate:cleanup-magic-links';
    protected $description = 'Deletes expired and consumed magic login tokens.';

    public function run(array $params): void
    {
        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        // Delete tokens that are either:
        //   (a) past their expires_at — TTL elapsed without use
        //   (b) already consumed (used_at IS NOT NULL) — single-use, safe to purge
        $affected = $db->table('magic_login_tokens')
            ->groupStart()
                ->where('expires_at <', $now)
                ->orWhere('used_at IS NOT NULL', null, false)
            ->groupEnd()
            ->delete();

        $count = $db->affectedRows();

        CLI::write("[estate:cleanup-magic-links] Deleted {$count} stale magic link token(s).", 'green');
    }
}
