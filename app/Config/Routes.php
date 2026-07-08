<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// CORS preflight — browsers send an OPTIONS request before any cross-origin
// POST/PUT/DELETE call. None of the routes below register the OPTIONS verb,
// so without this catch-all, preflight requests 404 before the `cors`
// filter (attached per-route below) ever gets a chance to run.
$routes->options('(:any)', static function () {
    return service('response');
}, ['filter' => 'cors']);

// Home
$routes->get('/', 'Home::index');
$routes->get('pricing', 'Home::pricing');
$routes->get('features', 'Home::features');
$routes->get('terms', 'Home::terms');
$routes->get('privacy', 'Home::privacy');
$routes->get('contact', 'Home::contact');
$routes->post('contact', 'Home::contactSubmit');
$routes->get('support', 'Home::support');
$routes->get('download', 'Home::download');

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

// API Auth — dedicated Api\AuthController (used by Flutter / native clients)
$routes->group('api/auth', ['filter' => 'cors'], function ($routes) {
    $routes->post('register', 'Api\AuthController::register');
    $routes->post('login',    'Api\AuthController::login');
    $routes->post('logout',   'Api\AuthController::logout',  ['filter' => 'jwt']);
    $routes->get('me',        'Api\AuthController::me',      ['filter' => 'jwt']);
    // Password-reset flows (web deep-link token; web controller handles email send)
    $routes->post('forgot-password',              'Auth\AuthController::apiForgotPassword');
    $routes->post('reset-password/(:alphanum)',   'Auth\AuthController::apiResetPassword/$1');
    $routes->get('verify',                        'Auth\AuthController::verify');
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

// Meeting join & resolve — jwt is optional here since a first-time guest (no
// account, no prior token) must be able to resolve a numeric meeting ID and
// reach Api\MeetingController::join()'s own guest-join branch, which is what
// establishes their identity/JWT in the first place. The controller enforces
// its own password/display-name checks for guests.
$routes->post('api/meetings/(:segment)/join',    'Api\MeetingController::join/$1', ['filter' => ['cors', 'jwtOptional']]);
$routes->get('api/meetings/resolve/(:segment)',  'Api\MeetingController::resolve/$1', ['filter' => ['cors', 'jwtOptional']]);

// API Meetings — dedicated Api\MeetingController (used by Flutter / native clients)
$routes->group('api/meetings', ['filter' => ['cors', 'jwt']], function ($routes) {
    $routes->get('/',                   'Api\MeetingController::index');
    $routes->post('/',                  'Api\MeetingController::create');
    $routes->get('(:segment)',          'Api\MeetingController::show/$1');
    $routes->put('(:segment)',          'Api\MeetingController::update/$1');
    $routes->delete('(:segment)',       'Api\MeetingController::destroy/$1');
    $routes->post('(:segment)/start',   'Api\MeetingController::start/$1');
    $routes->post('(:segment)/end',     'Api\MeetingController::end/$1');
    $routes->get('(:segment)/stats',    'Api\MeetingController::stats/$1');
    $routes->get('(:segment)/files',    'Meeting\FileController::apiFiles/$1');
    $routes->post('(:segment)/leave',   'Meeting\ParticipantController::apiLeave/$1');
    // Participant & room management (existing controllers)
    $routes->get('(:segment)/participants',          'Meeting\ParticipantController::apiList/$1');
    $routes->post('(:segment)/admit/(:num)',         'Meeting\ParticipantController::apiAdmit/$1/$2');
    $routes->post('(:segment)/remove/(:num)',        'Meeting\ParticipantController::apiRemove/$1/$2');
    $routes->post('(:segment)/mute/(:num)',          'Meeting\ParticipantController::apiMute/$1/$2');
    $routes->post('(:segment)/invite',               'Meeting\InvitationController::apiSend/$1');
    $routes->get('(:segment)/recordings',            'Meeting\RecordingController::apiList/$1');
    $routes->post('(:segment)/recordings',           'Meeting\RecordingController::apiCreate/$1');
    $routes->patch('(:segment)/recordings/(:num)',   'Meeting\RecordingController::apiStop/$1/$2');
    $routes->post('(:segment)/recordings/stop',      'Meeting\RecordingController::apiStopLatest/$1');
});

// Flutter / native-app SFU proxy — scoped to a meeting token, JWT-protected
// Mirrors /sfu-proxy/* but lives under /api/meetings/:token/sfu-proxy/* so
// the Flutter client can use a single base URL pattern.
$routes->group('api/meetings/(:segment)/sfu-proxy', ['filter' => ['cors', 'jwt']], function ($routes) {
    $routes->post('sessions/new',                   'Api\SfuProxyController::newSession/$1');
    $routes->post('sessions/(:segment)/tracks/new', 'Api\SfuProxyController::newTracks/$1/$2');
    $routes->put('sessions/(:segment)/renegotiate', 'Api\SfuProxyController::renegotiate/$1/$2');
    $routes->put('sessions/(:segment)/tracks/close','Api\SfuProxyController::closeTracks/$1/$2');
});

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
$routes->group('api/workspace', ['filter' => ['cors', 'jwt']], function ($routes) {
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
$routes->post('api/ai/chat', 'AiController::chat', ['filter' => ['cors', 'jwt']]);

// API Chat
$routes->post('api/chat/upload', 'Meeting\ChatController::apiUpload', ['filter' => ['cors', 'jwt']]);
$routes->group('api/chat', ['filter' => ['cors', 'jwt']], function ($routes) {
    $routes->post('(:segment)', 'Meeting\ChatController::apiStore/$1');
    $routes->get('(:segment)', 'Meeting\ChatController::apiList/$1');
});
