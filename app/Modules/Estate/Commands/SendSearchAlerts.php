<?php

namespace App\Modules\Estate\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Modules\Estate\Models\SavedSearchModel;
use App\Modules\Estate\Models\ListingModel;

/**
 * spark estate:send-search-alerts
 *
 * Finds saved searches with alert_enabled=1, checks for new listings
 * since last_alerted_at that match the saved filters, and sends an
 * email digest to the seeker.
 *
 * Run via Hostinger Scheduled Tasks:
 *   php /path/to/spark estate:send-search-alerts
 *   Frequency: daily or twice daily (e.g. 07:00 + 18:00 UTC)
 */
class SendSearchAlerts extends BaseCommand
{
    protected $group       = 'estate';
    protected $name        = 'estate:send-search-alerts';
    protected $description = 'Emails new-listing alerts to seekers with active saved-search alarms.';

    public function run(array $params): void
    {
        $savedSearches = new SavedSearchModel();
        $listings      = new ListingModel();

        $alerts = $savedSearches->getAlertsToSend();

        if (empty($alerts)) {
            CLI::write('[estate:send-search-alerts] No active alerts found.', 'green');
            return;
        }

        $sent  = 0;
        $empty = 0;

        foreach ($alerts as $alert) {
            $filters      = json_decode($alert['filters_json'] ?? '{}', true);
            $since        = $alert['last_alerted_at'] ?? date('Y-m-d H:i:s', strtotime('-1 day'));
            $seekerEmail  = $alert['seeker_email'] ?? '';

            if (! $seekerEmail) {
                continue;
            }

            $newListings = $this->findMatchingListings($listings, $filters, $since);

            if (empty($newListings)) {
                $empty++;
                continue;
            }

            $mailed = $this->sendAlertEmail($seekerEmail, $alert['label'] ?? 'Suche', $newListings, $filters);

            if ($mailed) {
                $savedSearches->stampAlerted((int) $alert['id']);
                $sent++;
                CLI::write("[estate:send-search-alerts] Alert sent to {$seekerEmail} ({$alert['label']}, " . count($newListings) . " Inserat(e))", 'green');
            }
        }

        CLI::write("[estate:send-search-alerts] Done. Sent: {$sent}, no new results: {$empty}.", 'green');
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function findMatchingListings(ListingModel $model, array $filters, string $since): array
    {
        $db = db_connect();

        $builder = $db->table('listings')
            ->where('status', 'live')
            ->where('created_at >', $since);

        if (! empty($filters['location'])) {
            $builder->like('location_approx', $filters['location']);
        }

        if (! empty($filters['rent_max'])) {
            $builder->where('warmmiete <=', (int) $filters['rent_max']);
        }

        if (! empty($filters['rooms_min'])) {
            $builder->where('rooms >=', (float) $filters['rooms_min']);
        }

        if (! empty($filters['type'])) {
            $builder->where('property_type', $filters['type']);
        }

        return $builder->orderBy('created_at', 'DESC')->limit(10)->get()->getResultArray();
    }

    private function sendAlertEmail(string $toEmail, string $label, array $newListings, array $filters): bool
    {
        $count   = count($newListings);
        $siteUrl = rtrim(base_url(), '/');

        $lines = [];
        foreach ($newListings as $l) {
            $price    = $l['warmmiete'] ? number_format($l['warmmiete'] / 100, 0, ',', '.') . ' €/Monat' : 'Preis auf Anfrage';
            $location = $l['location_approx'] ?? '—';
            $lines[]  = "• {$location} — {$price}";
            $lines[]  = "  {$siteUrl}/inserate/{$l['id']}";
        }

        $listText = implode("\n", $lines);

        $qs      = http_build_query(array_filter($filters));
        $listUrl = $siteUrl . '/inserate' . ($qs ? '?' . $qs : '');

        $body =
            "Guten Tag,\n\n" .
            "Es gibt {$count} neue Inserat(e) für Ihre gespeicherte Suche „{$label}":\n\n" .
            $listText . "\n\n" .
            "Alle Ergebnisse ansehen:\n{$listUrl}\n\n" .
            "Mit freundlichen Grüßen\nIhr modeiland-Team\n\n" .
            "---\nSie erhalten diese E-Mail, weil Sie einen Suchalarm eingerichtet haben.\n" .
            "Panel: {$siteUrl}/seeker/panel";

        try {
            $email = \Config\Services::email();
            $email->setFrom(env('EMAIL_FROM', 'noreply@modeiland.de'), 'modeiland');
            $email->setTo($toEmail);
            $email->setSubject("{$count} neue Inserat(e) für Ihre Suche — modeiland");
            $email->setMessage($body);
            return (bool) $email->send(false);
        } catch (\Throwable $e) {
            log_message('error', "[estate:send-search-alerts] Email to {$toEmail} failed: " . $e->getMessage());
            return false;
        }
    }
}
