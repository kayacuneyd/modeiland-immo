<?php

namespace App\Modules\Estate\Controllers\Admin;

use App\Core\Controllers\BaseAdminController;
use App\Modules\Estate\Models\ListingModel;
use App\Modules\Estate\Models\OwnerModel;
use App\Modules\Estate\Models\AuditLogModel;
use App\Modules\Estate\Libraries\AiService;

class ListingAdminController extends BaseAdminController
{
    private ListingModel $listings;
    private OwnerModel   $owners;
    private AuditLogModel $audit;
    private AiService    $ai;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->listings = new ListingModel();
        $this->owners   = new OwnerModel();
        $this->audit    = new AuditLogModel();
        $this->ai       = new AiService();
    }

    public function index(): string
    {
        return $this->render('App\Modules\Estate\Views\admin\listings\index', [
            'listings' => $this->db->table('listings l')
                ->select('l.*, o.display_name AS owner_name')
                ->join('owners o', 'o.id = l.owner_id', 'left')
                ->orderBy('l.created_at', 'DESC')
                ->get()->getResultArray(),
        ]);
    }

    public function create(int $ownerId): string
    {
        $owner = $this->owners->find($ownerId);
        if (! $owner) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Owner #{$ownerId} nicht gefunden");
        }

        return $this->render('App\Modules\Estate\Views\admin\listings\create', [
            'owner' => $owner,
        ]);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $ownerId  = (int) $this->request->getPost('owner_id');
        $owner    = $this->owners->find($ownerId);

        if (! $owner) {
            return redirect()->back()->with('error', 'Anbieter nicht gefunden.');
        }

        $isFirstFree = $this->listings->isFirstFreeEligible($ownerId) ? 1 : 0;

        $id = $this->listings->insert([
            'owner_id'         => $ownerId,
            'source_url'       => $this->request->getPost('source_url'),
            'source_text_raw'  => $this->request->getPost('source_text_raw'),
            'status'           => 'draft',
            'type'             => $this->request->getPost('type') ?: 'rent',
            'is_first_free'    => $isFirstFree,
            'ai_import_status' => 'pending',
        ]);

        $this->audit->record('listing.created', "listings/{$id}", [
            'owner_id' => $ownerId,
            'source_url' => $this->request->getPost('source_url'),
        ], 'admin');

        return redirect()->to("admin/estate/listings/{$id}")
                         ->with('success', 'Inserat-Entwurf angelegt. Jetzt KI-Import starten.');
    }

    public function show(int $id): string
    {
        $listing = $this->db->table('listings l')
            ->select('l.*, o.display_name AS owner_name, o.email AS owner_email')
            ->join('owners o', 'o.id = l.owner_id', 'left')
            ->where('l.id', $id)
            ->get()->getRowArray();

        if (! $listing) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Inserat #{$id} nicht gefunden");
        }

        return $this->render('App\Modules\Estate\Views\admin\listings\show', [
            'listing' => $listing,
        ]);
    }

    public function aiImport(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $listing = $this->listings->find($id);
        if (! $listing) {
            return redirect()->back()->with('error', 'Inserat nicht gefunden.');
        }

        $rawText = $listing['source_text_raw'] ?? '';

        if (empty($rawText) && empty($listing['source_url'])) {
            return redirect()->back()->with('error', 'Kein Quelltext und keine URL vorhanden.');
        }

        // Mark as importing before calling AI (shared-hosting: single HTTP request)
        $this->listings->update($id, ['ai_import_status' => 'importing']);

        $result = $this->ai->analyseListingText($rawText, $listing['source_url']);

        if (isset($result['error'])) {
            // Timeout or API error → draft_pending for cron retry
            $this->listings->update($id, ['ai_import_status' => 'draft_pending']);
            $this->audit->record('listing.ai_import_failed', "listings/{$id}", [
                'error' => $result['error'],
            ], 'admin');
            return redirect()->back()->with('error',
                'KI-Import fehlgeschlagen: ' . $result['error'] . ' — Entwurf als "ausstehend" gespeichert.');
        }

        // Map cents: AI returns cents already; guard against floats from AI
        $updateData = [
            'kaltmiete'       => isset($result['kaltmiete'])   ? (int) $result['kaltmiete']   : null,
            'warmmiete'       => isset($result['warmmiete'])   ? (int) $result['warmmiete']   : null,
            'nebenkosten'     => isset($result['nebenkosten']) ? (int) $result['nebenkosten'] : null,
            'deposit'         => isset($result['deposit'])     ? (int) $result['deposit']     : null,
            'rooms'           => isset($result['rooms'])       ? (float) $result['rooms']     : null,
            'm2'              => isset($result['m2'])          ? (float) $result['m2']        : null,
            'location_text'   => $result['location_text']   ?? null,
            'location_approx' => $result['location_approx'] ?? null,
            'available_from'  => $result['available_from']  ?? null,
            'ai_description'  => $result['ai_description']  ?? null,
            'ai_import_status'=> 'done',
        ];

        $this->listings->update($id, $updateData);
        $this->audit->record('listing.ai_imported', "listings/{$id}", [], 'admin');

        return redirect()->to("admin/estate/listings/{$id}")
                         ->with('success', 'KI-Import abgeschlossen. Bitte Entwurf prüfen.');
    }

    public function publish(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $listing = $this->listings->find($id);
        if (! $listing) {
            return redirect()->back()->with('error', 'Inserat nicht gefunden.');
        }

        $this->listings->update($id, ['status' => 'live']);
        $this->audit->record('listing.published', "listings/{$id}", [], 'admin');

        return redirect()->to("admin/estate/listings/{$id}")
                         ->with('success', 'Inserat ist jetzt live.');
    }

    public function destroy(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->listings->update($id, ['status' => 'removed']);
        $this->audit->record('listing.removed', "listings/{$id}", [], 'admin');
        return redirect()->to('admin/estate/listings')->with('success', 'Inserat entfernt.');
    }
}
