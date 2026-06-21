<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Public contact rotaları
$routes->get('contact',         '\App\Modules\Contact\Controllers\ContactController::show');
$routes->post('contact',        '\App\Modules\Contact\Controllers\ContactController::submit');
$routes->get('contact/success', '\App\Modules\Contact\Controllers\ContactController::success');

// Çok dilli
$routes->get('de/contact',         '\App\Modules\Contact\Controllers\ContactController::show');
$routes->post('de/contact',        '\App\Modules\Contact\Controllers\ContactController::submit');
$routes->get('de/contact/success', '\App\Modules\Contact\Controllers\ContactController::success');
$routes->get('en/contact',         '\App\Modules\Contact\Controllers\ContactController::show');
$routes->post('en/contact',        '\App\Modules\Contact\Controllers\ContactController::submit');
$routes->get('en/contact/success', '\App\Modules\Contact\Controllers\ContactController::success');

// Admin contact rotaları
$routes->group('admin/contact', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('/',         '\App\Modules\Contact\Controllers\Admin\ContactAdminController::index');
    $routes->get('(:num)',    '\App\Modules\Contact\Controllers\Admin\ContactAdminController::show/$1');
});
