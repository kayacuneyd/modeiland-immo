<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ─── Admin routes (CI4 core auth filter) ────────────────────────────────────
$routes->group('admin/estate', ['filter' => 'auth'], static function (RouteCollection $routes): void {

    // Owner leads
    $routes->get('owners',               '\App\Modules\Estate\Controllers\Admin\OwnerAdminController::index');
    $routes->get('owners/new',           '\App\Modules\Estate\Controllers\Admin\OwnerAdminController::create');
    $routes->post('owners',              '\App\Modules\Estate\Controllers\Admin\OwnerAdminController::store');
    $routes->get('owners/(:num)',        '\App\Modules\Estate\Controllers\Admin\OwnerAdminController::show/$1');
    $routes->post('owners/(:num)/delete',          '\App\Modules\Estate\Controllers\Admin\OwnerAdminController::destroy/$1');
    $routes->post('owners/(:num)/generate-invite', '\App\Modules\Estate\Controllers\Admin\OwnerAdminController::generateInvite/$1');

    // Listings / AI import
    $routes->get('listings',                   '\App\Modules\Estate\Controllers\Admin\ListingAdminController::index');
    $routes->get('listings/new/(:num)',        '\App\Modules\Estate\Controllers\Admin\ListingAdminController::create/$1');
    $routes->post('listings',                  '\App\Modules\Estate\Controllers\Admin\ListingAdminController::store');
    $routes->get('listings/(:num)',            '\App\Modules\Estate\Controllers\Admin\ListingAdminController::show/$1');
    $routes->post('listings/(:num)/ai-import','\App\Modules\Estate\Controllers\Admin\ListingAdminController::aiImport/$1');
    $routes->post('listings/(:num)/publish',  '\App\Modules\Estate\Controllers\Admin\ListingAdminController::publish/$1');
    $routes->post('listings/(:num)/delete',   '\App\Modules\Estate\Controllers\Admin\ListingAdminController::destroy/$1');
});

// ─── Owner public routes (no auth required) ───────────────────────────────────
// Invite link landing — validates token, creates session, redirects into protected area
$routes->get('einladung/(:segment)',  '\App\Modules\Estate\Controllers\Owner\InviteController::accept/$1');

// Magic link (post-upgrade login) — validates token, creates session
$routes->get('owner/magiclink/(:segment)', '\App\Modules\Estate\Controllers\Owner\MagicLinkController::accept/$1');

// Magic link request form (no auth)
$routes->get('owner/login',          '\App\Modules\Estate\Controllers\Owner\MagicLinkController::loginForm');
$routes->post('owner/login',         '\App\Modules\Estate\Controllers\Owner\MagicLinkController::sendLink');
$routes->get('owner/login/gesendet', '\App\Modules\Estate\Controllers\Owner\MagicLinkController::sent');

// Logout (public — clears cookie)
$routes->get('owner/logout',         '\App\Modules\Estate\Controllers\Owner\InviteController::logout');

// ─── Owner protected routes (OwnerAuthFilter) ─────────────────────────────────
$ownerFilter = \App\Modules\Estate\Filters\OwnerAuthFilter::class;

$routes->group('owner', ['filter' => $ownerFilter], static function (RouteCollection $routes): void {

    // Listing draft preview + consent (Faz 1 route protected in Faz 2)
    $routes->get('draft/(:num)',    '\App\Modules\Estate\Controllers\Owner\OwnerController::draft/$1');
    $routes->post('draft/(:num)',   '\App\Modules\Estate\Controllers\Owner\OwnerController::approve/$1');

    // Extra listing payment (Faz 3)
    $routes->get('listing-checkout/(:num)',          '\App\Modules\Estate\Controllers\Owner\ListingCheckoutController::show/$1');
    $routes->post('listing-checkout/(:num)/start',   '\App\Modules\Estate\Controllers\Owner\ListingCheckoutController::start/$1');
    $routes->get('listing-checkout/(:num)/erfolg',   '\App\Modules\Estate\Controllers\Owner\ListingCheckoutController::success/$1');
    $routes->get('listing-checkout/(:num)/abbruch',  '\App\Modules\Estate\Controllers\Owner\ListingCheckoutController::cancel/$1');

    // Panel — 3-zone message inbox
    $routes->get('panel',          '\App\Modules\Estate\Controllers\Owner\PanelController::index');
    $routes->get('messages/poll',  '\App\Modules\Estate\Controllers\Owner\PanelController::poll');

    // Listing actions from panel
    $routes->post('listings/(:num)/pausieren', '\App\Modules\Estate\Controllers\Owner\PanelController::pausieren/$1');
    $routes->post('listings/(:num)/entfernen', '\App\Modules\Estate\Controllers\Owner\PanelController::entfernen/$1');

    // Profile / account upgrade
    $routes->get('profil',         '\App\Modules\Estate\Controllers\Owner\ProfileController::index');
    $routes->post('profil',        '\App\Modules\Estate\Controllers\Owner\ProfileController::update');

    // Step-up re-authentication (sensitive ops)
    $routes->get('stepup',         '\App\Modules\Estate\Controllers\Owner\StepUpController::form');
    $routes->post('stepup',        '\App\Modules\Estate\Controllers\Owner\StepUpController::verify');
});

// ─── Stripe webhook (no auth — verified by Stripe-Signature header) ──────────
$routes->post('webhooks/stripe', '\App\Modules\Estate\Controllers\Stripe\WebhookController::handle');

// ─── Seeker / public routes ───────────────────────────────────────────────────
$routes->get('inserate',              '\App\Modules\Estate\Controllers\Seeker\SearchController::index');
$routes->get('inserate/(:num)',       '\App\Modules\Estate\Controllers\Seeker\ListingController::show/$1');
$routes->get('inserate/(:num)/kontakt', '\App\Modules\Estate\Controllers\Seeker\MessageController::form/$1');
$routes->post('inserate/(:num)/kontakt','\App\Modules\Estate\Controllers\Seeker\MessageController::send/$1');

// Subscription / paywall (Faz 3)
$routes->get('abonnieren',              '\App\Modules\Estate\Controllers\Seeker\SubscribeController::index');
$routes->post('abonnieren/checkout',    '\App\Modules\Estate\Controllers\Seeker\SubscribeController::checkout');
$routes->get('abonnieren/erfolg',       '\App\Modules\Estate\Controllers\Seeker\SubscribeController::success');
$routes->get('abonnieren/abbrechen',    '\App\Modules\Estate\Controllers\Seeker\SubscribeController::cancel');
$routes->post('abonnieren/portal',      '\App\Modules\Estate\Controllers\Seeker\SubscribeController::portal');

// Legacy alias kept for any existing links
$routes->get('abonnement',  '\App\Modules\Estate\Controllers\Seeker\SubscribeController::index');
$routes->post('abonnement', '\App\Modules\Estate\Controllers\Seeker\SubscribeController::checkout');

// Seeker panel (session-protected, no separate filter — controller checks session)
$routes->get('seeker/panel',              '\App\Modules\Estate\Controllers\Seeker\SeekerPanelController::index');
$routes->get('seeker/messages/poll',      '\App\Modules\Estate\Controllers\Seeker\SeekerPanelController::poll');
$routes->get('seeker/logout',             '\App\Modules\Estate\Controllers\Seeker\SeekerPanelController::logout');

// Saved searches
$routes->post('seeker/suche/speichern',           '\App\Modules\Estate\Controllers\Seeker\SavedSearchController::save');
$routes->post('seeker/suche/(:num)/loeschen',     '\App\Modules\Estate\Controllers\Seeker\SavedSearchController::delete/$1');
$routes->post('seeker/suche/(:num)/alarm',        '\App\Modules\Estate\Controllers\Seeker\SavedSearchController::toggleAlert/$1');

// Seeker profile (Faz 4)
$routes->get('seeker/profil',   '\App\Modules\Estate\Controllers\Seeker\SeekerProfileController::index');
$routes->post('seeker/profil',  '\App\Modules\Estate\Controllers\Seeker\SeekerProfileController::update');

// AI Bewerbungspaket (Faz 4)
$routes->get('inserate/(:num)/bewerben',     '\App\Modules\Estate\Controllers\Seeker\ApplicationController::show/$1');
$routes->post('inserate/(:num)/bewerben/neu','\App\Modules\Estate\Controllers\Seeker\ApplicationController::regenerate/$1');

// ─── Legal routes ─────────────────────────────────────────────────────────────
$routes->get('impressum',            '\App\Modules\Estate\Controllers\Legal\LegalController::impressum');
$routes->get('datenschutz',          '\App\Modules\Estate\Controllers\Legal\LegalController::datenschutz');
$routes->get('agb',                  '\App\Modules\Estate\Controllers\Legal\LegalController::agb');
