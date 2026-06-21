<?php

namespace App\Core\Controllers;

use App\Core\Auth\AuthService;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class BaseAdminController extends Controller
{
    protected AuthService $auth;

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);

        helper(['url', 'form', 'cekirdek']);

        $this->auth = new AuthService();
        $this->guard();
    }

    protected function guard(): void
    {
        if (! $this->auth->check()) {
            session()->set('redirect_after_login', current_url());
            header('Location: ' . site_url('admin/login'));
            exit;
        }
    }

    protected function requirePermission(string $permission): void
    {
        if (! $this->auth->can($permission)) {
            echo $this->render('admin/errors/403', ['message' => lang('Common.forbidden')]);
            exit;
        }
    }

    protected function render(string $view, array $data = []): string
    {
        $data['adminUser'] = $this->auth->user();
        $data['content']   = view($view, $data);

        return view('admin/layout', $data);
    }
}
