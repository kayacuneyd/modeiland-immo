<?php

namespace App\Core\Controllers;

use CodeIgniter\Controller;

class SitemapController extends Controller
{
    public function sitemap(): \CodeIgniter\HTTP\ResponseInterface
    {
        $db     = db_connect();
        $urls   = [];
        $base   = rtrim(base_url(), '/');

        // Statik rotalar
        $urls[] = ['loc' => $base . '/', 'changefreq' => 'weekly', 'priority' => '1.0'];
        $urls[] = ['loc' => $base . '/blog', 'changefreq' => 'daily', 'priority' => '0.8'];
        $urls[] = ['loc' => $base . '/contact', 'changefreq' => 'monthly', 'priority' => '0.5'];

        // Pages
        $pages = $db->table('pages')
            ->where('status', 'published')
            ->where('deleted_at IS NULL', null, false)
            ->get()->getResultArray();
        foreach ($pages as $page) {
            $urls[] = [
                'loc'        => $base . '/' . $page['slug'],
                'lastmod'    => date('Y-m-d', strtotime($page['updated_at'])),
                'changefreq' => 'monthly',
                'priority'   => '0.7',
            ];
        }

        // Blog posts
        $posts = $db->table('posts')
            ->where('status', 'published')
            ->where('deleted_at IS NULL', null, false)
            ->get()->getResultArray();
        foreach ($posts as $post) {
            $prefix = match ($post['lang']) {
                'de'    => '/de',
                'en'    => '/en',
                default => '',
            };
            $urls[] = [
                'loc'        => $base . $prefix . '/blog/' . $post['slug'],
                'lastmod'    => date('Y-m-d', strtotime($post['updated_at'])),
                'changefreq' => 'weekly',
                'priority'   => '0.6',
            ];
        }

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . esc($url['loc']) . "</loc>\n";
            if (! empty($url['lastmod'])) {
                $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            }
            $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$url['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return $this->response
            ->setHeader('Content-Type', 'application/xml; charset=utf-8')
            ->setBody($xml);
    }

    public function robots(): \CodeIgniter\HTTP\ResponseInterface
    {
        $content = setting('seo.robots_txt', "User-agent: *\nAllow: /\nSitemap: " . base_url('sitemap.xml'));

        return $this->response
            ->setHeader('Content-Type', 'text/plain; charset=utf-8')
            ->setBody($content);
    }
}
