<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Ana sayfa
$routes->get('/', '\App\Core\Controllers\HomeController::index');

// Sitemap & Robots
$routes->get('sitemap.xml', '\App\Core\Controllers\SitemapController::sitemap');
$routes->get('robots.txt',  '\App\Core\Controllers\SitemapController::robots');

// Core rotalar (auth, admin dashboard, settings, media)
require_once APPPATH . 'Core/Config/Routes.php';

// Modül rotaları (Pages en son çünkü catch-all slug içeriyor)
require_once APPPATH . 'Modules/Blog/Config/Routes.php';
require_once APPPATH . 'Modules/Contact/Config/Routes.php';
require_once APPPATH . 'Modules/Pages/Config/Routes.php';
