<?php

namespace App\Modules\Estate\Models;

use CodeIgniter\Model;

class ListingImageModel extends Model
{
    protected $table         = 'listing_images';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['listing_id', 'path', 'sort', 'approved'];
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';

    public function getApprovedForListing(int $listingId): array
    {
        return $this->where('listing_id', $listingId)
                    ->where('approved', 1)
                    ->orderBy('sort', 'ASC')
                    ->findAll();
    }
}
