<?php

namespace App\Modules\Estate\Services;

/**
 * Rule-based fit score + cached AI one-liner reason.
 *
 * Shared hosting constraint: NO real-time embeddings.
 * Scoring is pure PHP arithmetic (O(1) per listing).
 * AI reason is one short call per (listing × filters_hash) pair,
 * result stored in match_reasons table and reused on subsequent hits.
 *
 * Score breakdown (total 100):
 *   warmmiete  ≤ budget          → 35 pts
 *   rooms      ≥ min_rooms       → 25 pts
 *   m2         ≥ min_m2          → 20 pts
 *   location   keyword match     → 15 pts
 *   type       match             →  5 pts
 */
class AiMatchingService
{
    private const REASON_CACHE_DAYS = 7;

    /**
     * Returns ['score' => int 0-100, 'reason' => ''] — reason filled separately.
     */
    public function computeFitScore(array $listing, array $filters): array
    {
        $score = 0;

        // Warmmiete ≤ budget (filter: max_warmmiete in euros, listing in cents)
        if (! empty($filters['max_warmmiete']) && $listing['warmmiete']) {
            $budgetCents = (int) $filters['max_warmmiete'] * 100;
            if ($listing['warmmiete'] <= $budgetCents) {
                // Gradient: full points at ≤80% budget, proportional above
                $ratio  = $listing['warmmiete'] / $budgetCents;
                $score += $ratio <= 0.8 ? 35 : (int) round(35 * (1 - ($ratio - 0.8) / 0.2));
            }
        } else {
            $score += 20; // no filter → partial credit
        }

        // Rooms ≥ min_rooms
        if (! empty($filters['rooms_min']) && $listing['rooms']) {
            if ((float) $listing['rooms'] >= (float) $filters['rooms_min']) {
                $score += 25;
            }
        } else {
            $score += 15;
        }

        // m² ≥ min_m2
        if (! empty($filters['min_m2']) && $listing['m2']) {
            if ((float) $listing['m2'] >= (float) $filters['min_m2']) {
                $score += 20;
            }
        } else {
            $score += 12;
        }

        // Location keyword match
        if (! empty($filters['location']) && ! empty($listing['location_approx'])) {
            $needle   = mb_strtolower(trim($filters['location']));
            $haystack = mb_strtolower($listing['location_approx']);
            $score += str_contains($haystack, $needle) ? 15 : 0;
        } else {
            $score += 8;
        }

        // Property type
        if (! empty($filters['type']) && ! empty($listing['type'])) {
            $score += $listing['type'] === $filters['type'] ? 5 : 0;
        } else {
            $score += 5;
        }

        return ['score' => min(100, $score), 'reason' => ''];
    }

    /**
     * Returns a one-line German match reason for this listing+filters combination.
     * Result is cached in match_reasons by filters_hash — AI not called twice for the same pair.
     * Returns empty string on failure (graceful degradation).
     */
    public function generateReason(array $listing, array $filters): string
    {
        $hash    = $this->filtersHash($filters);
        $db      = db_connect();
        $cacheTs = date('Y-m-d H:i:s', strtotime('-' . self::REASON_CACHE_DAYS . ' days'));

        // Cache hit
        $cached = $db->table('match_reasons')
            ->where('listing_id', $listing['id'])
            ->where('filters_hash', $hash)
            ->where('created_at >', $cacheTs)
            ->get()->getRowArray();

        if ($cached) {
            return $cached['reason_text'] ?? '';
        }

        // Build a short, rule-based reason to avoid an AI call whenever possible
        $reason = $this->buildRuleReason($listing, $filters);

        // Only call AI if we have a description (meaningful input) and no rule reason
        if ($reason === '' && ! empty($listing['ai_description'])) {
            $reason = $this->callAiReason($listing, $filters);
        }

        // Persist (upsert)
        $score = $this->computeFitScore($listing, $filters)['score'];
        $now   = date('Y-m-d H:i:s');

        $existing = $db->table('match_reasons')
            ->where('listing_id', $listing['id'])
            ->where('filters_hash', $hash)
            ->get()->getRowArray();

        if ($existing) {
            $db->table('match_reasons')->where('id', $existing['id'])->update([
                'reason_text' => $reason,
                'score_total' => $score,
                'created_at'  => $now,
            ]);
        } else {
            $db->table('match_reasons')->insert([
                'listing_id'   => $listing['id'],
                'filters_hash' => $hash,
                'reason_text'  => $reason,
                'score_total'  => $score,
                'created_at'   => $now,
            ]);
        }

        return $reason;
    }

    /**
     * Sort listings by fit score (desc), returning annotated array.
     * Keeps original order for equal scores (stable).
     * AI reasons NOT generated here — only on detail page to limit API cost.
     */
    public function sortByFit(array $listings, array $filters): array
    {
        if (empty($filters)) {
            return $listings;
        }

        $scored = array_map(function ($listing) use ($filters) {
            $result             = $this->computeFitScore($listing, $filters);
            $listing['_score']  = $result['score'];
            return $listing;
        }, $listings);

        usort($scored, fn($a, $b) => $b['_score'] <=> $a['_score']);

        return $scored;
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function filtersHash(array $filters): string
    {
        ksort($filters);
        return hash('sha256', json_encode($filters));
    }

    private function buildRuleReason(array $listing, array $filters): string
    {
        $parts = [];

        if (! empty($filters['rooms_min']) && $listing['rooms'] >= (float) $filters['rooms_min']) {
            $parts[] = (string) $listing['rooms'] . ' Zimmer';
        }

        if (! empty($filters['max_warmmiete']) && $listing['warmmiete']) {
            $budgetCents = (int) $filters['max_warmmiete'] * 100;
            if ($listing['warmmiete'] <= $budgetCents) {
                $parts[] = 'in Ihrem Budget';
            }
        }

        if (! empty($filters['location']) && ! empty($listing['location_approx'])) {
            $needle = mb_strtolower(trim($filters['location']));
            if (str_contains(mb_strtolower($listing['location_approx']), $needle)) {
                $parts[] = 'in ' . esc($listing['location_approx']);
            }
        }

        return implode(', ', $parts);
    }

    private function callAiReason(array $listing, array $filters): string
    {
        try {
            $ai = new \App\Modules\Estate\Libraries\AiService();

            $filterLines = [];
            if (! empty($filters['max_warmmiete'])) {
                $filterLines[] = 'Budget: bis ' . $filters['max_warmmiete'] . ' €/Monat';
            }
            if (! empty($filters['rooms_min'])) {
                $filterLines[] = 'Mindest-Zimmer: ' . $filters['rooms_min'];
            }
            if (! empty($filters['location'])) {
                $filterLines[] = 'Standort: ' . $filters['location'];
            }

            $prompt = "Erkläre in EINEM kurzen deutschen Satz (max. 12 Wörter), warum dieses Inserat zu den Suchkriterien passt.\n\n"
                . "Suchkriterien:\n" . implode("\n", $filterLines) . "\n\n"
                . "Inserat:\n"
                . "Standort: " . ($listing['location_approx'] ?? '—') . "\n"
                . "Warmmiete: " . ($listing['warmmiete'] ? number_format($listing['warmmiete'] / 100, 0, ',', '.') . ' €' : '—') . "\n"
                . "Zimmer: " . ($listing['rooms'] ?? '—') . "\n"
                . "Antwort (nur der Satz, kein JSON, kein Präfix):";

            $result = $ai->rawPrompt($prompt, maxTokens: 60);
            return trim($result);
        } catch (\Throwable) {
            return '';
        }
    }
}
