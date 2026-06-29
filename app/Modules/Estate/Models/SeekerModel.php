<?php

namespace App\Modules\Estate\Models;

use CodeIgniter\Model;

class SeekerModel extends Model
{
    protected $table         = 'seekers';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'email', 'password_hash', 'login_method', 'subscription_status',
    ];
    protected $useTimestamps = true;

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    public function isSubscribed(int $seekerId): bool
    {
        $seeker = $this->find($seekerId);
        return $seeker && in_array($seeker['subscription_status'], ['active', 'trial'], true);
    }
}
