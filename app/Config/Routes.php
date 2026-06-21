<?php

use App\Core\Modules\ModuleRegistry;
use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Ana sayfa
$routes->get('/', '\App\Core\Controllers\HomeController::index');

// Sitemap & Robots
$routes->get('sitemap.xml', '\App\Core\Controllers\SitemapController::sitemap');
$routes->get('robots.txt',  '\App\Core\Controllers\SitemapController::robots');

// Core rotalar (auth, admin dashboard, settings, media)
require_once APPPATH . 'Core/Config/Routes.php';

// Modül rotaları module.json içindeki routePriority sırasıyla yüklenir.
foreach (ModuleRegistry::routeFiles() as $routeFile) {
    require_once $routeFile;
}
