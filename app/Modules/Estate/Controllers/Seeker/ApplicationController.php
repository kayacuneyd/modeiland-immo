<?php

namespace App\Modules\Estate\Controllers\Seeker;

use App\Core\Controllers\BaseWebController;
use App\Modules\Estate\Models\ListingModel;
use App\Modules\Estate\Models\SeekerModel;
use App\Modules\Estate\Models\SeekerProfileModel;
use App\Modules\Estate\Models\AuditLogModel;
use App\Modules\Estate\Services\AiBewerbungService;

/**
 * AI Bewerbungspaket — generates a personalised German cover letter + document checklist.
 * Requires: seeker session + active subscription.
 *
 * GET  /inserate/{id}/bewerben           → show (cached or generate)
 * POST /inserate/{id}/bewerben/neu       → force regenerate
 */
class ApplicationController extends BaseWebController
{
    private ListingModel       $listings;
    private SeekerModel        $seekers;
    private SeekerProfileModel $profiles;
    private AuditLogModel      $audit;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->listings = new ListingModel();
        $this->seekers  = new SeekerModel();
        $this->profiles = new SeekerProfileModel();
        $this->audit    = new AuditLogModel();
    }

    /** GET /inserate/{id}/bewerben */
    public function show(int $listingId): string|\CodeIgniter\HTTP\RedirectResponse
    {
        [$seeker, $listing] = $this->resolveContext($listingId);

        if ($seeker instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $seeker;
        }

        $profile = $this->profiles->findBySeeker($seeker['id']) ?? [];
        $service = new AiBewerbungService();
        $result  = $service->get($seeker['id'], $listingId, $profile, $listing);

        $this->audit->record('seeker.bewerbung_viewed', "listings/{$listingId}", [
            'cached' => $result['cached'],
        ], 'seeker', $seeker['id']);

        $this->setSeo(['title' => 'Bewerbungsunterlagen — modeiland', 'robots' => 'noindex']);

        return $this->render('App\Modules\Estate\Views\seeker\application', [
            'listing'     => $listing,
            'seeker'      => $seeker,
            'profile'     => $profile,
            'coverLetter' => $result['cover_letter'],
            'checklist'   => $result['checklist'],
            'cached'      => $result['cached'],
        ]);
    }

    /** POST /inserate/{id}/bewerben/neu — force regenerate */
    public function regenerate(int $listingId): \CodeIgniter\HTTP\RedirectResponse
    {
        [$seeker, $listing] = $this->resolveContext($listingId);

        if ($seeker instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $seeker;
        }

        $profile = $this->profiles->findBySeeker($seeker['id']) ?? [];
        $service = new AiBewerbungService();
        $service->regenerate($seeker['id'], $listingId, $profile, $listing);

        return redirect()->to(site_url("inserate/{$listingId}/bewerben"))
                         ->with('success', 'Neues Bewerbungsschreiben erstellt.');
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function resolveContext(int $listingId): array
    {
        $seekerId = session()->get('estate_seeker_id');

        if (! $seekerId) {
            return [redirect()->to(site_url('abonnieren'))
                              ->with('info', 'Bitte abonnieren Sie Plus, um Bewerbungsunterlagen zu erstellen.'), null];
        }

        $seeker = $this->seekers->find((int) $seekerId);

        if (! $seeker) {
            session()->remove('estate_seeker_id');
            return [redirect()->to(site_url('abonnieren')), null];
        }

        if (! $this->seekers->isSubscribed($seeker['id'])) {
            return [redirect()->to(site_url('abonnieren'))
                              ->with('info', 'Bewerbungsunterlagen sind für Plus-Mitglieder verfügbar.'), null];
        }

        $listing = $this->listings->find($listingId);

        if (! $listing || $listing['status'] !== 'live') {
            return [redirect()->to(site_url('inserate'))
                              ->with('error', 'Inserat nicht gefunden.'), null];
        }

        return [$seeker, $listing];
    }
}
