<?php

declare(strict_types=1);

use App\Http\Routing\Router;

/**
 * API Routes
 * 
 * Define all API routes here
 * These routes will be prefixed with /api
 */

/** @var Router $router */
$router = app(Router::class);

// API routes demonstrating Laravel-style functionality

// User API (showcasing illuminate/http and illuminate/validation)
$router->get('/users', 'App\Http\Controllers\Api\UserController::index')->name('api.users.index');
$router->get('/users/{id}', 'App\Http\Controllers\Api\UserController::show')->name('api.users.show');
$router->post('/users/register', 'App\Http\Controllers\Api\UserController::register')->name('api.users.register');

// Future API routes:

// Authentication
// $router->post('/auth/login', 'App\Http\Controllers\Api\AuthController::login')->name('api.auth.login');
// $router->post('/auth/logout', 'App\Http\Controllers\Api\AuthController::logout')->name('api.auth.logout');
// $router->post('/auth/refresh', 'App\Http\Controllers\Api\AuthController::refresh')->name('api.auth.refresh');

// Tracker API
// $router->get('/torrents', 'App\Http\Controllers\Api\TorrentController::index')->name('api.torrents.index');
// $router->get('/torrents/{id}', 'App\Http\Controllers\Api\TorrentController::show')->name('api.torrents.show');
// $router->post('/torrents', 'App\Http\Controllers\Api\TorrentController::store')->name('api.torrents.store');
// $router->put('/torrents/{id}', 'App\Http\Controllers\Api\TorrentController::update')->name('api.torrents.update');
// $router->delete('/torrents/{id}', 'App\Http\Controllers\Api\TorrentController::destroy')->name('api.torrents.destroy');

// Forum API
// $router->resource('/forums', 'App\Http\Controllers\Api\ForumController');

// Stats API
// $router->get('/stats/tracker', 'App\Http\Controllers\Api\StatsController::tracker')->name('api.stats.tracker');
// $router->get('/stats/forum', 'App\Http\Controllers\Api\StatsController::forum')->name('api.stats.forum');
// $router->get('/stats/users', 'App\Http\Controllers\Api\StatsController::users')->name('api.stats.users');

// Search API
// $router->get('/search/torrents', 'App\Http\Controllers\Api\SearchController::torrents')->name('api.search.torrents');
// $router->get('/search/users', 'App\Http\Controllers\Api\SearchController::users')->name('api.search.users');
// $router->get('/search/forums', 'App\Http\Controllers\Api\SearchController::forums')->name('api.search.forums');