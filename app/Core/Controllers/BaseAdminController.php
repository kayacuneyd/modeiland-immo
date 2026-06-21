<?php

namespace App\Core\Controllers;

use App\Core\Auth\AuthService;
use App\Core\Modules\ModuleRegistry;
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
    }

    protected function requirePermission(string $permission): void
    {
        if (! $this->auth->can($permission)) {
            $this->response->setStatusCode(403);
            echo $this->render('admin/errors/403', ['message' => lang('Common.forbidden')]);
            exit;
        }
    }

    protected function render(string $view, array $data = []): string
    {
        $data['adminUser'] = $this->auth->user();
        $data['adminNav']  = ModuleRegistry::adminNav($this->auth);
        $data['content']   = view($view, $data);

        return view('admin/layout', $data);
    }
}
