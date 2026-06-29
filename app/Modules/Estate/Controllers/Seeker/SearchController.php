<?php

namespace App\Modules\Estate\Controllers\Seeker;

use App\Core\Controllers\BaseWebController;
use App\Modules\Estate\Models\ListingModel;
use App\Modules\Estate\Models\SeekerModel;
use App\Modules\Estate\Models\SavedSearchModel;
use App\Modules\Estate\Services\AiMatchingService;

class SearchController extends BaseWebController
{
    private ListingModel     $listings;
    private SeekerModel      $seekers;
    private SavedSearchModel $savedSearches;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->listings      = new ListingModel();
        $this->seekers       = new SeekerModel();
        $this->savedSearches = new SavedSearchModel();
    }

    public function index(): string
    {
        $filters = [
            'min_warmmiete' => $this->request->getGet('min_warmmiete'),
            'max_warmmiete' => $this->request->getGet('max_warmmiete'),
            'min_rooms'     => $this->request->getGet('min_rooms'),
            'min_m2'        => $this->request->getGet('min_m2'),
            'location'      => $this->request->getGet('location'),
            'type'          => $this->request->getGet('type'),
        ];

        $page    = (int) ($this->request->getGet('page') ?: 1);
        $perPage = 12;

        // Active seeker session — check for saved search preferences
        $seekerId      = (int) session()->get('estate_seeker_id');
        $activeFilters = array_filter($filters);   // URL filters take priority
        $hasSavedPrefs = false;
        $canSave       = false;

        if ($seekerId) {
            $canSave = true;

            // If seeker has no URL filters but has a saved search, auto-apply first saved search
            if (empty($activeFilters)) {
                $saved = $this->savedSearches->getForSeeker($seekerId);
                if (! empty($saved)) {
                    $savedFilters  = json_decode($saved[0]['filters_json'] ?? '{}', true);
                    if (! empty($savedFilters)) {
                        // Remap saved filter keys to URL filter keys
                        $filters = array_merge($filters, [
                            'max_warmmiete' => $savedFilters['rent_max']   ?? ($savedFilters['max_warmmiete'] ?? null),
                            'min_rooms'     => $savedFilters['rooms_min']  ?? ($savedFilters['min_rooms'] ?? null),
                            'location'      => $savedFilters['location']   ?? null,
                            'type'          => $savedFilters['type']       ?? null,
                        ]);
                        $hasSavedPrefs = true;
                        $activeFilters = array_filter($filters);
                    }
                }
            }
        }

        $items = $this->listings->getLive($filters, $page, $perPage);
        $total = $this->listings->countLive($filters);

        // Sort by fit score when seeker has preferences
        if ($seekerId && ! empty($activeFilters) && ! empty($items)) {
            $matcher = new AiMatchingService();
            $items   = $matcher->sortByFit($items, $activeFilters);
        }

        $this->setSeo([
            'title'       => 'Inserate — Wohnungen & Häuser suchen',
            'description' => 'Finden Sie Ihre nächste Wohnung — direkt vom Eigentümer, ohne Makler.',
        ]);

        return $this->render('App\Modules\Estate\Views\seeker\index', [
            'listings'      => $items,
            'total'         => $total,
            'page'          => $page,
            'perPage'       => $perPage,
            'filters'       => $filters,
            'hasSavedPrefs' => $hasSavedPrefs,
            'canSave'       => $canSave,
            'seekerId'      => $seekerId,
        ]);
    }
}
