<?php

namespace App\Core\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class BaseWebController extends Controller
{
    protected array $seoData = [];
    protected string $locale = 'tr';

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);

        helper(['url', 'form', 'cekirdek']);

        // Locale algılama: URL prefix veya session
        $uriSegment = service('request')->getUri()->getSegment(1);
        $supported  = ['tr', 'de', 'en'];

        if (in_array($uriSegment, $supported, true)) {
            $this->locale = $uriSegment;
        } elseif (session()->has('locale')) {
            $this->locale = session()->get('locale');
        }

        service('language')->setLocale($this->locale);
    }

    protected function setSeo(array $data): void
    {
        $defaults = [
            'title'            => setting('site.title', 'CekirdekCMS'),
            'description'      => setting('site.description', ''),
            'robots'           => 'index,follow',
            'canonical'        => current_url(),
            'og_type'          => 'website',
            'og_image'         => base_url('public/img/og-default.jpg'),
            'locale'           => $this->locale,
        ];

        $this->seoData = array_merge($defaults, $data);
    }

    protected function render(string $view, array $data = []): string
    {
        $data['locale']   = $this->locale;
        $data['seoData']  = $this->seoData;
        $data['content']  = view($view, $data);

        return view('web/layout', $data);
    }
}
