<?php

namespace App\Core\Auth;

class AuthService
{
    private AuthModel $model;

    public function __construct()
    {
        $this->model = new AuthModel();
    }

    public function login(string $email, string $password): bool
    {
        $user = $this->model->findByEmail($email);

        if ($user === null) {
            return false;
        }

        if (! $this->model->verifyPassword($password, $user['password_hash'])) {
            return false;
        }

        $permissions = json_decode($user['role_permissions'] ?? '[]', true) ?: [];

        session()->regenerate(true);
        session()->set([
            'admin_user_id'    => $user['id'],
            'admin_name'       => $user['name'],
            'admin_email'      => $user['email'],
            'admin_role'       => $user['role_slug'],
            'admin_role_name'  => $user['role_name'],
            'admin_permissions'=> $permissions,
        ]);

        return true;
    }

    public function logout(): void
    {
        session()->destroy();
    }

    public function check(): bool
    {
        return session()->has('admin_user_id') && session()->get('admin_user_id') !== null;
    }

    public function user(): array
    {
        if (! $this->check()) {
            return [];
        }

        return [
            'id'          => session()->get('admin_user_id'),
            'name'        => session()->get('admin_name'),
            'email'       => session()->get('admin_email'),
            'role'        => session()->get('admin_role'),
            'role_name'   => session()->get('admin_role_name'),
            'permissions' => session()->get('admin_permissions') ?? [],
        ];
    }

    public function can(string $permission): bool
    {
        if (! $this->check()) {
            return false;
        }

        $permissions = session()->get('admin_permissions') ?? [];

        // Wildcard: ["*"] her şeyi kapsar
        if (in_array('*', $permissions, true)) {
            return true;
        }

        // Tam eşleşme
        if (in_array($permission, $permissions, true)) {
            return true;
        }

        // Prefix wildcard: "pages.*" → pages.create, pages.edit, pages.delete
        $parts = explode('.', $permission);
        if (count($parts) >= 2) {
            $prefix = $parts[0] . '.*';
            if (in_array($prefix, $permissions, true)) {
                return true;
            }
        }

        return false;
    }
}
