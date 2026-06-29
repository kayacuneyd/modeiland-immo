<?php

namespace App\Modules\Estate\Filters;

use App\Modules\Estate\Services\OwnerAuthService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * Protects /owner/* routes.
 * Validates the estate_owner_token cookie against owner_sessions DB.
 * On failure: redirects to /owner/login.
 * Sets estate_owner_id in CI4 session for downstream controllers.
 */
class OwnerAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): mixed
    {
        $auth    = new OwnerAuthService();
        $ownerId = $auth->validateSession();

        if (! $ownerId) {
            // Clear any stale cookie
            setcookie('estate_owner_token', '', [
                'expires'  => time() - 3600,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            return redirect()->to(site_url('owner/login'))
                             ->with('info', 'Bitte melden Sie sich an.');
        }

        // Make owner_id available to controllers via CI4 session
        session()->set('estate_owner_id', $ownerId);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        return null;
    }
}
