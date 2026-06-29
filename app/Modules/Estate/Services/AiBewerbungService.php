<?php

namespace App\Modules\Estate\Services;

use App\Modules\Estate\Libraries\AiService;

/**
 * Generates a personalised German application package (Bewerbungspaket):
 *   1. Anschreiben (cover letter) — original text, not a template fill-in
 *   2. Unterlagen-Checklist — document list relevant to the listing
 *
 * Results are cached in application_drafts (seeker_id × listing_id).
 * Cache lifetime: 30 days — regenerate button available.
 *
 * AI cost constraint: one call per seeker×listing pair; cached thereafter.
 * Token budget: ~600 tokens output max.
 */
class AiBewerbungService
{
    private const CACHE_DAYS = 30;

    /**
     * Returns cached or freshly generated Bewerbungspaket.
     * ['cover_letter' => string, 'checklist' => array<string>]
     * On AI failure returns placeholder text (never throws to caller).
     */
    public function get(int $seekerId, int $listingId, array $seekerProfile, array $listing): array
    {
        $db      = db_connect();
        $cutoff  = date('Y-m-d H:i:s', strtotime('-' . self::CACHE_DAYS . ' days'));

        $cached = $db->table('application_drafts')
            ->where('seeker_id', $seekerId)
            ->where('listing_id', $listingId)
            ->where('generated_at >', $cutoff)
            ->get()->getRowArray();

        if ($cached) {
            return [
                'cover_letter' => $cached['cover_letter'] ?? '',
                'checklist'    => json_decode($cached['checklist_json'] ?? '[]', true),
                'cached'       => true,
            ];
        }

        [$coverLetter, $checklist] = $this->generate($seekerProfile, $listing);

        $now = date('Y-m-d H:i:s');

        $existing = $db->table('application_drafts')
            ->where('seeker_id', $seekerId)
            ->where('listing_id', $listingId)
            ->get()->getRowArray();

        $row = [
            'cover_letter'   => $coverLetter,
            'checklist_json' => json_encode($checklist),
            'generated_at'   => $now,
        ];

        if ($existing) {
            $db->table('application_drafts')->where('id', $existing['id'])->update($row);
        } else {
            $db->table('application_drafts')->insert(array_merge($row, [
                'seeker_id'  => $seekerId,
                'listing_id' => $listingId,
            ]));
        }

        return [
            'cover_letter' => $coverLetter,
            'checklist'    => $checklist,
            'cached'       => false,
        ];
    }

    /** Force regeneration (ignore cache). */
    public function regenerate(int $seekerId, int $listingId, array $seekerProfile, array $listing): array
    {
        $db = db_connect();
        $db->table('application_drafts')
            ->where('seeker_id', $seekerId)
            ->where('listing_id', $listingId)
            ->delete();

        return $this->get($seekerId, $listingId, $seekerProfile, $listing);
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function generate(array $profile, array $listing): array
    {
        $name       = $profile['name'] ?? 'Interessent';
        $occupation = $profile['occupation'] ?? '';
        $income     = $profile['income_range_cents'] ? number_format($profile['income_range_cents'] / 100, 0, ',', '.') . ' €/Monat netto' : '';
        $household  = $profile['household_size'] ?? 1;
        $moveIn     = $profile['move_in_date'] ?? 'so bald wie möglich';
        $pets       = $profile['pets'] ? 'Ja' : 'Nein';
        $notes      = $profile['notes'] ?? '';

        $location   = $listing['location_approx'] ?? 'Ihrer Immobilie';
        $warmmiete  = $listing['warmmiete'] ? number_format($listing['warmmiete'] / 100, 0, ',', '.') . ' €' : '';
        $rooms      = $listing['rooms'] ?? '';
        $listingDesc = mb_substr(strip_tags($listing['ai_description'] ?? ''), 0, 300);

        $prompt = <<<PROMPT
        Du bist ein freundlicher Assistent, der Wohnungssuchenden hilft, Bewerbungsanschreiben auf Deutsch zu verfassen.

        Schreibe ein ORIGINELLES, persönliches Anschreiben (kein Template, kein Lückentext) für:

        Bewerber: {$name}
        Beruf: {$occupation}
        Haushaltsgröße: {$household} Person(en)
        Einkommen: {$income}
        Einzugstermin: {$moveIn}
        Haustiere: {$pets}
        Persönliche Notiz: {$notes}

        Inserat:
        Standort: {$location}
        Warmmiete: {$warmmiete}
        Zimmer: {$rooms}
        Beschreibung: {$listingDesc}

        Anforderungen:
        - Direkte Anrede: "Sehr geehrte Damen und Herren,"
        - Natürlicher, persönlicher Ton — kein Floskeln
        - Max. 200 Wörter
        - Kein Schluss-Gruß (wird separat eingefügt)
        - NUR den Anschreiben-Text ausgeben, kein JSON, kein Markdown

        Gib danach auf einer neuen Zeile "---CHECKLIST---" aus, gefolgt von einer JSON-Liste mit 5-7 Strings: typische Unterlagen für eine deutsche Wohnungsbewerbung, passend zu diesem Profil. Beispiel:
        ["Personalausweis (Kopie)", "Gehaltsnachweis letzten 3 Monate", ...]
        PROMPT;

        try {
            $ai  = new AiService();
            $raw = $ai->rawPrompt(trim($prompt), maxTokens: 700);

            $parts = explode('---CHECKLIST---', $raw, 2);

            $coverLetter = trim($parts[0] ?? $raw);
            $checklist   = [];

            if (isset($parts[1])) {
                $jsonStr   = trim($parts[1]);
                $decoded   = json_decode($jsonStr, true);
                $checklist = is_array($decoded) ? $decoded : $this->defaultChecklist($occupation, (bool) $profile['pets']);
            }

            if (empty($checklist)) {
                $checklist = $this->defaultChecklist($occupation, (bool) $profile['pets']);
            }

            return [$coverLetter, $checklist];
        } catch (\Throwable $e) {
            log_message('warning', '[AiBewerbung] AI call failed: ' . $e->getMessage());
            return [$this->fallbackLetter($name, $location, $moveIn), $this->defaultChecklist($occupation, (bool) ($profile['pets'] ?? false))];
        }
    }

    private function defaultChecklist(string $occupation, bool $pets): array
    {
        $list = [
            'Personalausweis (Kopie)',
            'Gehaltsnachweis der letzten 3 Monate',
            'SCHUFA-Auskunft (nicht älter als 3 Monate)',
            'Mietschuldenfreiheitsbescheinigung vom Vorvermieter',
            'Selbstauskunft',
        ];

        if (str_contains(mb_strtolower($occupation), 'selbstständ') || str_contains(mb_strtolower($occupation), 'freiberuf')) {
            $list[] = 'Einkommenssteuerbescheid (letzten 2 Jahre)';
            $list[] = 'BWA oder Gewinn-und-Verlust-Rechnung';
        }

        if ($pets) {
            $list[] = 'Bescheinigung über Tierhaltung / Referenz Vorvermieter';
        }

        return $list;
    }

    private function fallbackLetter(string $name, string $location, string $moveIn): string
    {
        return "Sehr geehrte Damen und Herren,\n\n"
            . "mit großem Interesse habe ich Ihr Inserat in {$location} gesehen "
            . "und möchte mich hiermit als Mieter bewerben.\n\n"
            . "Ich bin {$name} und suche eine neue Wohnung zum Einzug {$moveIn}. "
            . "Ich bin ein zuverlässiger und ordentlicher Mieter und freue mich auf ein persönliches Gespräch.\n\n"
            . "Über eine positive Rückmeldung würde ich mich sehr freuen.\n\n"
            . "Mit freundlichen Grüßen\n{$name}";
    }
}
