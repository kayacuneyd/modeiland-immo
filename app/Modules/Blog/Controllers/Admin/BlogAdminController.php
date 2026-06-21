<?php

namespace App\Modules\Blog\Controllers\Admin;

use App\Core\Controllers\BaseAdminController;
use App\Modules\Blog\Models\CategoryModel;
use App\Modules\Blog\Models\PostModel;

class BlogAdminController extends BaseAdminController
{
    private PostModel     $posts;
    private CategoryModel $categories;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->posts      = new PostModel();
        $this->categories = new CategoryModel();
    }

    public function index(): string
    {
        return $this->render('App\Modules\Blog\Views\admin\index', [
            'pageTitle' => lang('Blog.admin_title'),
            'posts'     => $this->posts->getAllForAdmin(),
        ]);
    }

    public function create(): string
    {
        return $this->render('App\Modules\Blog\Views\admin\edit', [
            'pageTitle'  => lang('Blog.new_post'),
            'post'       => null,
            'categories' => $this->getCategoryOptions(),
        ]);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requirePermission('blog.create');

        $data = $this->getFormData();

        if (! $this->posts->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->posts->errors());
        }

        return redirect()->to(site_url('admin/blog'))->with('success', lang('Common.saved'));
    }

    public function edit(int $id): string
    {
        $post = $this->posts->find($id);
        if (! $post) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        return $this->render('App\Modules\Blog\Views\admin\edit', [
            'pageTitle'  => lang('Common.edit') . ': ' . $post['title'],
            'post'       => $post,
            'categories' => $this->getCategoryOptions(),
        ]);
    }

    public function update(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requirePermission('blog.edit');

        $data = $this->getFormData();

        if (! $this->posts->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->posts->errors());
        }

        return redirect()->to(site_url('admin/blog'))->with('success', lang('Common.saved'));
    }

    public function destroy(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requirePermission('blog.delete');
        $this->posts->delete($id);

        return redirect()->to(site_url('admin/blog'))->with('success', lang('Common.deleted'));
    }

    private function getFormData(): array
    {
        $slug = $this->request->getPost('slug') ?: slug($this->request->getPost('title') ?? '');
        $publishedAt = $this->request->getPost('published_at') ?: date('Y-m-d H:i:s');

        return [
            'title'            => $this->request->getPost('title'),
            'slug'             => $slug,
            'lang'             => $this->request->getPost('lang') ?: 'tr',
            'excerpt'          => $this->request->getPost('excerpt'),
            'content'          => $this->request->getPost('content'),
            'category_id'      => $this->request->getPost('category_id') ?: null,
            'media_id'         => $this->request->getPost('media_id') ?: null,
            'status'           => $this->request->getPost('status') ?: 'draft',
            'published_at'     => $publishedAt,
            'meta_title'       => $this->request->getPost('meta_title'),
            'meta_description' => $this->request->getPost('meta_description'),
        ];
    }

    private function getCategoryOptions(): array
    {
        $options = ['' => '— Kategori Seç —'];
        $cats    = $this->categories->findAll();
        foreach ($cats as $cat) {
            $options[$cat['id']] = "[{$cat['lang']}] {$cat['name']}";
        }
        return $options;
    }
}
