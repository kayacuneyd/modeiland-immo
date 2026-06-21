<?php

namespace App\Core\Auth;

use App\Core\Models\BaseModel;

class AuthModel extends BaseModel
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'name', 'email', 'password_hash', 'role_id', 'is_active',
    ];

    public function findByEmail(string $email): ?array
    {
        return $this->select('users.*, roles.slug as role_slug, roles.name as role_name, roles.permissions as role_permissions')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->where('users.email', $email)
            ->where('users.is_active', 1)
            ->first();
    }

    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    public function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public function createAdmin(string $name, string $email, string $password, int $roleId = 1): int|string
    {
        return $this->insert([
            'name'          => $name,
            'email'         => $email,
            'password_hash' => $this->hashPassword($password),
            'role_id'       => $roleId,
            'is_active'     => 1,
        ]);
    }
}
