<?php

namespace App\Modules\Estate\Controllers\Seeker;

use App\Core\Controllers\BaseWebController;
use App\Modules\Estate\Models\SeekerModel;
use App\Modules\Estate\Models\SeekerProfileModel;

/**
 * Seeker profile — name, move-in date, household, occupation, income, pets, notes.
 * Used by AiBewerbungService to generate personalised cover letters.
 *
 * GET  /seeker/profil
 * POST /seeker/profil
 */
class SeekerProfileController extends BaseWebController
{
    private SeekerModel        $seekers;
    private SeekerProfileModel $profiles;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->seekers  = new SeekerModel();
        $this->profiles = new SeekerProfileModel();
    }

    public function index(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $seeker = $this->requireSeeker();
        if ($seeker instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $seeker;
        }

        $profile = $this->profiles->findBySeeker($seeker['id']);

        $this->setSeo(['title' => 'Mein Profil — modeiland', 'robots' => 'noindex']);

        return $this->render('App\Modules\Estate\Views\seeker\seeker_profile', [
            'seeker'  => $seeker,
            'profile' => $profile,
        ]);
    }

    public function update(): \CodeIgniter\HTTP\RedirectResponse
    {
        $seeker = $this->requireSeeker();
        if ($seeker instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $seeker;
        }

        $rules = [
            'name'           => 'permit_empty|max_length[150]',
            'move_in_date'   => 'permit_empty|max_length[20]',
            'household_size' => 'permit_empty|integer|greater_than[0]|less_than[20]',
            'occupation'     => 'permit_empty|max_length[100]',
            'income_range'   => 'permit_empty|integer|greater_than_equal_to[0]',
            'pets'           => 'permit_empty|in_list[0,1]',
            'notes'          => 'permit_empty|max_length[1000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $incomeEuro = (int) ($this->request->getPost('income_range') ?? 0);

        $this->profiles->upsert($seeker['id'], [
            'name'              => trim($this->request->getPost('name') ?? ''),
            'move_in_date'      => trim($this->request->getPost('move_in_date') ?? ''),
            'household_size'    => $this->request->getPost('household_size') ?: null,
            'occupation'        => trim($this->request->getPost('occupation') ?? ''),
            'income_range_cents'=> $incomeEuro * 100,
            'pets'              => (int) ($this->request->getPost('pets') ?? 0),
            'notes'             => trim($this->request->getPost('notes') ?? ''),
        ]);

        return redirect()->to(site_url('seeker/profil'))
                         ->with('success', 'Profil gespeichert.');
    }

    private function requireSeeker(): array|\CodeIgniter\HTTP\RedirectResponse
    {
        $id = session()->get('estate_seeker_id');
        if (! $id) {
            return redirect()->to(site_url('abonnieren'));
        }
        $seeker = $this->seekers->find((int) $id);
        if (! $seeker) {
            session()->remove('estate_seeker_id');
            return redirect()->to(site_url('abonnieren'));
        }
        return $seeker;
    }
}
