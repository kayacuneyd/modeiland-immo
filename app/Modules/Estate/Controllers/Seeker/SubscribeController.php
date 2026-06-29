<?php

namespace App\Modules\Estate\Controllers\Seeker;

use App\Core\Controllers\BaseWebController;
use App\Modules\Estate\Config\Estate;
use App\Modules\Estate\Models\SeekerModel;
use App\Modules\Estate\Models\SubscriptionModel;
use App\Modules\Estate\Models\AuditLogModel;
use App\Modules\Estate\Services\StripeService;

/**
 * Seeker subscription flow.
 *
 * BILLING_ENABLED=false  →  trial bypass: creates a 'trial' subscription and sets session.
 * BILLING_ENABLED=true   →  redirects to Stripe Checkout.
 *
 * Routes:
 *   GET  /abonnieren                  → paywall view
 *   POST /abonnieren/checkout         → initiate checkout
 *   GET  /abonnieren/erfolg           → post-Stripe success landing
 *   GET  /abonnieren/abbrechen        → post-Stripe cancel landing
 *   POST /abonnieren/portal           → Stripe Customer Portal redirect
 */
class SubscribeController extends BaseWebController
{
    private Estate            $config;
    private SeekerModel       $seekers;
    private SubscriptionModel $subscriptions;
    private AuditLogModel     $audit;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->config        = config(Estate::class);
        $this->seekers       = new SeekerModel();
        $this->subscriptions = new SubscriptionModel();
        $this->audit         = new AuditLogModel();
    }

    /** GET /abonnieren — value-first paywall */
    public function index(): string
    {
        $seeker = $this->currentSeeker();

        $this->setSeo([
            'title'       => 'Zugang freischalten — modeiland',
            'description' => 'Kontaktieren Sie Anbieter direkt — ohne Makler, ohne Provision.',
            'robots'      => 'noindex',
        ]);

        return $this->render('App\Modules\Estate\Views\seeker\paywall', [
            'billingEnabled'   => $this->config->billingEnabled,
            'seekerPriceCents' => $this->config->seekerPriceCents,
            'alreadyActive'    => $seeker && $this->seekers->isSubscribed($seeker['id']),
            'seeker'           => $seeker,
        ]);
    }

    /** POST /abonnieren/checkout — initiate payment or trial */
    public function checkout(): \CodeIgniter\HTTP\RedirectResponse
    {
        $email = trim($this->request->getPost('email') ?? '');

        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->to(site_url('abonnieren'))
                             ->withInput()
                             ->with('error', 'Bitte geben Sie eine gültige E-Mail-Adresse ein.');
        }

        // Find or create seeker
        $seeker = $this->seekers->findByEmail($email);
        if (! $seeker) {
            $id     = $this->seekers->insert([
                'email'               => $email,
                'subscription_status' => 'free',
                'login_method'        => 'magic_link',
            ]);
            $seeker = $this->seekers->find($id);
        }

        // ── Trial path (BILLING_ENABLED=false) ───────────────────────────────
        if (! $this->config->billingEnabled) {
            $this->subscriptions->createTrial($seeker['id']);
            $this->seekers->update($seeker['id'], ['subscription_status' => 'active']);
            session()->set('estate_seeker_id', $seeker['id']);

            $this->audit->record('seeker.trial_started', "seekers/{$seeker['id']}", [], 'seeker', $seeker['id']);

            return redirect()->to(site_url('seeker/panel'))
                             ->with('success', 'Willkommen! Sie können alle Funktionen kostenlos testen.');
        }

        // ── Stripe Checkout path ──────────────────────────────────────────────
        try {
            $stripe  = new StripeService();
            $session = $stripe->createSeekerCheckout(
                $email,
                site_url('abonnieren/erfolg'),
                site_url('abonnieren/abbrechen')
            );

            // Store seeker ID in session so success handler can resume
            session()->set('estate_seeker_id', $seeker['id']);
            session()->set('estate_checkout_email', $email);

            return redirect()->to($session->url);
        } catch (\Throwable $e) {
            log_message('error', '[Stripe] createSeekerCheckout failed: ' . $e->getMessage());
            return redirect()->to(site_url('abonnieren'))
                             ->with('error', 'Zahlung konnte nicht gestartet werden. Bitte versuchen Sie es später erneut.');
        }
    }

    /**
     * GET /abonnieren/erfolg?session_id={CHECKOUT_SESSION_ID}
     * Stripe redirects here after payment. Webhook will also fire, but this
     * landing page is where we set the session for immediate UX continuity.
     */
    public function success(): \CodeIgniter\HTTP\RedirectResponse
    {
        $sessionId = $this->request->getGet('session_id');

        if ($sessionId && $this->config->billingEnabled) {
            try {
                $stripe      = new StripeService();
                $stripeSession = $stripe->retrieveSession($sessionId);
                $email       = $stripeSession->customer_email ?? $stripeSession->customer_details->email ?? null;

                if ($email) {
                    $seeker = $this->seekers->findByEmail($email);
                    if ($seeker) {
                        session()->set('estate_seeker_id', $seeker['id']);
                        $this->seekers->update($seeker['id'], ['subscription_status' => 'active']);
                    }
                }
            } catch (\Throwable $e) {
                log_message('warning', '[Stripe] success retrieval failed: ' . $e->getMessage());
                // Non-fatal: webhook will handle DB update, user sees success anyway
            }
        }

        return redirect()->to(site_url('seeker/panel'))
                         ->with('success', 'Ihr Abonnement ist aktiv. Willkommen bei modeiland Plus!');
    }

    /** GET /abonnieren/abbrechen */
    public function cancel(): string
    {
        $this->setSeo(['title' => 'Abonnement abgebrochen — modeiland', 'robots' => 'noindex']);

        return $this->render('App\Modules\Estate\Views\seeker\subscribe_cancel', [
            'seekerPriceCents' => $this->config->seekerPriceCents,
        ]);
    }

    /** POST /abonnieren/portal — redirect to Stripe Customer Portal */
    public function portal(): \CodeIgniter\HTTP\RedirectResponse
    {
        $seeker = $this->currentSeeker();

        if (! $seeker) {
            return redirect()->to(site_url('abonnieren'));
        }

        $sub = $this->subscriptions->findActiveForSeeker($seeker['id']);

        if (! $sub || ! $sub['stripe_customer_id']) {
            return redirect()->to(site_url('seeker/panel'))
                             ->with('info', 'Kein aktives Stripe-Abonnement gefunden. Bitte kontaktieren Sie den Support.');
        }

        try {
            $stripe  = new StripeService();
            $portal  = $stripe->createPortalSession(
                $sub['stripe_customer_id'],
                site_url('seeker/panel')
            );
            return redirect()->to($portal->url);
        } catch (\Throwable $e) {
            log_message('error', '[Stripe] createPortalSession failed: ' . $e->getMessage());
            return redirect()->to(site_url('seeker/panel'))
                             ->with('error', 'Kundenportal konnte nicht geöffnet werden.');
        }
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function currentSeeker(): ?array
    {
        $id = session()->get('estate_seeker_id');
        return $id ? $this->seekers->find((int) $id) : null;
    }
}
