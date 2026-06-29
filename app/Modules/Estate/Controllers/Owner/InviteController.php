<?php

namespace App\Modules\Estate\Controllers\Owner;

use App\Core\Controllers\BaseWebController;
use App\Modules\Estate\Services\OwnerAuthService;

/**
 * Handles the invite link landing page: /einladung/<token>
 *
 * Flow:
 *  1. Validate raw token against owner_invites (hash comparison, TTL, status).
 *  2. Create long-lived owner session (cookie + DB row).
 *  3. Log security event.
 *  4. Redirect to owner's pending draft (if any) or panel.
 */
class InviteController extends BaseWebController
{
    private OwnerAuthService $auth;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->auth = new OwnerAuthService();
    }

    /** GET /einladung/<token> */
    public function accept(string $rawToken): mixed
    {
        // If already authenticated, skip invite flow → panel
        if ($this->auth->validateSession()) {
            return redirect()->to(site_url('owner/panel'));
        }

        $owner = $this->auth->validateInviteToken($rawToken);

        if (! $owner) {
            $this->auth->logSecurityEvent('invite_invalid', null, ['token_prefix' => substr($rawToken, 0, 8)]);
            return $this->inviteError('invalid');
        }

        // Create session (sets HttpOnly cookie)
        $this->auth->createSession((int) $owner['id']);
        $this->auth->logSecurityEvent('invite_used', (int) $owner['id']);

        // Redirect to pending draft listing if one exists, otherwise panel
        $pendingDraft = db_connect()
            ->table('listings')
            ->where('owner_id', $owner['id'])
            ->where('status', 'draft')
            ->orderBy('created_at', 'ASC')
            ->get()->getRowArray();

        if ($pendingDraft) {
            return redirect()->to(site_url("owner/draft/{$pendingDraft['id']}"));
        }

        return redirect()->to(site_url('owner/panel'))
                         ->with('success', 'Willkommen! Sie sind jetzt angemeldet.');
    }

    /** GET /owner/logout */
    public function logout(): \CodeIgniter\HTTP\RedirectResponse
    {
        $ownerId = session()->get('estate_owner_id');
        if ($ownerId) {
            $this->auth->logSecurityEvent('logout', (int) $ownerId);
        }
        $this->auth->destroySession();
        session()->destroy();

        return redirect()->to(site_url('owner/login'))
                         ->with('success', 'Sie wurden abgemeldet.');
    }

    // ─── Private helpers ───────────────────────────────────────────────────────

    private function inviteError(string $reason): string
    {
        $this->setSeo(['title' => 'Einladung ungültig — modeiland', 'robots' => 'noindex']);

        return $this->render('App\Modules\Estate\Views\owner\invite_error', [
            'reason' => $reason,
        ]);
    }
}
