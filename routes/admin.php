<?php

declare(strict_types=1);

use App\Http\Routing\Router;

/**
 * Admin Routes
 * 
 * Define all admin panel routes here
 * These routes will be prefixed with /admin and protected by admin middleware
 */

/** @var Router $router */
$router = app(Router::class);

// Admin Dashboard
// $router->get('/', 'App\Http\Controllers\Admin\DashboardController::index')->name('admin.dashboard');

// User Management
// $router->get('/users', 'App\Http\Controllers\Admin\UserController::index')->name('admin.users.index');
// $router->get('/users/{id}/edit', 'App\Http\Controllers\Admin\UserController::edit')->name('admin.users.edit');
// $router->put('/users/{id}', 'App\Http\Controllers\Admin\UserController::update')->name('admin.users.update');
// $router->delete('/users/{id}', 'App\Http\Controllers\Admin\UserController::destroy')->name('admin.users.destroy');

// Torrent Management
// $router->get('/torrents', 'App\Http\Controllers\Admin\TorrentController::index')->name('admin.torrents.index');
// $router->get('/torrents/{id}', 'App\Http\Controllers\Admin\TorrentController::show')->name('admin.torrents.show');
// $router->delete('/torrents/{id}', 'App\Http\Controllers\Admin\TorrentController::destroy')->name('admin.torrents.destroy');

// Forum Management
// $router->resource('forums', 'App\Http\Controllers\Admin\ForumController');

// Settings
// $router->get('/settings', 'App\Http\Controllers\Admin\SettingsController::index')->name('admin.settings.index');
// $router->put('/settings', 'App\Http\Controllers\Admin\SettingsController::update')->name('admin.settings.update');

// Logs
// $router->get('/logs', 'App\Http\Controllers\Admin\LogController::index')->name('admin.logs.index');
// $router->get('/logs/{type}', 'App\Http\Controllers\Admin\LogController::show')->name('admin.logs.show');

// Reports
// $router->get('/reports', 'App\Http\Controllers\Admin\ReportController::index')->name('admin.reports.index');
// $router->get('/reports/{id}', 'App\Http\Controllers\Admin\ReportController::show')->name('admin.reports.show');
// $router->put('/reports/{id}/resolve', 'App\Http\Controllers\Admin\ReportController::resolve')->name('admin.reports.resolve');

// Bans
// $router->get('/bans', 'App\Http\Controllers\Admin\BanController::index')->name('admin.bans.index');
// $router->post('/bans', 'App\Http\Controllers\Admin\BanController::store')->name('admin.bans.store');
// $router->delete('/bans/{id}', 'App\Http\Controllers\Admin\BanController::destroy')->name('admin.bans.destroy');

// Statistics
// $router->get('/stats', 'App\Http\Controllers\Admin\StatsController::index')->name('admin.stats.index');
// $router->get('/stats/users', 'App\Http\Controllers\Admin\StatsController::users')->name('admin.stats.users');
// $router->get('/stats/torrents', 'App\Http\Controllers\Admin\StatsController::torrents')->name('admin.stats.torrents');
// $router->get('/stats/tracker', 'App\Http\Controllers\Admin\StatsController::tracker')->name('admin.stats.tracker');