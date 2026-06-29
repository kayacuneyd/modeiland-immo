<?php

namespace App\Modules\Estate\Models;

use CodeIgniter\Model;

class OwnerListingChargeModel extends Model
{
    protected $table         = 'owner_listing_charges';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'owner_id', 'listing_id', 'amount_cents', 'status',
        'provider_payment_id', 'stripe_session_id',
    ];
    protected $useTimestamps = false;

    public function isPaid(int $listingId): bool
    {
        return (bool) $this->where('listing_id', $listingId)
                           ->where('status', 'paid')
                           ->first();
    }

    public function findBySessionId(string $sessionId): ?array
    {
        return $this->where('stripe_session_id', $sessionId)->first();
    }
}
