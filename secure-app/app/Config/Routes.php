<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'Home::index');

// Auth Interface Handling
$routes->get('/login', 'AuthController::login');
$routes->post('/login/authenticate', 'AuthController::authenticate');
$routes->get('/register', 'AuthController::register');
$routes->post('/register/store', 'AuthController::store');
$routes->get('/forgot-password', 'AuthController::forgotPassword');
$routes->post('/forgot-password/send', 'AuthController::sendResetLink');
$routes->get('/reset-password/(:any)', 'AuthController::resetPassword/$1');
$routes->post('/reset-password/update', 'AuthController::updatePassword');
$routes->get('/logout', 'AuthController::logout');

// Debug Email Route (Restricted to Super Admin)
$routes->get('/test-email-sim', 'AuthController::testEmail', ['filter' => 'pageAuth:super_admin']);

// Dashboard Page (Requires standard authentication)
$routes->get('/dashboard', 'DashboardController::index', ['filter' => 'pageAuth']);

// Desert Storm Planner Routes (Requires standard authentication)
$routes->get('/ds_planner', 'DesertStormController::index', ['filter' => 'pageAuth']);
$routes->post('/ds_planner/save', 'DesertStormController::saveAssignments', ['filter' => 'pageAuth:admin']);
$routes->get('/ds_planner_mobile', 'DesertStormController::mobile', ['filter' => 'pageAuth']);


// Canyon Storm Planner Routes (Requires standard authentication)
$routes->get('/cs_planner', 'CanyonStormController::index', ['filter' => 'pageAuth']);
$routes->post('/cs_planner/save', 'CanyonStormController::saveAssignments', ['filter' => 'pageAuth:admin']);

// Polar Express (Gold Train) Routes
$routes->get('/gold-train', 'GoldTrainController::index', ['filter' => 'pageAuth']);
$routes->get('/gold-train/history', 'GoldTrainController::history', ['filter' => 'pageAuth']);
$routes->post('/gold-train/update-status', 'GoldTrainController::updateStatus', ['filter' => 'pageAuth:admin']);
$routes->post('/gold-train/generate-cycle', 'GoldTrainController::generateCycle', ['filter' => 'pageAuth:admin']);
$routes->post('/gold-train/save-cycle', 'GoldTrainController::saveCycle', ['filter' => 'pageAuth:admin']);
$routes->post('/gold-train/load-cycle', 'GoldTrainController::loadCycle', ['filter' => 'pageAuth:admin']);
$routes->post('/gold-train/delete-cycle', 'GoldTrainController::deleteCycle', ['filter' => 'pageAuth:admin']);
$routes->post('/gold-train/reset', 'GoldTrainController::resetRoster', ['filter' => 'pageAuth:admin']);
$routes->post('/gold-train/swap', 'GoldTrainController::swapSchedule', ['filter' => 'pageAuth:admin']);
$routes->post('/gold-train/shift-down', 'GoldTrainController::shiftDown', ['filter' => 'pageAuth:admin']); // NEW SHIFT DOWN ROUTE

// User Profile & Squad Stats (Requires standard authentication)
$routes->group('profile', ['filter' => 'pageAuth'], static function ($routes) {
    $routes->get('squad', 'ProfileController::squad');
    $routes->post('squad/update', 'ProfileController::updateSquad');
});

// Member Growth Leaderboard & Search (Public for approved users)
$routes->get('/growth', 'DashboardController::growthAnalytics', ['filter' => 'pageAuth']);

// Event Tracking for Regular Users
$routes->get('events/log', 'EventController::userLog', ['filter' => 'pageAuth']);

// Admin Pages (Strictly restricted to the 'admin' role)
$routes->group('admin', ['filter' => 'pageAuth:admin'], static function ($routes) {
    $routes->get('settings', 'AdminController::settings');
    $routes->get('approvals', 'AdminController::approvals');
    $routes->post('approvals/update', 'AdminController::updateApprovalStatus');
    
    // User Management
    $routes->get('users', 'AdminController::users');
    $routes->get('users/add', 'AdminController::addMemberView');
    $routes->post('users/add', 'AdminController::addUser');
    $routes->get('users/edit/(:num)', 'AdminController::editUser/$1');
    $routes->post('users/update', 'AdminController::updateUser');
    $routes->post('users/import', 'AdminController::importUsers');
    $routes->post('users/delete', 'AdminController::deleteUser');
    
    // Admin Squad Powers Management
    $routes->get('users/squad_powers', 'AdminController::squadPowers');
    $routes->get('users/squad_powers/growth', 'AdminController::squadGrowth');
    $routes->post('users/squad_powers/update', 'AdminController::updateSquadPower');
    $routes->post('users/squad_powers/bulk_update', 'AdminController::bulkUpdateSquadPowers');
    $routes->get('users/squad_powers/export', 'AdminController::exportSquadPowers');
    $routes->post('users/squad_powers/import', 'AdminController::importSquadPowers');
    
    // Event Management
    $routes->get('events', 'EventController::adminLog');
    $routes->get('events/select', 'EventController::selectMatch');
    $routes->get('events/edit', 'EventController::editMatch');
    $routes->post('events/log_participation', 'EventController::logParticipation');
});

// Super Admin Pages (Strictly restricted to 'super_admin')
$routes->group('super_admin', ['filter' => 'pageAuth:super_admin'], static function ($routes) {
    $routes->get('access-logs', 'SuperAdminController::accessLogs');
    $routes->post('access-logs/snapshot', 'SuperAdminController::generateLogSnapshot');
});
