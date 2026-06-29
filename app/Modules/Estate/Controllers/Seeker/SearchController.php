<?php

namespace App\Modules\Estate\Controllers\Seeker;

use App\Core\Controllers\BaseWebController;
use App\Modules\Estate\Models\ListingModel;

class SearchController extends BaseWebController
{
    private ListingModel $listings;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->listings = new ListingModel();
    }

    public function index(): string
    {
        $filters = [
            'min_warmmiete' => $this->request->getGet('min_warmmiete'),
            'max_warmmiete' => $this->request->getGet('max_warmmiete'),
            'min_rooms'     => $this->request->getGet('min_rooms'),
            'min_m2'        => $this->request->getGet('min_m2'),
            'location'      => $this->request->getGet('location'),
        ];

        $page    = (int) ($this->request->getGet('page') ?: 1);
        $perPage = 12;
        $items   = $this->listings->getLive($filters, $page, $perPage);
        $total   = $this->listings->countLive($filters);

        $this->setSeo([
            'title'       => 'Inserate — Wohnungen & Häuser suchen',
            'description' => 'Finden Sie Ihre nächste Wohnung — direkt vom Eigentümer, ohne Makler.',
        ]);

        return $this->render('App\Modules\Estate\Views\seeker\index', [
            'listings' => $items,
            'total'    => $total,
            'page'     => $page,
            'perPage'  => $perPage,
            'filters'  => $filters,
        ]);
    }
}
