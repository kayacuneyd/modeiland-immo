<?php

namespace App\Modules\Estate\Controllers\Seeker;

use App\Core\Controllers\BaseWebController;
use App\Modules\Estate\Models\SavedSearchModel;

/**
 * Saved search management — seeker must be in session.
 *
 * POST /seeker/suche/speichern      → save current filters
 * POST /seeker/suche/{id}/loeschen  → delete saved search
 * POST /seeker/suche/{id}/alarm     → toggle alert_enabled
 */
class SavedSearchController extends BaseWebController
{
    private SavedSearchModel $savedSearches;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->savedSearches = new SavedSearchModel();
    }

    /** POST /seeker/suche/speichern */
    public function save(): \CodeIgniter\HTTP\RedirectResponse
    {
        $seekerId = $this->requireSeekerSession();
        if (! $seekerId) {
            return redirect()->to(site_url('abonnieren'));
        }

        $filtersRaw = $this->request->getPost('filters') ?? [];
        $label      = trim($this->request->getPost('label') ?? '');

        // Sanitise: only keep known filter keys
        $allowed = ['q', 'location', 'rent_max', 'rooms_min', 'type'];
        $filters = array_filter(
            array_intersect_key($filtersRaw, array_flip($allowed)),
            fn($v) => $v !== '' && $v !== null
        );

        if (empty($filters)) {
            return redirect()->back()->with('error', 'Keine Suchkriterien zum Speichern vorhanden.');
        }

        $this->savedSearches->insert([
            'seeker_id'    => $seekerId,
            'label'        => $label ?: $this->autoLabel($filters),
            'filters_json' => json_encode($filters),
            'alert_enabled'=> 0,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(site_url('seeker/panel'))->with('success', 'Suche gespeichert.');
    }

    /** POST /seeker/suche/{id}/loeschen */
    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $seekerId = $this->requireSeekerSession();
        if (! $seekerId) {
            return redirect()->to(site_url('abonnieren'));
        }

        $search = $this->savedSearches->find($id);
        if ($search && (int) $search['seeker_id'] === $seekerId) {
            $this->savedSearches->delete($id);
        }

        return redirect()->to(site_url('seeker/panel'))->with('success', 'Suche gelöscht.');
    }

    /** POST /seeker/suche/{id}/alarm */
    public function toggleAlert(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $seekerId = $this->requireSeekerSession();
        if (! $seekerId) {
            return redirect()->to(site_url('abonnieren'));
        }

        $search = $this->savedSearches->find($id);
        if ($search && (int) $search['seeker_id'] === $seekerId) {
            $newVal = $search['alert_enabled'] ? 0 : 1;
            $this->savedSearches->update($id, ['alert_enabled' => $newVal]);
            $msg = $newVal ? 'E-Mail-Alarm aktiviert.' : 'E-Mail-Alarm deaktiviert.';
            return redirect()->to(site_url('seeker/panel'))->with('success', $msg);
        }

        return redirect()->to(site_url('seeker/panel'));
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function requireSeekerSession(): ?int
    {
        $id = session()->get('estate_seeker_id');
        return $id ? (int) $id : null;
    }

    private function autoLabel(array $filters): string
    {
        $parts = [];
        if (! empty($filters['location'])) {
            $parts[] = $filters['location'];
        }
        if (! empty($filters['rent_max'])) {
            $parts[] = 'bis ' . number_format((int) $filters['rent_max'] / 100, 0, ',', '.') . ' €';
        }
        if (! empty($filters['rooms_min'])) {
            $parts[] = 'ab ' . $filters['rooms_min'] . ' Zi.';
        }
        return $parts ? implode(', ', $parts) : 'Gespeicherte Suche';
    }
}
