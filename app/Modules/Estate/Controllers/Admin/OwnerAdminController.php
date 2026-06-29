<?php

namespace App\Modules\Estate\Controllers\Admin;

use App\Core\Controllers\BaseAdminController;
use App\Modules\Estate\Models\OwnerModel;
use App\Modules\Estate\Models\ListingModel;
use App\Modules\Estate\Models\AuditLogModel;
use App\Modules\Estate\Services\OwnerAuthService;

class OwnerAdminController extends BaseAdminController
{
    private OwnerModel      $owners;
    private ListingModel    $listings;
    private AuditLogModel   $audit;
    private OwnerAuthService $auth;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->owners   = new OwnerModel();
        $this->listings = new ListingModel();
        $this->audit    = new AuditLogModel();
        $this->auth     = new OwnerAuthService();
    }

    public function index(): string
    {
        return $this->render('App\Modules\Estate\Views\admin\owners\index', [
            'leads'  => $this->owners->getLeads(),
            'active' => $this->owners->getActive(),
        ]);
    }

    public function create(): string
    {
        return $this->render('App\Modules\Estate\Views\admin\owners\create', []);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $rules = [
            'display_name' => 'required|max_length[150]',
            'source_url'   => 'permit_empty|valid_url_strict|max_length[2000]',
            'email'        => 'permit_empty|valid_email|max_length[200]',
            'phone'        => 'permit_empty|max_length[50]',
            'outreach_note'=> 'permit_empty|max_length[2000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $id = $this->owners->insert([
            'display_name'  => $this->request->getPost('display_name'),
            'source_url'    => $this->request->getPost('source_url'),
            'email'         => $this->request->getPost('email'),
            'phone'         => $this->request->getPost('phone'),
            'outreach_note' => $this->request->getPost('outreach_note'),
            'status'        => 'lead',
        ]);

        $this->audit->record('owner.created', "owners/{$id}", [
            'display_name' => $this->request->getPost('display_name'),
        ], 'admin');

        return redirect()->to("admin/estate/owners/{$id}")
                         ->with('success', 'Anbieter-Lead angelegt.');
    }

    public function show(int $id): string
    {
        $owner = $this->owners->find($id);
        if (! $owner) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Owner #{$id} nicht gefunden");
        }

        $outreachTemplate = $this->buildOutreachTemplate($owner);

        return $this->render('App\Modules\Estate\Views\admin\owners\show', [
            'owner'            => $owner,
            'listings'         => $this->listings->getByOwner($id),
            'outreachTemplate' => $outreachTemplate,
        ]);
    }

    public function destroy(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->owners->delete($id);
        $this->audit->record('owner.deleted', "owners/{$id}", [], 'admin');
        return redirect()->to('admin/estate/owners')->with('success', 'Anbieter gelöscht.');
    }

    /**
     * POST admin/estate/owners/{id}/generate-invite
     * Generates a new invite token, revokes previous ones, shows link.
     */
    public function generateInvite(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $owner = $this->owners->find($id);
        if (! $owner) {
            return redirect()->back()->with('error', 'Anbieter nicht gefunden.');
        }

        $rawToken  = $this->auth->generateInviteToken($id);
        $inviteUrl = site_url("einladung/{$rawToken}");

        $this->audit->record('owner.invite_generated', "owners/{$id}", [], 'admin');

        // Store URL in flash for display — raw token is the link itself
        return redirect()->to("admin/estate/owners/{$id}")
                         ->with('invite_url', $inviteUrl)
                         ->with('success', 'Neuer Einladungslink generiert. Bitte sicher übermitteln (einmal anzeigen).');
    }

    // ─── Private helpers ────────────────────────────────────────────────────

    private function buildOutreachTemplate(array $owner): string
    {
        $name = htmlspecialchars($owner['display_name']);
        $url  = htmlspecialchars($owner['source_url'] ?? '');

        return <<<MSG
        Guten Tag {$name},

        ich habe Ihr Inserat gesehen ({$url}) und möchte Ihnen eine kostenlose Möglichkeit vorstellen,
        Ihre Immobilie noch mehr Interessenten zu zeigen — ohne Makler, ohne Provision, ohne Verpflichtung.

        Unsere Plattform modeiland hilft privaten Anbietern wie Ihnen, qualifizierte Interessenten
        direkt und sicher zu erreichen. Sie behalten die volle Kontrolle über Ihre Anzeige.

        Falls Sie Interesse haben, antworte ich Ihnen gerne mit weiteren Details.

        Mit freundlichen Grüßen
        MSG;
    }
}
