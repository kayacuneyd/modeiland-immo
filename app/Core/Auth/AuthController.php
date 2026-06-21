<?php

namespace App\Core\Auth;

use App\Core\Controllers\BaseWebController;

class AuthController extends BaseWebController
{
    private AuthService $auth;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->auth = new AuthService();
    }

    public function login(): string
    {
        if ($this->auth->check()) {
            return redirect()->to(site_url('admin/dashboard'))->send() ?: '';
        }

        $this->setSeo(['title' => lang('Auth.login_title')]);

        return view('admin/login', ['seoData' => $this->seoData]);
    }

    public function authenticate(): \CodeIgniter\HTTP\RedirectResponse
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        if (! $this->auth->login($email, $password)) {
            return redirect()->back()->withInput()->with('error', lang('Auth.login_failed'));
        }

        $redirectTo = session()->get('redirect_after_login') ?: site_url('admin/dashboard');
        session()->remove('redirect_after_login');

        return redirect()->to($redirectTo);
    }

    public function logout(): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->auth->logout();

        return redirect()->to(site_url('admin/login'))->with('success', lang('Auth.logout_success'));
    }
}
