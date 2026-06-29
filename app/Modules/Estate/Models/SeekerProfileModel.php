<?php

namespace App\Modules\Estate\Models;

use CodeIgniter\Model;

class SeekerProfileModel extends Model
{
    protected $table         = 'seeker_profiles';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'seeker_id', 'name', 'move_in_date', 'household_size',
        'occupation', 'income_range_cents', 'pets', 'notes',
    ];
    protected $useTimestamps = true;

    public function findBySeeker(int $seekerId): ?array
    {
        return $this->where('seeker_id', $seekerId)->first();
    }

    public function upsert(int $seekerId, array $data): void
    {
        $existing = $this->findBySeeker($seekerId);
        if ($existing) {
            $this->update($existing['id'], $data);
        } else {
            $this->insert(array_merge($data, ['seeker_id' => $seekerId]));
        }
    }

    public function isComplete(int $seekerId): bool
    {
        $p = $this->findBySeeker($seekerId);
        return $p && ! empty($p['name']) && ! empty($p['occupation']);
    }
}
