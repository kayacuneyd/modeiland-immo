<?php

namespace App\Modules\Estate\Models;

use CodeIgniter\Model;

class ListingImageModel extends Model
{
    protected $table         = 'listing_images';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['listing_id', 'path', 'sort', 'approved', 'cf_image_id', 'cf_url'];
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';

    public function getApprovedForListing(int $listingId): array
    {
        return $this->where('listing_id', $listingId)
                    ->where('approved', 1)
                    ->orderBy('sort', 'ASC')
                    ->findAll();
    }

    /**
     * Returns the display URL for an image row.
     * Prefers the Cloudflare CDN URL when available; falls back to local path.
     */
    public static function displayUrl(array $image): string
    {
        if (! empty($image['cf_url'])) {
            return $image['cf_url'];
        }
        // Local path — serve via /uploads/ if it's a relative path
        $path = $image['path'] ?? '';
        return str_starts_with($path, '/') ? $path : base_url('uploads/' . ltrim($path, '/'));
    }
}
