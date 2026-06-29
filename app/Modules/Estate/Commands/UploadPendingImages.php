<?php

namespace App\Modules\Estate\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Modules\Estate\Services\CloudflareImageService;

/**
 * spark estate:upload-images
 *
 * Uploads approved listing images that have not yet been sent to
 * Cloudflare Images (cf_url IS NULL). Stores the returned cf_image_id
 * and cf_url in the listing_images row.
 *
 * Only runs if CloudflareImageService::isConfigured() is true.
 * Safe to run repeatedly — already-uploaded images are skipped.
 *
 * Run via Hostinger Scheduled Tasks:
 *   php /path/to/spark estate:upload-images
 *   Frequency: every 15 minutes, or after listing approval.
 */
class UploadPendingImages extends BaseCommand
{
    protected $group       = 'estate';
    protected $name        = 'estate:upload-images';
    protected $description = 'Uploads approved listing images to Cloudflare Images.';

    public function run(array $params): void
    {
        $cf = new CloudflareImageService();

        if (! $cf->isConfigured()) {
            CLI::write('[estate:upload-images] Cloudflare Images not configured — skipping.', 'yellow');
            return;
        }

        $db      = db_connect();
        $pending = $db->table('listing_images')
            ->where('approved', 1)
            ->where('cf_url IS NULL', null, false)
            ->where('path IS NOT NULL', null, false)
            ->get()->getResultArray();

        if (empty($pending)) {
            CLI::write('[estate:upload-images] No pending images.', 'green');
            return;
        }

        $uploaded = 0;
        $failed   = 0;

        foreach ($pending as $img) {
            // path is stored relative to WRITEPATH or as absolute — normalise
            $localPath = $this->resolvePath($img['path']);

            if (! file_exists($localPath)) {
                CLI::write("[estate:upload-images] File not found, skipping: {$localPath}", 'yellow');
                $failed++;
                continue;
            }

            try {
                $result = $cf->upload($localPath, (int) $img['listing_id'], (int) $img['sort']);

                $db->table('listing_images')->where('id', $img['id'])->update([
                    'cf_image_id' => $result['cf_image_id'],
                    'cf_url'      => $result['cf_url'],
                ]);

                CLI::write("[estate:upload-images] Uploaded image #{$img['id']} → {$result['cf_url']}", 'green');
                $uploaded++;
            } catch (\Throwable $e) {
                log_message('error', "[estate:upload-images] Image #{$img['id']} failed: " . $e->getMessage());
                CLI::write("[estate:upload-images] Failed image #{$img['id']}: " . $e->getMessage(), 'red');
                $failed++;
            }
        }

        CLI::write("[estate:upload-images] Done. Uploaded: {$uploaded}, Failed: {$failed}.", 'green');
    }

    private function resolvePath(string $path): string
    {
        if (str_starts_with($path, '/')) {
            return $path;
        }
        // Relative paths stored from WRITEPATH
        return rtrim(WRITEPATH, '/') . '/' . ltrim($path, '/');
    }
}
