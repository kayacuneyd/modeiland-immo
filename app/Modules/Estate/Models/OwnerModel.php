<?php

namespace App\Modules\Estate\Models;

use CodeIgniter\Model;

class OwnerModel extends Model
{
    protected $table         = 'owners';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'status', 'display_name', 'email', 'phone',
        'password_hash', 'login_method', 'source_url', 'outreach_note',
    ];
    protected $useTimestamps = true;

    public function getLeads(): array
    {
        return $this->where('status', 'lead')->orderBy('created_at', 'DESC')->findAll();
    }

    public function getActive(): array
    {
        return $this->where('status', 'active')->orderBy('created_at', 'DESC')->findAll();
    }

    public function countListings(int $ownerId): int
    {
        return (int) $this->db->table('listings')
            ->where('owner_id', $ownerId)
            ->countAllResults();
    }
}
