<?php

namespace App\Modules\Pages\Controllers;

use App\Core\Controllers\BaseWebController;
use App\Modules\Pages\Models\PageModel;

class PagesController extends BaseWebController
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

    public function show(string $slug): string
    {
        $page = $this->model->findBySlugAndLang($slug, $this->locale);

        if (! $page || $page['status'] !== 'published') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Sayfa bulunamadı: {$slug}");
        }

        $this->setSeo([
            'title'       => $page['meta_title'] ?: $page['title'],
            'description' => $page['meta_description'] ?: '',
            'canonical'   => site_url($slug),
        ]);

        return $this->render('App\Modules\Pages\Views\show', ['page' => $page]);
    }
}
