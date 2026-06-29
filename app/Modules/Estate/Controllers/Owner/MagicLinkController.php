<?php

namespace App\Modules\Estate\Controllers\Owner;

use App\Core\Controllers\BaseWebController;
use App\Modules\Estate\Services\OwnerAuthService;

/**
 * Magic link authentication — for owners who have completed profile upgrade.
 * Links are single-use and expire in 15 minutes.
 */
class MagicLinkController extends BaseWebController
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

    /** GET /owner/login */
    public function loginForm(): string
    {
        // Already logged in → panel
        if ($this->auth->validateSession()) {
            return redirect()->to(site_url('owner/panel'));
        }

        $this->setSeo(['title' => 'Anmelden — modeiland', 'robots' => 'noindex']);

        return $this->render('App\Modules\Estate\Views\owner\login', []);
    }

    /** POST /owner/login — send magic link email */
    public function sendLink(): \CodeIgniter\HTTP\RedirectResponse
    {
        $email = trim($this->request->getPost('email') ?? '');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'Bitte geben Sie eine gültige E-Mail-Adresse ein.');
        }

        $owner = db_connect()->table('owners')
            ->where('email', $email)
            ->where('status', 'active')
            ->get()->getRowArray();

        // Always redirect to "sent" page — don't reveal whether email exists (timing attack prevention)
        if ($owner) {
            $rawToken = $this->auth->generateMagicLink((int) $owner['id']);
            $this->sendMagicLinkEmail($owner, $rawToken);
        } else {
            // Log attempt for unknown email without revealing result
            $this->auth->logSecurityEvent('magic_link_unknown_email', null, ['email_hash' => hash('sha256', $email)]);
        }

        return redirect()->to(site_url('owner/login/gesendet'));
    }

    /** GET /owner/login/gesendet */
    public function sent(): string
    {
        $this->setSeo(['title' => 'Link gesendet — modeiland', 'robots' => 'noindex']);
        return $this->render('App\Modules\Estate\Views\owner\login_sent', []);
    }

    /** GET /owner/magiclink/<token> — validate and log in */
    public function accept(string $rawToken): mixed
    {
        $ownerId = $this->auth->validateMagicLink($rawToken);

        if (! $ownerId) {
            $this->auth->logSecurityEvent('magic_link_invalid', null, ['token_prefix' => substr($rawToken, 0, 8)]);

            return $this->render('App\Modules\Estate\Views\owner\invite_error', [
                'reason' => 'invalid',
            ]);
        }

        $this->auth->createSession($ownerId);

        return redirect()->to(site_url('owner/panel'))
                         ->with('success', 'Sie sind jetzt angemeldet.');
    }

    // ─── Private helpers ───────────────────────────────────────────────────────

    private function sendMagicLinkEmail(array $owner, string $rawToken): void
    {
        $link = site_url("owner/magiclink/{$rawToken}");

        try {
            $emailSvc = \Config\Services::email();
            $emailSvc->setTo($owner['email']);
            $emailSvc->setSubject('Ihr Anmelde-Link — modeiland');
            $emailSvc->setMessage(view(
                'App\Modules\Estate\Views\owner\email_magic_link',
                ['owner' => $owner, 'link' => $link]
            ));
            $emailSvc->send();
        } catch (\Throwable $e) {
            log_message('error', '[Estate] Magic link email failed: ' . $e->getMessage());
        }
    }
}
