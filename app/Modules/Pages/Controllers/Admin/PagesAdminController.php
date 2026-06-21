<?php

namespace App\Modules\Pages\Controllers\Admin;

use App\Core\Controllers\BaseAdminController;
use App\Modules\Pages\Models\PageModel;

class PagesAdminController extends BaseAdminController
{
    private PageModel $model;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->model = new PageModel();
    }

    public function index(): string
    {
        return $this->render('App\Modules\Pages\Views\admin\index', [
            'pageTitle' => lang('Common.nav_pages'),
            'pages'     => $this->model->getAllForAdmin(),
        ]);
    }

    public function create(): string
    {
        return $this->render('App\Modules\Pages\Views\admin\edit', [
            'pageTitle' => lang('Common.new_page'),
            'page'      => null,
        ]);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requirePermission('pages.create');

        $data = $this->getFormData();

        if (! $this->model->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        return redirect()->to(site_url('admin/pages'))->with('success', lang('Common.saved'));
    }

    public function edit(int $id): string
    {
        $page = $this->model->find($id);
        if (! $page) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        return $this->render('App\Modules\Pages\Views\admin\edit', [
            'pageTitle' => lang('Common.edit') . ': ' . $page['title'],
            'page'      => $page,
        ]);
    }

    public function update(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requirePermission('pages.edit');

        $data = $this->getFormData();

        if (! $this->model->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        return redirect()->to(site_url('admin/pages'))->with('success', lang('Common.saved'));
    }

    public function destroy(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requirePermission('pages.delete');
        $this->model->delete($id);

        return redirect()->to(site_url('admin/pages'))->with('success', lang('Common.deleted'));
    }

    private function getFormData(): array
    {
        $slug = $this->request->getPost('slug') ?: slug($this->request->getPost('title') ?? '');

        return [
            'title'            => $this->request->getPost('title'),
            'slug'             => $slug,
            'lang'             => $this->request->getPost('lang') ?: 'tr',
            'content'          => $this->request->getPost('content'),
            'meta_title'       => $this->request->getPost('meta_title'),
            'meta_description' => $this->request->getPost('meta_description'),
            'status'           => $this->request->getPost('status') ?: 'draft',
            'sort_order'       => (int) $this->request->getPost('sort_order'),
            'media_id'         => $this->request->getPost('media_id') ?: null,
        ];
    }
}
