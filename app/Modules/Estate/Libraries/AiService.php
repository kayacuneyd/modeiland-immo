<?php

namespace App\Modules\Estate\Libraries;

use App\Modules\Estate\Config\Estate;

/**
 * Provider-agnostic AI service for listing import.
 * Swap AI_PROVIDER in .env without touching callers.
 */
class AiService
{
    private Estate $config;

    public function __construct()
    {
        $this->config = config(Estate::class);
    }

    /**
     * Analyse raw listing text and return structured fields + German description.
     *
     * Returns array with keys:
     *   kaltmiete, warmmiete, nebenkosten, deposit, rooms, m2,
     *   location_text, location_approx, available_from, ai_description
     * or ['error' => 'message'] on failure.
     *
     * Caller must handle 'error' key and set listing status to 'draft_pending'.
     */
    public function analyseListingText(string $rawText, ?string $sourceUrl = null): array
    {
        if (empty($this->config->aiApiKey)) {
            return ['error' => 'AI_API_KEY not configured'];
        }

        $prompt = $this->buildPrompt($rawText, $sourceUrl);

        return match ($this->config->aiProvider) {
            'anthropic' => $this->callAnthropic($prompt),
            default     => $this->callOpenAi($prompt),
        };
    }

    /**
     * Send a free-form prompt and return the raw text response.
     * Used by AiMatchingService and AiBewerbungService for short, targeted calls.
     * Throws \RuntimeException on hard failure; caller should catch.
     */
    public function rawPrompt(string $prompt, int $maxTokens = 200): string
    {
        if (empty($this->config->aiApiKey)) {
            throw new \RuntimeException('AI_API_KEY not configured');
        }

        return match ($this->config->aiProvider) {
            'anthropic' => $this->rawAnthropic($prompt, $maxTokens),
            default     => $this->rawOpenAi($prompt, $maxTokens),
        };
    }

    // ─── Private helpers ────────────────────────────────────────────────

    private function buildPrompt(string $rawText, ?string $sourceUrl): string
    {
        $urlLine = $sourceUrl ? "Quell-URL: {$sourceUrl}\n\n" : '';

        return <<<PROMPT
        Du bist ein Assistent für eine deutsche Immobilienplattform.
        Analysiere den folgenden Anzeigentext und extrahiere strukturierte Daten.
        Schreibe dann eine originelle, ansprechende Beschreibung auf Deutsch
        (NICHT den Originaltext kopieren — eigene, klare Formulierung).

        {$urlLine}Originaltext:
        ---
        {$rawText}
        ---

        Antworte NUR mit gültigem JSON, kein Markdown:
        {
          "kaltmiete": <integer_cents_or_null>,
          "warmmiete": <integer_cents_or_null>,
          "nebenkosten": <integer_cents_or_null>,
          "deposit": <integer_cents_or_null>,
          "rooms": <float_or_null>,
          "m2": <float_or_null>,
          "location_text": "<vollständige Adresse oder null>",
          "location_approx": "<Stadtteil/Stadt, kein Straßenname>",
          "available_from": "<YYYY-MM-DD oder 'sofort' oder null>",
          "ai_description": "<originelle deutsche Beschreibung, 3-5 Sätze>"
        }
        PROMPT;
    }

    private function callOpenAi(string $prompt): array
    {
        $payload = json_encode([
            'model'       => $this->config->aiModel,
            'messages'    => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.4,
            'max_tokens'  => 800,
        ]);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 25,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->config->aiApiKey,
            ],
        ]);

        $raw   = curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        if ($errno || $raw === false) {
            return ['error' => 'AI request timeout or network error'];
        }

        $resp = json_decode($raw, true);
        $text = $resp['choices'][0]['message']['content'] ?? null;

        if (! $text) {
            return ['error' => 'Empty AI response'];
        }

        return $this->parseJsonResponse($text);
    }

    private function callAnthropic(string $prompt): array
    {
        $payload = json_encode([
            'model'      => $this->config->aiModel ?: 'claude-haiku-4-5-20251001',
            'max_tokens' => 800,
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ]);

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 25,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $this->config->aiApiKey,
                'anthropic-version: 2023-06-01',
            ],
        ]);

        $raw   = curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        if ($errno || $raw === false) {
            return ['error' => 'AI request timeout or network error'];
        }

        $resp = json_decode($raw, true);
        $text = $resp['content'][0]['text'] ?? null;

        if (! $text) {
            return ['error' => 'Empty AI response'];
        }

        return $this->parseJsonResponse($text);
    }

    private function rawOpenAi(string $prompt, int $maxTokens): string
    {
        $payload = json_encode([
            'model'       => $this->config->aiModel,
            'messages'    => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.5,
            'max_tokens'  => $maxTokens,
        ]);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->config->aiApiKey,
            ],
        ]);

        $raw   = curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        if ($errno || $raw === false) {
            throw new \RuntimeException('OpenAI request failed');
        }

        $resp = json_decode($raw, true);
        return trim($resp['choices'][0]['message']['content'] ?? '');
    }

    private function rawAnthropic(string $prompt, int $maxTokens): string
    {
        $payload = json_encode([
            'model'      => $this->config->aiModel ?: 'claude-haiku-4-5-20251001',
            'max_tokens' => $maxTokens,
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ]);

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $this->config->aiApiKey,
                'anthropic-version: 2023-06-01',
            ],
        ]);

        $raw   = curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        if ($errno || $raw === false) {
            throw new \RuntimeException('Anthropic request failed');
        }

        $resp = json_decode($raw, true);
        return trim($resp['content'][0]['text'] ?? '');
    }

    private function parseJsonResponse(string $text): array
    {
        // Strip potential markdown fences
        $text = preg_replace('/^```json\s*/i', '', trim($text));
        $text = preg_replace('/\s*```$/', '', $text);

        $data = json_decode($text, true);

        if (! is_array($data)) {
            return ['error' => 'AI returned invalid JSON'];
        }

        $allowed = [
            'kaltmiete', 'warmmiete', 'nebenkosten', 'deposit',
            'rooms', 'm2', 'location_text', 'location_approx',
            'available_from', 'ai_description',
        ];

        return array_intersect_key($data, array_flip($allowed));
    }
}
