<?php

namespace App\Modules\Estate\Controllers\Seeker;

use App\Core\Controllers\BaseWebController;
use App\Modules\Estate\Models\ListingModel;
use App\Modules\Estate\Models\SeekerModel;
use App\Modules\Estate\Models\MessageModel;
use App\Modules\Estate\Models\AuditLogModel;
use App\Modules\Estate\Config\Estate;

class MessageController extends BaseWebController
{
    private ListingModel  $listings;
    private SeekerModel   $seekers;
    private MessageModel  $messages;
    private AuditLogModel $audit;
    private Estate        $config;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->listings = new ListingModel();
        $this->seekers  = new SeekerModel();
        $this->messages = new MessageModel();
        $this->audit    = new AuditLogModel();
        $this->config   = config(Estate::class);
    }

    /** GET /inserate/{id}/kontakt */
    public function form(int $listingId): string
    {
        $listing = $this->listings->find($listingId);

        if (! $listing || $listing['status'] !== 'live') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Inserat nicht gefunden.');
        }

        $seeker   = $this->resolveSeeker();
        $canSend  = $seeker && $this->seekerCanMessage($seeker);

        $this->setSeo(['title' => 'Kontakt aufnehmen — modeiland']);

        return $this->render('App\Modules\Estate\Views\seeker\contact', [
            'listing'  => $listing,
            'seeker'   => $seeker,
            'canSend'  => $canSend,
            'billing'  => $this->config->billingEnabled,
        ]);
    }

    /** POST /inserate/{id}/kontakt */
    public function send(int $listingId): \CodeIgniter\HTTP\RedirectResponse
    {
        $listing = $this->listings->find($listingId);

        if (! $listing || $listing['status'] !== 'live') {
            return redirect()->back()->with('error', 'Inserat nicht gefunden.');
        }

        // Guest: require email to register/identify
        $email = $this->request->getPost('email');
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'Bitte geben Sie eine gültige E-Mail-Adresse an.');
        }

        // Find or create seeker
        $seeker = $this->seekers->findByEmail($email);
        if (! $seeker) {
            $seekerId = $this->seekers->insert([
                'email'               => $email,
                'subscription_status' => 'free',
                'login_method'        => 'none',
            ]);
            $seeker = $this->seekers->find($seekerId);
        }

        // Paywall check
        if (! $this->seekerCanMessage($seeker)) {
            return redirect()->to('abonnement')
                             ->with('info', 'Um Nachrichten zu senden, benötigen Sie ein Abonnement.');
        }

        $body = trim($this->request->getPost('body') ?? '');
        if (empty($body)) {
            return redirect()->back()->withInput()->with('error', 'Bitte geben Sie eine Nachricht ein.');
        }
        if (mb_strlen($body) > 2000) {
            return redirect()->back()->withInput()->with('error', 'Nachricht zu lang (max. 2000 Zeichen).');
        }

        $this->messages->insert([
            'listing_id' => $listingId,
            'seeker_id'  => $seeker['id'],
            'owner_id'   => $listing['owner_id'],
            'body'       => $body,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->audit->record('message.sent', "listings/{$listingId}", [
            'seeker_id' => $seeker['id'],
        ], 'seeker', $seeker['id']);

        // Trigger owner email notification
        $this->notifyOwner($listing, $seeker, $body);

        return redirect()->to("inserate/{$listingId}")
                         ->with('success', 'Ihre Nachricht wurde gesendet. Der Anbieter wird benachrichtigt.');
    }

    // ─── Private helpers ────────────────────────────────────────────────────

    private function resolveSeeker(): ?array
    {
        // Phase 1: simple session-based seeker identification
        $seekerId = session()->get('estate_seeker_id');
        if ($seekerId) {
            return $this->seekers->find((int) $seekerId);
        }
        return null;
    }

    private function seekerCanMessage(array $seeker): bool
    {
        // BILLING_ENABLED=false → anyone can send (trial mode)
        if (! $this->config->billingEnabled) {
            return true;
        }
        return $this->seekers->isSubscribed($seeker['id']);
    }

    private function notifyOwner(array $listing, array $seeker, string $body): void
    {
        $owner = db_connect()->table('owners')
            ->where('id', $listing['owner_id'])
            ->get()->getRowArray();

        if (! $owner || empty($owner['email'])) {
            return;
        }

        try {
            $emailSvc = \Config\Services::email();
            $emailSvc->setTo($owner['email']);
            $emailSvc->setSubject('Neue Nachricht zu Ihrem Inserat — modeiland');
            $emailSvc->setMessage(view(
                'App\Modules\Estate\Views\owner\email_new_message',
                ['owner' => $owner, 'seeker' => $seeker, 'body' => $body, 'listing' => $listing]
            ));
            $emailSvc->send();
        } catch (\Throwable $e) {
            log_message('error', '[Estate] Owner email notification failed: ' . $e->getMessage());
        }
    }
}
