<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Admin sayfaları — önce admin (catch-all'dan önce gelmelidir)
$routes->group('admin/pages', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('/',             '\App\Modules\Pages\Controllers\Admin\PagesAdminController::index');
    $routes->get('new',           '\App\Modules\Pages\Controllers\Admin\PagesAdminController::create');
    $routes->post('/',            '\App\Modules\Pages\Controllers\Admin\PagesAdminController::store');
    $routes->get('(:num)/edit',   '\App\Modules\Pages\Controllers\Admin\PagesAdminController::edit/$1');
    $routes->post('(:num)',       '\App\Modules\Pages\Controllers\Admin\PagesAdminController::update/$1');
    $routes->post('(:num)/delete','\App\Modules\Pages\Controllers\Admin\PagesAdminController::destroy/$1');
});

// Public — EN SON (catch-all slug, diğer route'lardan sonra tanımlanmalı)
$routes->get('(:segment)', '\App\Modules\Pages\Controllers\PagesController::show/$1');
