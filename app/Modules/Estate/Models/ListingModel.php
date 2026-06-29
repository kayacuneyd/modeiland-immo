<?php

namespace App\Modules\Estate\Models;

use CodeIgniter\Model;

class ListingModel extends Model
{
    protected $table         = 'listings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'owner_id', 'source_url', 'status',
        'kaltmiete', 'warmmiete', 'nebenkosten', 'deposit',
        'rooms', 'm2', 'location_text', 'location_approx',
        'available_from', 'ai_description', 'source_text_raw',
        'type', 'is_first_free', 'ai_import_status',
    ];
    protected $useTimestamps = true;

    public function getLive(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $builder = $this->where('status', 'live');

        if (! empty($filters['min_warmmiete'])) {
            $builder->where('warmmiete >=', (int) $filters['min_warmmiete'] * 100);
        }
        if (! empty($filters['max_warmmiete'])) {
            $builder->where('warmmiete <=', (int) $filters['max_warmmiete'] * 100);
        }
        if (! empty($filters['min_rooms'])) {
            $builder->where('rooms >=', (float) $filters['min_rooms']);
        }
        if (! empty($filters['min_m2'])) {
            $builder->where('m2 >=', (float) $filters['min_m2']);
        }
        if (! empty($filters['location'])) {
            $builder->like('location_approx', $filters['location']);
        }

        return $builder->orderBy('created_at', 'DESC')
                       ->paginate($perPage, 'default', $page);
    }

    public function countLive(array $filters = []): int
    {
        $builder = $this->where('status', 'live');

        if (! empty($filters['min_warmmiete'])) {
            $builder->where('warmmiete >=', (int) $filters['min_warmmiete'] * 100);
        }
        if (! empty($filters['max_warmmiete'])) {
            $builder->where('warmmiete <=', (int) $filters['max_warmmiete'] * 100);
        }
        if (! empty($filters['min_rooms'])) {
            $builder->where('rooms >=', (float) $filters['min_rooms']);
        }
        if (! empty($filters['min_m2'])) {
            $builder->where('m2 >=', (float) $filters['min_m2']);
        }
        if (! empty($filters['location'])) {
            $builder->like('location_approx', $filters['location']);
        }

        return (int) $builder->countAllResults(false);
    }

    public function getByOwner(int $ownerId): array
    {
        return $this->where('owner_id', $ownerId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function isFirstFreeEligible(int $ownerId): bool
    {
        $count = $this->where('owner_id', $ownerId)
                      ->whereIn('status', ['draft', 'live', 'paused'])
                      ->countAllResults();
        return $count === 0;
    }
}
