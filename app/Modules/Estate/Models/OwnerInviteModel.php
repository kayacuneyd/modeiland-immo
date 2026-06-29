<?php

namespace App\Modules\Estate\Models;

use CodeIgniter\Model;

class OwnerInviteModel extends Model
{
    protected $table         = 'owner_invites';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['owner_id', 'token_hash', 'expires_at', 'status'];
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';

    public function findActiveByTokenHash(string $hash): ?array
    {
        return $this->where('token_hash', $hash)
                    ->where('status', 'active')
                    ->where('expires_at >=', date('Y-m-d H:i:s'))
                    ->first();
    }

    public function revokeForOwner(int $ownerId): void
    {
        $this->where('owner_id', $ownerId)->set(['status' => 'revoked'])->update();
    }
}
