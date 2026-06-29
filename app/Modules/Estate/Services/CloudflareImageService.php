<?php

namespace App\Modules\Estate\Services;

use App\Modules\Estate\Config\Estate;

/**
 * Cloudflare Images API wrapper.
 *
 * Handles uploading local listing images to Cloudflare Images and
 * building delivery URLs with variants.
 *
 * Docs: https://developers.cloudflare.com/images/cloudflare-images/
 *
 * No VPS required — CF Images is a fully external service.
 * The origin stays on shared hosting; only the CDN URL is stored.
 */
class CloudflareImageService
{
    private Estate $config;
    private string $apiBase;

    public function __construct()
    {
        $this->config  = config(Estate::class);
        $this->apiBase = "https://api.cloudflare.com/client/v4/accounts/{$this->config->cloudflareAccountId}/images/v1";
    }

    /**
     * Upload a local file to Cloudflare Images.
     * Returns ['cf_image_id' => string, 'cf_url' => string] on success.
     * Throws \RuntimeException on failure.
     */
    public function upload(string $localPath, int $listingId, int $sort = 0): array
    {
        if (! file_exists($localPath)) {
            throw new \RuntimeException("File not found: {$localPath}");
        }

        $metadata = json_encode([
            'listing_id' => (string) $listingId,
            'sort'       => (string) $sort,
        ]);

        $ch = curl_init($this->apiBase);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->config->cloudflareImagesToken,
            ],
            CURLOPT_POSTFIELDS     => [
                'file'     => new \CURLFile($localPath),
                'metadata' => $metadata,
                'requireSignedURLs' => 'false',
            ],
            CURLOPT_TIMEOUT        => 30,
        ]);

        $raw      = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $httpCode >= 400) {
            throw new \RuntimeException("Cloudflare Images upload failed (HTTP {$httpCode}): {$raw}");
        }

        $body = json_decode($raw, true);

        if (empty($body['success']) || empty($body['result']['id'])) {
            $err = $body['errors'][0]['message'] ?? 'unknown error';
            throw new \RuntimeException("Cloudflare Images API error: {$err}");
        }

        $cfImageId = $body['result']['id'];

        return [
            'cf_image_id' => $cfImageId,
            'cf_url'      => $this->buildUrl($cfImageId),
        ];
    }

    /**
     * Delete an image from Cloudflare Images by its CF image ID.
     */
    public function delete(string $cfImageId): void
    {
        $url = $this->apiBase . '/' . rawurlencode($cfImageId);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->config->cloudflareImagesToken,
            ],
            CURLOPT_TIMEOUT => 15,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    /**
     * Build a delivery URL for a given CF image ID and variant.
     * Default variant 'public' must exist in your CF Images configuration.
     */
    public function buildUrl(string $cfImageId, string $variant = 'public'): string
    {
        $base = rtrim($this->config->cloudflareImagesDelivery, '/');
        return "{$base}/{$cfImageId}/{$variant}";
    }

    public function isConfigured(): bool
    {
        return $this->config->cloudflareAccountId !== ''
            && $this->config->cloudflareImagesToken !== ''
            && $this->config->cloudflareImagesDelivery !== '';
    }
}
