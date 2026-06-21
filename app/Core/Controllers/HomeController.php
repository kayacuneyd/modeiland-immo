<?php

namespace App\Core\Controllers;

use App\Modules\Blog\Models\PostModel;

class HomeController extends BaseWebController
{
    public function index(): string
    {
        $this->setSeo([
            'title'       => setting('site.tagline', 'Yeniden kullanılabilir CI4 CMS çekirdeği'),
            'description' => setting('site.description', ''),
            'og_type'     => 'website',
        ]);

        // Son 3 blog yazısını göster
        $posts = [];
        try {
            $posts = (new PostModel())->getPublishedByLang('tr', 1, 3);
        } catch (\Throwable) {
            // DB henüz kurulmamış olabilir
        }

        return $this->render('web/home', ['posts' => $posts]);
    }
}
