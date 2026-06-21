<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Public blog rotaları (TR prefixsiz, DE/EN prefixli)
$routes->get('blog',               '\App\Modules\Blog\Controllers\BlogController::index');
$routes->get('blog/(:segment)',    '\App\Modules\Blog\Controllers\BlogController::show/$1');
$routes->get('de/blog',            '\App\Modules\Blog\Controllers\BlogController::index');
$routes->get('de/blog/(:segment)', '\App\Modules\Blog\Controllers\BlogController::show/$1');
$routes->get('en/blog',            '\App\Modules\Blog\Controllers\BlogController::index');
$routes->get('en/blog/(:segment)', '\App\Modules\Blog\Controllers\BlogController::show/$1');

// Admin blog rotaları
$routes->group('admin/blog', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('/',              '\App\Modules\Blog\Controllers\Admin\BlogAdminController::index');
    $routes->get('new',            '\App\Modules\Blog\Controllers\Admin\BlogAdminController::create');
    $routes->post('/',             '\App\Modules\Blog\Controllers\Admin\BlogAdminController::store');
    $routes->get('(:num)/edit',    '\App\Modules\Blog\Controllers\Admin\BlogAdminController::edit/$1');
    $routes->post('(:num)',        '\App\Modules\Blog\Controllers\Admin\BlogAdminController::update/$1');
    $routes->post('(:num)/delete', '\App\Modules\Blog\Controllers\Admin\BlogAdminController::destroy/$1');
});
