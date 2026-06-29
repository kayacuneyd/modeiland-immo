<?php

namespace App\Modules\Estate\Controllers\Owner;

use App\Core\Controllers\BaseWebController;
use App\Modules\Estate\Services\OwnerAuthService;

/**
 * Owner profile upgrade: add email / phone / password.
 * Completing this revokes the invite token and switches login_method.
 */
class ProfileController extends BaseWebController
{
    private OwnerAuthService $auth;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->auth = new OwnerAuthService();
    }

    /** GET /owner/profil */
    public function index(): string
    {
        $ownerId = (int) session()->get('estate_owner_id');
        $owner   = db_connect()->table('owners')->where('id', $ownerId)->get()->getRowArray();

        $this->setSeo(['title' => 'Mein Profil — modeiland', 'robots' => 'noindex']);

        return $this->render('App\Modules\Estate\Views\owner\profile', [
            'owner' => $owner,
        ]);
    }

    /** POST /owner/profil */
    public function update(): \CodeIgniter\HTTP\RedirectResponse
    {
        $ownerId = (int) session()->get('estate_owner_id');
        $owner   = db_connect()->table('owners')->where('id', $ownerId)->get()->getRowArray();

        if (! $owner) {
            return redirect()->to(site_url('owner/login'));
        }

        $email    = trim($this->request->getPost('email') ?? '');
        $phone    = trim($this->request->getPost('phone') ?? '');
        $password = $this->request->getPost('password') ?? '';
        $confirm  = $this->request->getPost('password_confirm') ?? '';

        $errors = [];

        if ($email && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Ungültige E-Mail-Adresse.';
        }

        if ($password) {
            if (mb_strlen($password) < 10) {
                $errors[] = 'Passwort muss mindestens 10 Zeichen lang sein.';
            }
            if ($password !== $confirm) {
                $errors[] = 'Passwörter stimmen nicht überein.';
            }
        }

        if (empty($email) && empty($phone) && empty($password)) {
            $errors[] = 'Bitte mindestens ein Feld ausfüllen.';
        }

        if ($errors) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $data = [];
        if ($email)    $data['email']    = $email;
        if ($phone)    $data['phone']    = $phone;
        if ($password) $data['password'] = $password;

        // Rotates session inside (privilege change)
        $this->auth->upgradeOwner($ownerId, $data);

        return redirect()->to(site_url('owner/panel'))
                         ->with('success', 'Ihr Profil wurde aktualisiert. Der Einladungslink ist jetzt deaktiviert.');
    }
}
