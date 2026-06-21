<?php

namespace App\Core\Controllers;

use App\Core\Settings\SettingsService;

class SettingsAdminController extends BaseAdminController
{
    private SettingsService $settings;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->settings = new SettingsService();
    }

    public function index(): string
    {
        $grouped = $this->settings->getAllForAdmin();

        return $this->render('admin/settings/index', [
            'pageTitle' => lang('Common.nav_settings'),
            'grouped'   => $grouped,
        ]);
    }

    public function update(): \CodeIgniter\HTTP\RedirectResponse
    {
        $post = $this->request->getPost();
        unset($post[$this->request->config->CSRFTokenName ?? 'csrf_test_name']);

        foreach ($post as $key => $value) {
            $key = str_replace('__', '.', $key); // HTML name'deki '.' yerine '__' kullanıyoruz
            $this->settings->set($key, $value);
        }

        return redirect()->to(site_url('admin/settings'))->with('success', lang('Common.saved'));
    }

    public function clearCache(): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->settings->bustCache();
        cache()->clean();

        return redirect()->to(site_url('admin/settings'))->with('success', lang('Common.cache_cleared'));
    }
}
