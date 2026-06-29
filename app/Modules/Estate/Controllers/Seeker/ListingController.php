<?php

namespace App\Modules\Estate\Controllers\Seeker;

use App\Core\Controllers\BaseWebController;
use App\Modules\Estate\Models\ListingModel;
use App\Modules\Estate\Models\ListingImageModel;
use App\Modules\Estate\Models\SeekerModel;
use App\Modules\Estate\Models\SavedSearchModel;
use App\Modules\Estate\Services\AiMatchingService;

class ListingController extends BaseWebController
{
    private ListingModel      $listings;
    private ListingImageModel $images;
    private SeekerModel       $seekers;
    private SavedSearchModel  $savedSearches;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->listings      = new ListingModel();
        $this->images        = new ListingImageModel();
        $this->seekers       = new SeekerModel();
        $this->savedSearches = new SavedSearchModel();
    }

    public function show(int $id): string
    {
        $listing = $this->listings->find($id);

        if (! $listing || $listing['status'] !== 'live') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Inserat nicht gefunden.');
        }

        $images    = $this->images->getApprovedForListing($id);
        $fitResult = null;
        $canApply  = false;

        $seekerId = (int) session()->get('estate_seeker_id');
        if ($seekerId) {
            $seeker = $this->seekers->find($seekerId);
            if ($seeker) {
                $canApply = $this->seekers->isSubscribed($seekerId);

                // Compute fit score from the seeker's most recent saved search filters
                $searches = $this->savedSearches->getForSeeker($seekerId);
                if (! empty($searches)) {
                    $filters = json_decode($searches[0]['filters_json'] ?? '{}', true);
                    if (! empty($filters)) {
                        $matcher   = new AiMatchingService();
                        $fitResult = $matcher->computeFitScore($listing, $filters);
                        // AI reason is fetched only on detail page — single call, cached
                        if ($fitResult['score'] >= 40) {
                            $fitResult['reason'] = $matcher->generateReason($listing, $filters);
                        }
                    }
                }
            }
        }

        $this->setSeo([
            'title'       => ($listing['location_approx'] ?? 'Inserat') . ' — modeiland',
            'description' => mb_substr(strip_tags($listing['ai_description'] ?? ''), 0, 160),
        ]);

        return $this->render('App\Modules\Estate\Views\seeker\show', [
            'listing'   => $listing,
            'images'    => $images,
            'fitResult' => $fitResult,
            'canApply'  => $canApply,
        ]);
    }
}
