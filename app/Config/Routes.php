<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ── Public frontend (no auth) ──
$routes->get('/',                    '\App\Modules\Frontend\Controllers\SiteController::home');
$routes->get('services',             '\App\Modules\Frontend\Controllers\SiteController::services');
$routes->get('book',                 '\App\Modules\Frontend\Controllers\BookingController::index');
$routes->post('book',                '\App\Modules\Frontend\Controllers\BookingController::store');
$routes->get('book/confirm/(:any)',  '\App\Modules\Frontend\Controllers\BookingController::confirm/$1');
$routes->get('locale/switch/(:any)', '\App\Modules\Frontend\Controllers\LocaleController::switch/$1');

// Static pages + contact
$routes->get('about',    '\App\Modules\Frontend\Controllers\PagesController::about');
$routes->get('team',     '\App\Modules\Frontend\Controllers\PagesController::team');
$routes->get('contact',  '\App\Modules\Frontend\Controllers\PagesController::contact');
$routes->post('contact', '\App\Modules\Frontend\Controllers\PagesController::contactSubmit');
$routes->get('terms',    '\App\Modules\Frontend\Controllers\PagesController::terms');
$routes->get('privacy',  '\App\Modules\Frontend\Controllers\PagesController::privacy');
$routes->get('refund',   '\App\Modules\Frontend\Controllers\PagesController::refund');

// Public review submission
$routes->get('review',          '\App\Modules\Frontend\Controllers\PublicReviewController::form');
$routes->get('review/(:any)',   '\App\Modules\Frontend\Controllers\PublicReviewController::form/$1');
$routes->post('review',         '\App\Modules\Frontend\Controllers\PublicReviewController::submit');

// Customer self-service portal
$routes->get('portal',                  '\App\Modules\CustomerPortal\Controllers\PortalController::index');
$routes->post('portal/request-otp',     '\App\Modules\CustomerPortal\Controllers\PortalController::requestOtp');
$routes->get('portal/verify',           '\App\Modules\CustomerPortal\Controllers\PortalController::verifyForm');
$routes->post('portal/verify',          '\App\Modules\CustomerPortal\Controllers\PortalController::verify');
$routes->get('portal/logout',           '\App\Modules\CustomerPortal\Controllers\PortalController::logout');
$routes->group('portal', ['filter' => 'customerauth'], static function ($routes) {
    $routes->get('dashboard',                '\App\Modules\CustomerPortal\Controllers\PortalController::dashboard');
    $routes->get('invoice/(:num)',           '\App\Modules\CustomerPortal\Controllers\PortalController::invoice/$1');
    $routes->get('availability',             '\App\Modules\CustomerPortal\Controllers\PortalController::availability');
    $routes->post('booking/(:num)/cancel',     '\App\Modules\CustomerPortal\Controllers\PortalController::cancelBooking/$1');
    $routes->get('booking/(:num)/reschedule',  '\App\Modules\CustomerPortal\Controllers\PortalController::rescheduleForm/$1');
    $routes->post('booking/(:num)/reschedule', '\App\Modules\CustomerPortal\Controllers\PortalController::reschedule/$1');
});

// ── Auth (guest only) ──
$routes->group('', ['namespace' => 'App\Modules\Auth\Controllers', 'filter' => 'guest'], static function ($routes) {
    $routes->get('login', 'LoginController::index');
    $routes->post('login', 'LoginController::attempt');
    $routes->get('forgot',  'ForgotController::index');
    $routes->post('forgot', 'ForgotController::send');
    $routes->get('reset/(:any)', 'ForgotController::reset/$1');
    $routes->post('reset',  'ForgotController::doReset');
});

$routes->get('logout', '\App\Modules\Auth\Controllers\LoginController::logout');

// ── Admin (auth required) ──
$routes->group('admin', ['filter' => 'auth'], static function ($routes) {

    // Dashboard
    $routes->get('dashboard', '\App\Modules\Dashboard\Controllers\DashboardController::index');

    // My profile (self-service for the logged-in user)
    $routes->get('profile',           '\App\Modules\Auth\Controllers\ProfileController::index');
    $routes->post('profile',          '\App\Modules\Auth\Controllers\ProfileController::update');
    $routes->post('profile/password', '\App\Modules\Auth\Controllers\ProfileController::changePassword');

    // POS
    $routes->group('pos', ['namespace' => 'App\Modules\POS\Controllers'], static function ($routes) {
        $routes->get('/',                  'POSController::index');
        $routes->get('availability',       'POSController::availability');
        $routes->post('book',              'POSController::quickBook');
        $routes->post('bill',              'POSController::quickBill');
        $routes->post('customer/quick',    'POSController::quickCustomer');
    });

    // Customers
    $routes->group('customers', ['namespace' => 'App\Modules\Customers\Controllers'], static function ($routes) {
        $routes->get('/',          'CustomersController::index');
        $routes->get('create',     'CustomersController::create', ['filter' => 'perm:customers.create']);
        $routes->post('/',         'CustomersController::store',  ['filter' => 'perm:customers.create']);
        $routes->get('(:num)',     'CustomersController::show/$1');
        $routes->get('(:num)/edit','CustomersController::edit/$1', ['filter' => 'perm:customers.edit']);
        $routes->put('(:num)',     'CustomersController::update/$1', ['filter' => 'perm:customers.edit']);
        $routes->delete('(:num)',  'CustomersController::destroy/$1', ['filter' => 'perm:customers.delete']);
        $routes->post('(:num)/block', 'CustomersController::toggleBlock/$1', ['filter' => 'perm:customers.edit']);

        // Customer autocomplete (JSON) for live search suggestions
        $routes->get('suggest', 'CustomersController::suggest');

        // Service history & records sub-resources
        $routes->post('(:num)/notes',                   'CustomersController::addNote/$1');
        $routes->delete('(:num)/notes/(:num)',          'CustomersController::deleteNote/$1/$2');
        $routes->post('(:num)/allergies',               'CustomersController::addAllergy/$1');
        $routes->delete('(:num)/allergies/(:num)',      'CustomersController::deleteAllergy/$1/$2');
        $routes->post('(:num)/preferences',             'CustomersController::savePreferences/$1');
        $routes->delete('(:num)/preferences/(:num)',    'CustomersController::deletePreference/$1/$2');
        $routes->put('(:num)/history/(:num)',           'CustomersController::updateHistory/$1/$2');
        $routes->post('(:num)/files',                   'CustomersController::uploadFile/$1');
        $routes->delete('(:num)/files/(:num)',          'CustomersController::deleteFile/$1/$2');
    });

    // Services
    $routes->group('services', ['namespace' => 'App\Modules\Services\Controllers'], static function ($routes) {
        $routes->get('/',           'ServicesController::index');
        $routes->get('create',      'ServicesController::create',     ['filter' => 'perm:services.create']);
        $routes->post('/',          'ServicesController::store',      ['filter' => 'perm:services.create']);
        $routes->get('(:num)/edit', 'ServicesController::edit/$1',    ['filter' => 'perm:services.edit']);
        $routes->put('(:num)',      'ServicesController::update/$1',  ['filter' => 'perm:services.edit']);
        $routes->delete('(:num)',   'ServicesController::destroy/$1', ['filter' => 'perm:services.delete']);
    });
    $routes->group('service-categories', ['namespace' => 'App\Modules\Services\Controllers'], static function ($routes) {
        $routes->get('/',         'ServiceCategoriesController::index');
        $routes->post('/',        'ServiceCategoriesController::store',      ['filter' => 'perm:services.create']);
        $routes->delete('(:num)', 'ServiceCategoriesController::destroy/$1', ['filter' => 'perm:services.delete']);
    });
    $routes->group('service-types', ['namespace' => 'App\Modules\Services\Controllers'], static function ($routes) {
        $routes->get('/',           'ServiceTypesController::index');
        $routes->post('/',          'ServiceTypesController::store');
        $routes->put('(:num)',      'ServiceTypesController::update/$1');
        $routes->delete('(:num)',   'ServiceTypesController::destroy/$1');
    });

    // Staff
    $routes->group('staff', ['namespace' => 'App\Modules\Staff\Controllers'], static function ($routes) {
        $routes->get('/',               'StaffController::index');
        $routes->get('create',          'StaffController::create',     ['filter' => 'perm:staff.create']);
        $routes->post('/',              'StaffController::store',      ['filter' => 'perm:staff.create']);
        $routes->get('(:num)/edit',     'StaffController::edit/$1',    ['filter' => 'perm:staff.edit']);
        $routes->get('(:num)/calendar', 'StaffController::calendar/$1');
        $routes->get('(:num)/revenue',  'StaffController::revenue/$1');
        $routes->get('(:num)/payouts',  'StaffController::payouts/$1');
        $routes->put('(:num)',          'StaffController::update/$1',  ['filter' => 'perm:staff.edit']);
        $routes->delete('(:num)',       'StaffController::destroy/$1', ['filter' => 'perm:staff.delete']);
        $routes->post('(:num)/time-off',          'StaffController::addTimeOff/$1');
        $routes->delete('(:num)/time-off/(:num)', 'StaffController::removeTimeOff/$1/$2');
        $routes->post('(:num)/date-window',          'StaffController::addDateWindow/$1');
        $routes->delete('(:num)/date-window/(:num)', 'StaffController::removeDateWindow/$1/$2');
        $routes->post('(:num)/date-window/reset',    'StaffController::resetDateWindows/$1');
        // Payouts: generate, upload slip, notify stylist, delete
        $routes->post('(:num)/payouts',                'StaffController::generatePayout/$1', ['filter' => 'perm:staff.edit']);
        $routes->post('(:num)/payouts/(:num)/slip',    'StaffController::uploadSlip/$1/$2',  ['filter' => 'perm:staff.edit']);
        $routes->post('(:num)/payouts/(:num)/notify',  'StaffController::notifyPayout/$1/$2',['filter' => 'perm:staff.edit']);
        $routes->delete('(:num)/payouts/(:num)',       'StaffController::deletePayout/$1/$2',['filter' => 'perm:staff.edit']);
    });

    // Appointments
    $routes->group('appointments', ['namespace' => 'App\Modules\Appointments\Controllers'], static function ($routes) {
        $routes->get('/', 'AppointmentsController::index');
        $routes->get('cancellations', 'AppointmentsController::cancellations');
        $routes->get('create', 'AppointmentsController::create');
        $routes->post('/', 'AppointmentsController::store');
        $routes->get('(:num)', 'AppointmentsController::show/$1');
        $routes->get('(:num)/edit', 'AppointmentsController::edit/$1');
        $routes->put('(:num)', 'AppointmentsController::update/$1');
        $routes->post('(:num)/status', 'AppointmentsController::setStatus/$1');
        $routes->post('(:num)/cancel', 'AppointmentsController::cancel/$1');
        $routes->delete('(:num)', 'AppointmentsController::destroy/$1');
    });

    // Billing
    $routes->group('billing/invoices', ['namespace' => 'App\Modules\Billing\Controllers'], static function ($routes) {
        $routes->get('/',                              'InvoicesController::index');
        $routes->get('create',                         'InvoicesController::create',                ['filter' => 'perm:invoices.create']);
        $routes->post('/',                             'InvoicesController::store',                 ['filter' => 'perm:invoices.create']);
        $routes->get('create-from-appointment/(:num)', 'InvoicesController::createFromAppointment/$1', ['filter' => 'perm:invoices.create']);
        $routes->get('(:num)',                         'InvoicesController::show/$1');
        $routes->get('(:num)/print',                   'InvoicesController::print/$1');
        $routes->get('(:num)/pdf',                     'InvoicesController::pdf/$1');
        $routes->post('(:num)/email',                  'InvoicesController::email/$1');
        $routes->post('(:num)/attribution',            'InvoicesController::updateAttribution/$1', ['filter' => 'perm:invoices.create']);
        $routes->post('(:num)/payments',               'InvoicesController::recordPayment/$1', ['filter' => 'perm:payments.record']);
        $routes->post('(:num)/redeem',                 'InvoicesController::redeem/$1',        ['filter' => 'perm:payments.record']);
    });
    $routes->get('billing/payments', '\App\Modules\Billing\Controllers\PaymentsController::index');
    $routes->get('billing/payouts',  '\App\Modules\Billing\Controllers\PayoutsController::index');

    // Branches
    $routes->group('branches', ['namespace' => 'App\Modules\Branches\Controllers'], static function ($routes) {
        $routes->get('/', 'BranchesController::index');
        $routes->post('/', 'BranchesController::store');
        $routes->delete('(:num)', 'BranchesController::destroy/$1');
    });

    // Notifications + activity log
    $routes->group('notifications', ['namespace' => 'App\Modules\System\Controllers'], static function ($routes) {
        $routes->get('/',                   'NotificationsController::index');
        $routes->get('feed',                'NotificationsController::topbarFeed');
        $routes->post('mark-all-read',      'NotificationsController::markAllRead');
        $routes->post('(:num)/read',        'NotificationsController::markRead/$1');
        $routes->delete('(:num)',           'NotificationsController::destroy/$1');
    });
    $routes->get('activity-log',   '\App\Modules\System\Controllers\ActivityLogController::index');

    // Reviews
    $routes->group('reviews', ['namespace' => 'App\Modules\Reviews\Controllers'], static function ($routes) {
        $routes->get('/',                 'ReviewsController::index');
        $routes->get('create',            'ReviewsController::create');
        $routes->post('/',                'ReviewsController::store');
        $routes->post('(:num)/approve',   'ReviewsController::approve/$1');
        $routes->post('(:num)/reject',    'ReviewsController::reject/$1');
        $routes->post('(:num)/featured',  'ReviewsController::toggleFeatured/$1');
        $routes->delete('(:num)',         'ReviewsController::destroy/$1');
        $routes->post('import-google',    'ReviewsController::importGoogle');
    });

    // Reports
    $routes->group('reports', ['namespace' => 'App\Modules\Reports\Controllers'], static function ($routes) {
        $routes->get('/',             'ReportsController::index');
        $routes->get('overview',      'ReportsController::overview');
        $routes->get('sales',         'ReportsController::sales');
        $routes->get('services',      'ReportsController::services');
        $routes->get('staff',         'ReportsController::staff');
        $routes->get('sales/csv',     'ReportsController::csvSales');
        $routes->get('services/csv',  'ReportsController::csvServices');
        $routes->get('staff/csv',     'ReportsController::csvStaff');
        $routes->get('staff/(:num)/payout', 'ReportsController::payout/$1');
    });

    // Settings
    $routes->group('settings', ['namespace' => 'App\Modules\Settings\Controllers'], static function ($routes) {
        $routes->get('/',          'SettingsController::index');
        $routes->get('general',    'SettingsController::general');
        $routes->post('general',   'SettingsController::saveGeneral');
        $routes->get('appointments',  'SettingsController::appointments');
        $routes->post('appointments', 'SettingsController::saveAppointments');
        $routes->post('github-save',   'SettingsController::saveGithubSettings');
        $routes->get('github-check',   'SettingsController::checkGithubUpdates');
        $routes->post('github-update', 'SettingsController::applyGithubUpdate');
        $routes->get('business',   'SettingsController::business');
        $routes->post('business',  'SettingsController::saveBusiness');
        $routes->get('smtp',       'SettingsController::smtp');
        $routes->post('smtp',      'SettingsController::saveSmtp');
        $routes->post('smtp/test', 'SettingsController::testSmtp');
        $routes->get('cron',       'SettingsController::cron');
        $routes->post('cron',      'SettingsController::saveCron');
        $routes->get('updates',    'SettingsController::updates');
        $routes->get('loyalty',    'SettingsController::loyalty');
        $routes->post('loyalty',   'SettingsController::saveLoyalty');
        $routes->get('frontend',   'SettingsController::frontend');
        $routes->post('frontend',  'SettingsController::saveFrontend');
        $routes->get('pages',          'SettingsController::pages');
        $routes->post('pages',         'SettingsController::savePages');
        $routes->get('seo',            'SettingsController::seo');
        $routes->post('seo',           'SettingsController::saveSeo');
        $routes->get('integrations',   'SettingsController::integrations');
        $routes->post('integrations',  'SettingsController::saveIntegrations');
        $routes->get('gateways',       'SettingsController::gateways');
        $routes->post('gateways',      'SettingsController::saveGateways');

        // Users / Roles / Permissions (gated)
        $routes->get('users',                'SettingsController::users',   ['filter' => 'perm:users.manage']);
        $routes->post('users',               'UsersController::store',      ['filter' => 'perm:users.manage']);
        $routes->put('users/(:num)',         'UsersController::update/$1',  ['filter' => 'perm:users.manage']);
        $routes->delete('users/(:num)',      'UsersController::destroy/$1', ['filter' => 'perm:users.manage']);

        $routes->get('roles',                'SettingsController::roles',     ['filter' => 'perm:roles.manage']);
        $routes->post('roles',               'RolesController::store',        ['filter' => 'perm:roles.manage']);
        $routes->put('roles/(:num)',         'RolesController::update/$1',    ['filter' => 'perm:roles.manage']);
        $routes->delete('roles/(:num)',      'RolesController::destroy/$1',   ['filter' => 'perm:roles.manage']);

        $routes->get('permissions',          'SettingsController::permissions', ['filter' => 'perm:roles.manage']);
        $routes->post('permissions',         'PermissionsController::save',     ['filter' => 'perm:roles.manage']);
    });
});

// ── REST API ──
// CORS + apiauth filters are applied via $filters URI-pattern bindings in Filters.php (not here).
// Route ordering: literal paths MUST be before (:num) segments — CI4 first-match-wins.
$routes->group('api', ['namespace' => 'App\Modules\Api\Controllers'], static function ($routes) {

    // CORS preflight catch-all — CI4 filter URI patterns only apply to matched routes,
    // so OPTIONS must have an explicit route to trigger the CORS filter.
    $routes->options('(:any)', static function () {
        return service('response')
            ->setStatusCode(204)
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept, X-Requested-With')
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->setHeader('Access-Control-Max-Age', '7200');
    });

    // Auth (login is whitelisted in ApiAuthFilter — no token required)
    $routes->post('auth/login',      'AuthController::login');
    $routes->post('auth/logout',     'AuthController::logout');
    $routes->post('auth/logout-all', 'AuthController::logoutAll');

    // Dashboard
    $routes->get('dashboard', 'DashboardController::index');

    // Profile
    $routes->get('me',   'ProfileController::show');
    $routes->patch('me', 'ProfileController::update');

    // Appointments — availability BEFORE (:num) to avoid routing conflict
    $routes->get('appointments/availability',    'AppointmentsController::availability');
    $routes->get('appointments',                 'AppointmentsController::index');
    $routes->post('appointments',                'AppointmentsController::store');
    $routes->get('appointments/(:num)',              'AppointmentsController::show/$1');
    $routes->patch('appointments/(:num)/status',     'AppointmentsController::setStatus/$1');
    $routes->patch('appointments/(:num)/reschedule', 'AppointmentsController::reschedule/$1');
    $routes->post('appointments/(:num)/invoice',     'AppointmentsController::convertToInvoice/$1');
    $routes->get('appointments/(:num)/activity',     'AppointmentsController::activity/$1');

    // Customers
    $routes->get('customers',        'CustomersController::index');
    $routes->post('customers',       'CustomersController::store');
    $routes->get('customers/(:num)', 'CustomersController::show/$1');
    $routes->post('customers/(:num)/notes', 'CustomersController::addNote/$1');
    $routes->post('customers/(:num)/files', 'CustomersController::uploadFile/$1');

    // Invoices
    $routes->get('invoices',                          'InvoicesController::index');
    $routes->post('invoices',                         'InvoicesController::store');
    $routes->get('invoices/(:num)',                   'InvoicesController::show/$1');
    $routes->get('invoices/(:num)/pdf',               'InvoicesController::pdf/$1');
    $routes->post('invoices/(:num)/email',            'InvoicesController::email/$1');
    $routes->patch('invoices/(:num)/status',          'InvoicesController::setStatus/$1');
    $routes->post('invoices/(:num)/payments',         'InvoicesController::recordPayment/$1');
    $routes->post('invoices/(:num)/attribution',      'InvoicesController::updateAttribution/$1');

    // Staff — literal paths before (:num)
    $routes->get('staff',                    'StaffController::index');
    $routes->get('staff/(:num)',             'StaffController::show/$1');
    $routes->get('staff/(:num)/schedule',   'StaffController::schedule/$1');
    $routes->get('staff/(:num)/payouts',    'StaffController::payouts/$1');
    $routes->get('staff/(:num)/revenue',    'StaffController::revenue/$1');

    // Notifications — read-all BEFORE (:num)/read
    $routes->get('notifications',              'NotificationsController::index');
    $routes->patch('notifications/read-all',   'NotificationsController::markAllRead');
    $routes->patch('notifications/(:num)/read','NotificationsController::markRead/$1');

    // Services
    $routes->get('services', 'ServicesController::index');
});
