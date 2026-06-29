<?php

namespace App\Modules\Estate\Models;

use CodeIgniter\Model;

class ListingConsentModel extends Model
{
    protected $table         = 'listing_consents';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'owner_id', 'listing_id', 'consent_version', 'accepted_at',
        'ip_address', 'user_agent', 'approved_photos',
        'approved_contact_method', 'approved_ai_rewrite',
    ];
    protected $useTimestamps = false;

    public function latestForListing(int $listingId): ?array
    {
        return $this->where('listing_id', $listingId)
                    ->orderBy('id', 'DESC')
                    ->first();
    }
}
