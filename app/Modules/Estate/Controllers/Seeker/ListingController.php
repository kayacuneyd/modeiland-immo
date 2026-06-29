<?php

namespace App\Modules\Estate\Controllers\Seeker;

use App\Core\Controllers\BaseWebController;
use App\Modules\Estate\Models\ListingModel;
use App\Modules\Estate\Models\ListingImageModel;

class ListingController extends BaseWebController
{
    private ListingModel      $listings;
    private ListingImageModel $images;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->listings = new ListingModel();
        $this->images   = new ListingImageModel();
    }

    public function show(int $id): string
    {
        $listing = $this->listings->find($id);

        if (! $listing || $listing['status'] !== 'live') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Inserat nicht gefunden.');
        }

        $images = $this->images->getApprovedForListing($id);

        $this->setSeo([
            'title'       => ($listing['location_approx'] ?? 'Inserat') . ' — modeiland',
            'description' => mb_substr(strip_tags($listing['ai_description'] ?? ''), 0, 160),
        ]);

        return $this->render('App\Modules\Estate\Views\seeker\show', [
            'listing' => $listing,
            'images'  => $images,
        ]);
    }
}
