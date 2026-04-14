<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default route
$routes->get('/', 'AuthController::login', ['as' => 'login']);

// Auth routes
$routes->group('auth', function($routes) {
    $routes->get('login', 'AuthController::login', ['as' => 'login']);
    $routes->post('login', 'AuthController::attemptLogin');
    $routes->get('logout', 'AuthController::logout', ['as' => 'logout']);
});

// Dashboard routes (require login)
$routes->group('dashboard', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'DashboardController::index', ['as' => 'dashboard']);
});

// Queue routes
$routes->group('queue', ['filter' => 'auth'], function($routes) {
    // Input antrian (perawat only)
    $routes->get('input', 'QueueController::input', ['filter' => 'role:perawat']);
    $routes->post('add', 'QueueController::add', ['filter' => 'role:perawat']);
    
    // Panggil antrian (perawat & dokter)
    $routes->get('call', 'QueueController::call', ['filter' => 'role:perawat,dokter']);
    $routes->post('call-next', 'QueueController::callNext', ['filter' => 'role:perawat,dokter']);
    $routes->post('call-specific', 'QueueController::callSpecific', ['filter' => 'role:perawat,dokter']);
    $routes->post('finish/(:num)', 'QueueController::finish/$1', ['filter' => 'role:perawat,dokter']);
    $routes->post('skip/(:num)', 'QueueController::skip/$1', ['filter' => 'role:perawat,dokter']);
    $routes->post('update-nama', 'QueueController::updateNama', ['filter' => 'role:perawat,dokter']);
    $routes->post('warn', 'QueueController::warn', ['filter' => 'role:perawat,dokter']);
    $routes->post('recallQueue', 'QueueController::recallQueue', ['filter' => 'role:perawat,dokter']);

    // Generate actions
    $routes->get('generate', 'QueueController::generate');
    $routes->post('process-generate', 'QueueController::processGenerate');
    $routes->post('reset-generate', 'QueueController::resetGenerate');
    $routes->get('check-status', 'QueueController::checkStatus');
});

// Display route (public)
$routes->get('display', 'DisplayController::index'); // Default lantai 1
$routes->get('display/(:segment)', 'DisplayController::index/$1'); // Dynamic lantai
$routes->get('display/data/(:segment)', 'DisplayController::getData/$1'); // Legacy API

// Admin routes
$routes->group('admin', ['filter' => 'auth', 'filter' => 'role:admin'], function($routes) {
    // Users management
    $routes->get('users', 'Admin\UserController::index', ['as' => 'admin.users']);
    $routes->get('users/create', 'Admin\UserController::create');
    $routes->post('users/store', 'Admin\UserController::store');
    $routes->get('users/edit/(:num)', 'Admin\UserController::edit/$1');
    $routes->post('users/update/(:num)', 'Admin\UserController::update/$1');
    $routes->post('users/delete/(:num)', 'Admin\UserController::delete/$1');
    
    // Services management
    $routes->get('services', 'Admin\ServiceController::index', ['as' => 'admin.services']);
    $routes->get('services/create', 'Admin\ServiceController::create');
    $routes->post('services/store', 'Admin\ServiceController::store');
    $routes->get('services/edit/(:num)', 'Admin\ServiceController::edit/$1');
    $routes->post('services/update/(:num)', 'Admin\ServiceController::update/$1');
    $routes->post('services/delete/(:num)', 'Admin\ServiceController::delete/$1');
    
    // Laporan
    $routes->get('laporan', 'Admin\LaporanController::index', ['as' => 'admin.laporan']);
    $routes->get('laporan/export', 'Admin\LaporanController::export');
});

// API routes for AJAX
$routes->group('api', function($routes) {
    $routes->get('queue/current/(:segment)', 'Api\QueueApiController::getCurrent/$1');
    $routes->get('queue/waiting/(:segment)', 'Api\QueueApiController::getWaiting/$1');
    $routes->get('queue/statistics', 'Api\QueueApiController::getStatistics');
    $routes->get('queue/list', 'Api\QueueApiController::getList');
    $routes->get('queue/by-services/(:segment)', 'DisplayController::getByServices/$1');
});