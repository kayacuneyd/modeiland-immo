<?php

namespace App\Modules\Estate\Controllers\Owner;

use App\Core\Controllers\BaseWebController;
use App\Modules\Estate\Models\ListingModel;
use App\Modules\Estate\Models\ListingConsentModel;
use App\Modules\Estate\Models\ListingImageModel;
use App\Modules\Estate\Models\AuditLogModel;
use App\Modules\Estate\Services\OwnerAuthService;
use App\Modules\Estate\Models\OwnerListingChargeModel;
use App\Modules\Estate\Config\Estate;

/**
 * Owner draft preview + consent flow (Faz 2: session-authenticated).
 * Route /owner/draft/{id} is protected by OwnerAuthFilter.
 * Owner can only access their own listings.
 */
class OwnerController extends BaseWebController
{
    private ListingModel             $listings;
    private ListingConsentModel      $consents;
    private ListingImageModel        $images;
    private AuditLogModel            $audit;
    private OwnerAuthService         $auth;
    private OwnerListingChargeModel  $charges;
    private Estate                   $config;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->listings = new ListingModel();
        $this->consents = new ListingConsentModel();
        $this->images   = new ListingImageModel();
        $this->audit    = new AuditLogModel();
        $this->auth     = new OwnerAuthService();
        $this->charges  = new OwnerListingChargeModel();
        $this->config   = config(Estate::class);
    }

    /** GET /owner/draft/{listing_id} */
    public function draft(int $listingId): string
    {
        $ownerId = (int) session()->get('estate_owner_id');
        $listing = $this->listings->find($listingId);

        if (! $listing || (int) $listing['owner_id'] !== $ownerId) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Inserat nicht gefunden.');
        }

        if (! in_array($listing['status'], ['draft', 'live'], true)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Dieses Inserat ist nicht verfügbar.');
        }

        $images          = $this->images->getApprovedForListing($listingId);
        $existingConsent = $this->consents->latestForListing($listingId);
        $alreadyApproved = $listing['status'] === 'live';
        $owner           = db_connect()->table('owners')->where('id', $ownerId)->get()->getRowArray();

        $this->setSeo(['title' => 'Ihr Inserat — Vorschau', 'robots' => 'noindex']);

        return $this->render('App\Modules\Estate\Views\owner\draft', [
            'listing'         => $listing,
            'owner'           => $owner,
            'images'          => $images,
            'existingConsent' => $existingConsent,
            'alreadyApproved' => $alreadyApproved,
            'consentVersion'  => $this->config->consentVersion,
        ]);
    }

    /** POST /owner/draft/{listing_id} — consent + publish */
    public function approve(int $listingId): \CodeIgniter\HTTP\RedirectResponse
    {
        $ownerId = (int) session()->get('estate_owner_id');
        $listing = $this->listings->find($listingId);

        if (! $listing || (int) $listing['owner_id'] !== $ownerId) {
            return redirect()->to(site_url('owner/panel'))->with('error', 'Zugriff verweigert.');
        }

        if ($listing['status'] !== 'draft') {
            return redirect()->back()->with('error', 'Dieses Inserat kann nicht mehr freigegeben werden.');
        }

        // Extra listing charge gate (BILLING_ENABLED=true only; first listing is always free)
        if ($this->config->billingEnabled && ! $listing['is_first_free']) {
            if (! $this->charges->isPaid($listingId)) {
                return redirect()->to(site_url("owner/listing-checkout/{$listingId}"))
                                 ->with('info', 'Für dieses Inserat ist eine einmalige Gebühr erforderlich.');
            }
        }

        // All 3 required checkboxes must be present
        $required = ['consent_owner_auth', 'consent_publish', 'consent_ai_rewrite'];
        foreach ($required as $field) {
            if (! $this->request->getPost($field)) {
                return redirect()->back()->with('error',
                    'Bitte bestätigen Sie alle erforderlichen Einwilligungen (✱).');
            }
        }

        $approvedPhotos  = $this->request->getPost('consent_photos') ? 1 : 0;
        $contactMethod   = $this->request->getPost('consent_direct_contact') ? 'direct' : 'platform';

        // Append-only consent log (DSGVO §2.4)
        $this->consents->insert([
            'owner_id'                => $ownerId,
            'listing_id'              => $listingId,
            'consent_version'         => $this->config->consentVersion,
            'accepted_at'             => date('Y-m-d H:i:s'),
            'ip_address'              => $this->request->getIPAddress(),
            'user_agent'              => $this->request->getUserAgent()->getAgentString(),
            'approved_photos'         => $approvedPhotos,
            'approved_contact_method' => $contactMethod,
            'approved_ai_rewrite'     => 1,
        ]);

        $this->listings->update($listingId, ['status' => 'live']);

        $this->auth->logSecurityEvent('listing_approved', $ownerId, [
            'listing_id'      => $listingId,
            'approved_photos' => $approvedPhotos,
            'contact_method'  => $contactMethod,
            'consent_version' => $this->config->consentVersion,
        ]);

        $this->audit->record('listing.owner_approved', "listings/{$listingId}", [
            'ip'             => $this->request->getIPAddress(),
            'consent_version'=> $this->config->consentVersion,
        ], 'owner', $ownerId);

        return redirect()->to(site_url("owner/draft/{$listingId}"))
                         ->with('success', 'Ihr Inserat ist jetzt online. Vielen Dank für Ihre Einwilligung!');
    }
}
