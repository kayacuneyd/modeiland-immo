<?php

namespace App\Modules\Estate\Controllers\Owner;

use App\Core\Controllers\BaseWebController;
use App\Modules\Estate\Models\ListingModel;
use App\Modules\Estate\Models\OwnerListingChargeModel;
use App\Modules\Estate\Services\StripeService;
use App\Modules\Estate\Config\Estate;

/**
 * Owner extra listing one-off payment via Stripe Checkout.
 * Protected by OwnerAuthFilter (estate_owner_id in session).
 *
 * GET  /owner/listing-checkout/{id}          → info page with pay button
 * POST /owner/listing-checkout/{id}/start    → create Stripe Checkout session → redirect
 * GET  /owner/listing-checkout/{id}/erfolg   → post-payment success landing
 * GET  /owner/listing-checkout/{id}/abbruch  → cancel landing
 */
class ListingCheckoutController extends BaseWebController
{
    private ListingModel            $listings;
    private OwnerListingChargeModel $charges;
    private Estate                  $config;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->listings = new ListingModel();
        $this->charges  = new OwnerListingChargeModel();
        $this->config   = config(Estate::class);
    }

    /** GET /owner/listing-checkout/{id} */
    public function show(int $listingId): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $ownerId = (int) session()->get('estate_owner_id');
        $listing = $this->listings->find($listingId);

        if (! $listing || (int) $listing['owner_id'] !== $ownerId) {
            return redirect()->to(site_url('owner/panel'))->with('error', 'Zugriff verweigert.');
        }

        if ($this->charges->isPaid($listingId)) {
            return redirect()->to(site_url("owner/draft/{$listingId}"))
                             ->with('success', 'Gebühr bereits bezahlt — Sie können das Inserat jetzt freigeben.');
        }

        $this->setSeo(['title' => 'Inserat freischalten — modeiland', 'robots' => 'noindex']);

        return $this->render('App\Modules\Estate\Views\owner\listing_checkout', [
            'listing'    => $listing,
            'amountEuro' => number_format($this->config->ownerExtraListingCents / 100, 0, ',', '.'),
        ]);
    }

    /** POST /owner/listing-checkout/{id}/start */
    public function start(int $listingId): \CodeIgniter\HTTP\RedirectResponse
    {
        $ownerId = (int) session()->get('estate_owner_id');
        $listing = $this->listings->find($listingId);

        if (! $listing || (int) $listing['owner_id'] !== $ownerId) {
            return redirect()->to(site_url('owner/panel'))->with('error', 'Zugriff verweigert.');
        }

        if ($this->charges->isPaid($listingId)) {
            return redirect()->to(site_url("owner/draft/{$listingId}"));
        }

        // BILLING_ENABLED=false → skip payment, create a free charge record
        if (! $this->config->billingEnabled) {
            $this->charges->insert([
                'owner_id'    => $ownerId,
                'listing_id'  => $listingId,
                'amount_cents'=> 0,
                'status'      => 'paid',
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
            return redirect()->to(site_url("owner/draft/{$listingId}"))
                             ->with('success', 'Inserat freigeschaltet (Test-Modus, keine Zahlung erforderlich).');
        }

        $owner      = db_connect()->table('owners')->where('id', $ownerId)->get()->getRowArray();
        $ownerEmail = $owner['email'] ?? '';

        try {
            $stripe  = new StripeService();
            $session = $stripe->createOwnerListingCheckout(
                $ownerId,
                $listingId,
                $ownerEmail,
                site_url("owner/listing-checkout/{$listingId}/erfolg"),
                site_url("owner/listing-checkout/{$listingId}/abbruch")
            );

            // Pre-create a pending charge for audit trail
            $this->charges->insert([
                'owner_id'         => $ownerId,
                'listing_id'       => $listingId,
                'amount_cents'     => $this->config->ownerExtraListingCents,
                'status'           => 'pending',
                'stripe_session_id'=> $session->id,
                'created_at'       => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to($session->url);
        } catch (\Throwable $e) {
            log_message('error', '[Stripe] createOwnerListingCheckout failed: ' . $e->getMessage());
            return redirect()->back()
                             ->with('error', 'Zahlung konnte nicht gestartet werden. Bitte versuchen Sie es später erneut.');
        }
    }

    /** GET /owner/listing-checkout/{id}/erfolg?session_id=... */
    public function success(int $listingId): \CodeIgniter\HTTP\RedirectResponse
    {
        // Webhook will mark the charge as paid. Redirect to draft to complete approval.
        return redirect()->to(site_url("owner/draft/{$listingId}"))
                         ->with('success', 'Zahlung erfolgreich! Sie können Ihr Inserat jetzt freigeben.');
    }

    /** GET /owner/listing-checkout/{id}/abbruch */
    public function cancel(int $listingId): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->to(site_url("owner/listing-checkout/{$listingId}"))
                         ->with('info', 'Zahlung abgebrochen. Sie können jederzeit erneut versuchen.');
    }
}
