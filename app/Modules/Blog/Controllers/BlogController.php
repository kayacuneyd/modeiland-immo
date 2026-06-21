<?php

namespace App\Modules\Blog\Controllers;

use App\Core\Controllers\BaseWebController;
use App\Modules\Blog\Models\PostModel;

class BlogController extends BaseWebController
{
    private PostModel $model;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->model = new PostModel();
    }

    public function index(): string
    {
        $page    = (int) ($this->request->getGet('page') ?: 1);
        $posts   = $this->model->getPublishedByLang($this->locale, $page, 12);
        $total   = $this->model->countPublishedByLang($this->locale);

        $this->setSeo([
            'title'       => lang('Blog.title'),
            'description' => lang('Blog.all_posts'),
        ]);

        return $this->render('App\Modules\Blog\Views\index', [
            'posts'   => $posts,
            'total'   => $total,
            'page'    => $page,
            'perPage' => 12,
        ]);
    }

    public function show(string $slug): string
    {
        $post = $this->model->findPublishedBySlug($slug, $this->locale);

        if (! $post) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Yazı bulunamadı: {$slug}");
        }

        $adjacent = $this->model->getAdjacent($post['id'], $this->locale);

        $this->setSeo([
            'title'       => $post['meta_title'] ?: $post['title'],
            'description' => $post['meta_description'] ?: ($post['excerpt'] ?: ''),
        ]);

        return $this->render('App\Modules\Blog\Views\show', [
            'post'     => $post,
            'adjacent' => $adjacent,
        ]);
    }
}
