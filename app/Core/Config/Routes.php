<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Auth rotaları
$routes->get('admin/login',   '\App\Core\Auth\AuthController::login');
$routes->post('admin/login',  '\App\Core\Auth\AuthController::authenticate');
$routes->post('admin/logout', '\App\Core\Auth\AuthController::logout');

// Admin dashboard
$routes->get('admin',           '\App\Core\Controllers\AdminDashboardController::index', ['filter' => 'auth']);
$routes->get('admin/dashboard', '\App\Core\Controllers\AdminDashboardController::index', ['filter' => 'auth']);

// Settings
$routes->group('admin/settings', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('/',  '\App\Core\Controllers\SettingsAdminController::index');
    $routes->post('/', '\App\Core\Controllers\SettingsAdminController::update');
    $routes->post('cache/clear', '\App\Core\Controllers\SettingsAdminController::clearCache');
});

// Media
$routes->group('admin/media', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('/',             '\App\Core\Controllers\MediaAdminController::index');
    $routes->post('/',            '\App\Core\Controllers\MediaAdminController::upload');
    $routes->post('(:num)/delete','\App\Core\Controllers\MediaAdminController::delete/$1');
    $routes->get('picker',        '\App\Core\Controllers\MediaAdminController::picker');
});
