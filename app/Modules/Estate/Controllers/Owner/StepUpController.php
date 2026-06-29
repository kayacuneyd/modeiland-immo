<?php

namespace App\Modules\Estate\Controllers\Owner;

use App\Core\Controllers\BaseWebController;
use App\Modules\Estate\Services\OwnerAuthService;

/**
 * Step-up re-authentication for sensitive operations:
 *   - listing entfernen, e-mail/phone change, account close, direct contact enable
 *
 * Flow:
 *   Sensitive action → redirect to /owner/stepup?next=<encoded-url>
 *   → owner verifies (OTP or password) → isStepUpValid() → redirect back to next URL
 */
class StepUpController extends BaseWebController
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

    /** GET /owner/stepup?next=<url>&action=<action_label> */
    public function form(): string
    {
        $ownerId = (int) session()->get('estate_owner_id');
        $owner   = db_connect()->table('owners')->where('id', $ownerId)->get()->getRowArray();

        if (! $owner) {
            return redirect()->to(site_url('owner/login'));
        }

        $nextUrl = $this->request->getGet('next') ?? site_url('owner/panel');
        $action  = $this->request->getGet('action') ?? 'diese Aktion';

        // Store intended URL in session (not query string — to survive POST)
        session()->set('estate_stepup_next', $nextUrl);
        session()->set('estate_stepup_action', $action);

        $hasPassword = ! empty($owner['password_hash']);
        $hasEmail    = ! empty($owner['email']);

        // If email exists and no password → generate OTP
        $otpSent = false;
        if ($hasEmail && ! $hasPassword) {
            $otp     = $this->auth->generateOtp($ownerId);
            $otpSent = $this->sendOtpEmail($owner, $otp);
        }

        $this->setSeo(['title' => 'Identität bestätigen — modeiland', 'robots' => 'noindex']);

        return $this->render('App\Modules\Estate\Views\owner\stepup', [
            'owner'       => $owner,
            'action'      => $action,
            'hasPassword' => $hasPassword,
            'hasEmail'    => $hasEmail,
            'otpSent'     => $otpSent,
        ]);
    }

    /** POST /owner/stepup */
    public function verify(): \CodeIgniter\HTTP\RedirectResponse
    {
        $ownerId = (int) session()->get('estate_owner_id');
        $owner   = db_connect()->table('owners')->where('id', $ownerId)->get()->getRowArray();

        if (! $owner) {
            return redirect()->to(site_url('owner/login'));
        }

        $method   = $this->request->getPost('method');
        $input    = $this->request->getPost('credential') ?? '';
        $verified = false;

        if ($method === 'otp') {
            $verified = $this->auth->verifyOtp($ownerId, trim($input));
        } elseif ($method === 'password') {
            $verified = $this->auth->verifyPassword($ownerId, $input);
        }

        if (! $verified) {
            return redirect()->back()->with('error', 'Bestätigung fehlgeschlagen. Bitte erneut versuchen.');
        }

        $nextUrl = session()->get('estate_stepup_next') ?? site_url('owner/panel');
        session()->remove('estate_stepup_next');
        session()->remove('estate_stepup_action');

        return redirect()->to($nextUrl);
    }

    // ─── Private helpers ───────────────────────────────────────────────────────

    private function sendOtpEmail(array $owner, string $otp): bool
    {
        if (empty($owner['email'])) {
            return false;
        }

        try {
            $emailSvc = \Config\Services::email();
            $emailSvc->setTo($owner['email']);
            $emailSvc->setSubject('Ihr Bestätigungscode — modeiland');
            $emailSvc->setMessage(view(
                'App\Modules\Estate\Views\owner\email_otp',
                ['owner' => $owner, 'otp' => $otp]
            ));
            $emailSvc->send();
            return true;
        } catch (\Throwable $e) {
            log_message('error', '[Estate] OTP email failed: ' . $e->getMessage());
            return false;
        }
    }
}
