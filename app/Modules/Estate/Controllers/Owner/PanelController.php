<?php

namespace App\Modules\Estate\Controllers\Owner;

use App\Core\Controllers\BaseWebController;
use App\Modules\Estate\Models\MessageModel;
use App\Modules\Estate\Models\ListingModel;
use App\Modules\Estate\Models\AuditLogModel;
use App\Modules\Estate\Services\OwnerAuthService;
use App\Modules\Estate\Config\Estate;

/**
 * Owner panel — three zones: Meine Inserate / Freigabe ausstehend / Anfragen.
 * Protected by OwnerAuthFilter (estate_owner_id in session).
 */
class PanelController extends BaseWebController
{
    private MessageModel  $messages;
    private ListingModel  $listings;
    private AuditLogModel $audit;
    private OwnerAuthService $auth;
    private Estate        $config;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->messages = new MessageModel();
        $this->listings = new ListingModel();
        $this->audit    = new AuditLogModel();
        $this->auth     = new OwnerAuthService();
        $this->config   = config(Estate::class);
    }

    /** GET /owner/panel */
    public function index(): string
    {
        $ownerId = (int) session()->get('estate_owner_id');
        $owner   = db_connect()->table('owners')->where('id', $ownerId)->get()->getRowArray();

        $allListings = $this->listings->getByOwner($ownerId);
        $live        = array_filter($allListings, fn($l) => in_array($l['status'], ['live', 'paused'], true));
        $drafts      = array_filter($allListings, fn($l) => $l['status'] === 'draft');
        $messages    = $this->messages->getForOwner($ownerId);
        $unread      = $this->messages->countUnreadForOwner($ownerId);

        // Profile completion status (calm banner logic)
        $profileComplete = ! empty($owner['email']) || ! empty($owner['password_hash']);

        $this->setSeo(['title' => 'Mein Panel — modeiland', 'robots' => 'noindex']);

        return $this->render('App\Modules\Estate\Views\owner\panel', [
            'owner'           => $owner,
            'liveListings'    => array_values($live),
            'draftListings'   => array_values($drafts),
            'messages'        => $messages,
            'unread'          => $unread,
            'profileComplete' => $profileComplete,
            'pollingInterval' => $this->config->messagePollingIntervalSeconds,
        ]);
    }

    /**
     * GET /owner/messages/poll — JSON endpoint for JS polling.
     * Returns {unread: int} — no WebSocket, no daemon.
     */
    public function poll(): \CodeIgniter\HTTP\ResponseInterface
    {
        $ownerId = (int) session()->get('estate_owner_id');
        $unread  = $ownerId ? $this->messages->countUnreadForOwner($ownerId) : 0;

        return $this->response
            ->setContentType('application/json')
            ->setBody(json_encode(['unread' => $unread]));
    }

    /** POST /owner/listings/{id}/pausieren */
    public function pausieren(int $listingId): \CodeIgniter\HTTP\RedirectResponse
    {
        $ownerId = (int) session()->get('estate_owner_id');
        $listing = $this->listings->find($listingId);

        if (! $listing || (int) $listing['owner_id'] !== $ownerId) {
            return redirect()->to(site_url('owner/panel'))->with('error', 'Zugriff verweigert.');
        }

        $newStatus = $listing['status'] === 'paused' ? 'live' : 'paused';
        $this->listings->update($listingId, ['status' => $newStatus]);
        $this->audit->record("listing.{$newStatus}", "listings/{$listingId}", [], 'owner', $ownerId);

        $msg = $newStatus === 'paused' ? 'Inserat pausiert.' : 'Inserat wieder aktiviert.';
        return redirect()->to(site_url('owner/panel'))->with('success', $msg);
    }

    /** POST /owner/listings/{id}/entfernen — requires step-up */
    public function entfernen(int $listingId): \CodeIgniter\HTTP\RedirectResponse
    {
        $ownerId = (int) session()->get('estate_owner_id');
        $listing = $this->listings->find($listingId);

        if (! $listing || (int) $listing['owner_id'] !== $ownerId) {
            return redirect()->to(site_url('owner/panel'))->with('error', 'Zugriff verweigert.');
        }

        // Step-up required for deletion
        if (! $this->auth->isStepUpValid()) {
            $next   = urlencode(site_url("owner/listings/{$listingId}/entfernen"));
            return redirect()->to(site_url("owner/stepup?next={$next}&action=Inserat+entfernen"));
        }

        $this->listings->update($listingId, ['status' => 'removed']);
        $this->auth->logSecurityEvent('listing_removed', $ownerId, ['listing_id' => $listingId]);
        $this->audit->record('listing.removed', "listings/{$listingId}", [], 'owner', $ownerId);

        return redirect()->to(site_url('owner/panel'))->with('success', 'Inserat wurde entfernt.');
    }
}
