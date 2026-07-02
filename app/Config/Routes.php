<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Home
$routes->get('/', 'Home::index');
$routes->get('pricing', 'Home::pricing');
$routes->get('features', 'Home::features');

// Auth Routes
$routes->group('auth', function ($routes) {
    $routes->get('login', 'Auth\AuthController::loginPage');
    $routes->post('login', 'Auth\AuthController::login');
    $routes->get('register', 'Auth\AuthController::registerPage');
    $routes->post('register', 'Auth\AuthController::register');
    $routes->get('logout', 'Auth\AuthController::logout');
    $routes->get('forgot-password', 'Auth\AuthController::forgotPasswordPage');
    $routes->post('forgot-password', 'Auth\AuthController::forgotPassword');
    $routes->get('reset-password/(:alphanum)', 'Auth\AuthController::resetPasswordPage/$1');
    $routes->post('reset-password/(:alphanum)', 'Auth\AuthController::resetPassword/$1');

    // Social Auth
    $routes->get('social/(:alpha)', 'Auth\SocialAuthController::redirect/$1');
    $routes->get('callback/(:alpha)', 'Auth\SocialAuthController::callback/$1');
    $routes->post('callback/apple', 'Auth\SocialAuthController::callback/apple');
    $routes->get('link/(:alpha)', 'Auth\SocialAuthController::link/$1', ['filter' => 'jwt']);
    $routes->post('unlink/(:alpha)', 'Auth\SocialAuthController::unlink/$1', ['filter' => 'jwt']);
});

// API Auth
$routes->group('api/auth', function ($routes) {
    $routes->post('register', 'Auth\AuthController::apiRegister');
    $routes->post('login', 'Auth\AuthController::apiLogin');
    $routes->post('logout', 'Auth\AuthController::apiLogout', ['filter' => 'jwt']);
    $routes->post('forgot-password', 'Auth\AuthController::apiForgotPassword');
    $routes->post('reset-password/(:alphanum)', 'Auth\AuthController::apiResetPassword/$1');
    $routes->get('me', 'Auth\AuthController::me', ['filter' => 'jwt']);
    $routes->get('verify', 'Auth\AuthController::verify');
});

// Dashboard (protected)
$routes->get('dashboard', 'DashboardController::index', ['filter' => 'jwt']);

// Meetings (web views, protected)
$routes->group('meetings', ['filter' => 'jwt'], function ($routes) {
    $routes->get('/', 'Meeting\MeetingController::index');
    $routes->get('schedule', 'Meeting\MeetingController::schedulePage');
    $routes->get('(:segment)', 'Meeting\MeetingController::detail/$1');
    $routes->get('(:segment)/edit', 'Meeting\MeetingController::editPage/$1');
});

// Join & Room (web views)
$routes->get('join/(:segment)', 'Meeting\MeetingController::joinPage/$1');
$routes->get('room/(:segment)', 'Meeting\MeetingController::roomPage/$1', ['filter' => 'jwt']);

// Profile
$routes->get('profile', 'Auth\AuthController::profilePage', ['filter' => 'jwt']);
$routes->post('profile', 'Auth\AuthController::updateProfile', ['filter' => 'jwt']);

// API Meetings
$routes->group('api/meetings', ['filter' => 'jwt'], function ($routes) {
    $routes->get('resolve/(:segment)', 'Meeting\MeetingController::apiResolve/$1');
    $routes->get('/', 'Meeting\MeetingController::apiList');
    $routes->post('/', 'Meeting\MeetingController::apiCreate');
    $routes->get('(:segment)', 'Meeting\MeetingController::apiGet/$1');
    $routes->put('(:segment)', 'Meeting\MeetingController::apiUpdate/$1');
    $routes->delete('(:segment)', 'Meeting\MeetingController::apiDelete/$1');
    $routes->post('(:segment)/start', 'Meeting\MeetingController::apiStart/$1');
    $routes->post('(:segment)/end', 'Meeting\MeetingController::apiEnd/$1');
    $routes->get('(:segment)/participants', 'Meeting\ParticipantController::apiList/$1');
    $routes->post('(:segment)/admit/(:num)', 'Meeting\ParticipantController::apiAdmit/$1/$2');
    $routes->post('(:segment)/remove/(:num)', 'Meeting\ParticipantController::apiRemove/$1/$2');
    $routes->post('(:segment)/mute/(:num)', 'Meeting\ParticipantController::apiMute/$1/$2');
    $routes->post('(:segment)/invite', 'Meeting\InvitationController::apiSend/$1');
    $routes->get('(:segment)/recordings', 'Meeting\RecordingController::apiList/$1');
    $routes->post('(:segment)/recordings', 'Meeting\RecordingController::apiCreate/$1');
    $routes->patch('(:segment)/recordings/(:num)', 'Meeting\RecordingController::apiStop/$1/$2');
    $routes->post('(:segment)/recordings/stop', 'Meeting\RecordingController::apiStopLatest/$1');
});

$routes->post('api/meetings/(:segment)/join', 'Meeting\MeetingController::apiJoin/$1');

// Cloudflare Realtime SFU proxy (keeps App Secret server-side)
$routes->group('sfu-proxy', ['filter' => 'jwt'], function ($routes) {
    $routes->post('sessions/new',                          'SfuProxyController::newSession');
    $routes->post('sessions/(:segment)/tracks/new',        'SfuProxyController::newTracks/$1');
    $routes->put('sessions/(:segment)/renegotiate',        'SfuProxyController::renegotiate/$1');
    $routes->put('sessions/(:segment)/tracks/close',       'SfuProxyController::closeTracks/$1');
});

// ── VTalanoa Workplace ─────────────────────────────────────────────────────

// Web view (session auth)
$routes->get('workspace', 'WorkspaceController::index', ['filter' => 'jwt']);

// API — Team Chat
$routes->group('api/workspace', ['filter' => 'jwt'], function ($routes) {
    $routes->get('channels',                         'Workspace\ChatController::channels');
    $routes->post('channels',                        'Workspace\ChatController::createChannel');
    $routes->get('channels/(:num)/messages',         'Workspace\ChatController::messages/$1');
    $routes->post('channels/(:num)/messages',        'Workspace\ChatController::send/$1');
    $routes->get('users',                            'Workspace\ChatController::users');

    // Docs
    $routes->get('docs',                             'Workspace\DocController::index');
    $routes->post('docs',                            'Workspace\DocController::create');
    $routes->get('docs/(:num)',                      'Workspace\DocController::show/$1');
    $routes->put('docs/(:num)',                      'Workspace\DocController::update/$1');
    $routes->delete('docs/(:num)',                   'Workspace\DocController::destroy/$1');

    // Mail
    $routes->get('mail',                             'Workspace\MailController::index');
    $routes->post('mail',                            'Workspace\MailController::send');
    $routes->get('mail/(:num)',                      'Workspace\MailController::show/$1');
    $routes->delete('mail/(:num)',                   'Workspace\MailController::trash/$1');
    $routes->patch('mail/(:num)/star',               'Workspace\MailController::star/$1');

    // Calendar
    $routes->get('calendar',                         'Workspace\CalendarController::events');
    $routes->post('calendar',                        'Workspace\CalendarController::create');
    $routes->put('calendar/(:num)',                  'Workspace\CalendarController::update/$1');
    $routes->delete('calendar/(:num)',               'Workspace\CalendarController::destroy/$1');
});

// API AI Companion
$routes->post('api/ai/chat', 'AiController::chat', ['filter' => 'jwt']);

// API Chat
$routes->post('api/chat/upload', 'Meeting\ChatController::apiUpload', ['filter' => 'jwt']);
$routes->group('api/chat', ['filter' => 'jwt'], function ($routes) {
    $routes->post('(:segment)', 'Meeting\ChatController::apiStore/$1');
    $routes->get('(:segment)', 'Meeting\ChatController::apiList/$1');
});
