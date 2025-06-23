<?php

declare(strict_types=1);

use App\Http\Routing\Router;
use Illuminate\Http\Request;

/**
 * Web Routes
 * 
 * Define all web-accessible routes here
 */

/** @var Router $router */
$router = app(Router::class);

// Test routes showcasing new Laravel-style features
$router->get('/hello', 'App\Http\Controllers\Web\HelloWorldController::index')->name('hello');
$router->get('/hello.json', 'App\Http\Controllers\Web\HelloWorldController::jsonResponse')->name('hello.json');
$router->get('/hello/json', 'App\Http\Controllers\Web\HelloWorldController::jsonResponse')->name('hello.json.alt');
$router->get('/hello/features', 'App\Http\Controllers\Web\HelloWorldController::features')->name('hello.features');

// Future routes can be added here:

// Home page
// $router->get('/', 'App\Http\Controllers\Web\HomeController::index')->name('home');

// Forum routes
// $router->get('/forum', 'App\Http\Controllers\Web\ForumController::index')->name('forum.index');
// $router->get('/forum/{id}', 'App\Http\Controllers\Web\ForumController::show')->name('forum.show');
// $router->get('/forum/{id}/topics', 'App\Http\Controllers\Web\TopicController::index')->name('forum.topics');

// Tracker routes  
// $router->get('/tracker', 'App\Http\Controllers\Web\TrackerController::index')->name('tracker.index');
// $router->get('/tracker/torrent/{id}', 'App\Http\Controllers\Web\TrackerController::show')->name('torrent.show');
// $router->get('/tracker/categories', 'App\Http\Controllers\Web\CategoryController::index')->name('categories.index');

// User routes
// $router->get('/profile/{id}', 'App\Http\Controllers\Web\UserController::profile')->name('user.profile');
// $router->get('/users', 'App\Http\Controllers\Web\UserController::index')->name('users.index');

// Authentication routes (if using custom auth)
// $router->get('/login', 'App\Http\Controllers\Auth\LoginController::showLoginForm')->name('login');
// $router->post('/login', 'App\Http\Controllers\Auth\LoginController::login');
// $router->post('/logout', 'App\Http\Controllers\Auth\LoginController::logout')->name('logout');
// $router->get('/register', 'App\Http\Controllers\Auth\RegisterController::showRegistrationForm')->name('register');
// $router->post('/register', 'App\Http\Controllers\Auth\RegisterController::register');

// Legacy routes - explicit routes for each legacy controller
$router->get('/', 'App\Http\Controllers\Web\LegacyController@index')->name('legacy.index');
$router->get('/ajax.php', 'App\Http\Controllers\Web\LegacyController@ajax')->name('legacy.ajax');
$router->get('/dl.php', 'App\Http\Controllers\Web\LegacyController@dl')->name('legacy.dl');
$router->get('/dl_list.php', 'App\Http\Controllers\Web\LegacyController@dl_list')->name('legacy.dl_list');
$router->get('/feed.php', 'App\Http\Controllers\Web\LegacyController@feed')->name('legacy.feed');
$router->get('/filelist.php', 'App\Http\Controllers\Web\LegacyController@filelist')->name('legacy.filelist');
$router->get('/group.php', 'App\Http\Controllers\Web\LegacyController@group')->name('legacy.group');
$router->get('/group_edit.php', 'App\Http\Controllers\Web\LegacyController@group_edit')->name('legacy.group_edit');
$router->get('/index.php', 'App\Http\Controllers\Web\LegacyController@index')->name('legacy.index_php');
$router->get('/info.php', 'App\Http\Controllers\Web\LegacyController@info')->name('legacy.info');
$router->get('/login.php', 'App\Http\Controllers\Web\LegacyController@login')->name('legacy.login');
$router->get('/memberlist.php', 'App\Http\Controllers\Web\LegacyController@memberlist')->name('legacy.memberlist');
$router->get('/modcp.php', 'App\Http\Controllers\Web\LegacyController@modcp')->name('legacy.modcp');
$router->get('/playback_m3u.php', 'App\Http\Controllers\Web\LegacyController@playback_m3u')->name('legacy.playback_m3u');
$router->get('/poll.php', 'App\Http\Controllers\Web\LegacyController@poll')->name('legacy.poll');
$router->get('/posting.php', 'App\Http\Controllers\Web\LegacyController@posting')->name('legacy.posting');
$router->get('/privmsg.php', 'App\Http\Controllers\Web\LegacyController@privmsg')->name('legacy.privmsg');
$router->get('/profile.php', 'App\Http\Controllers\Web\LegacyController@profile')->name('legacy.profile');
$router->get('/search.php', 'App\Http\Controllers\Web\LegacyController@search')->name('legacy.search');
$router->get('/terms.php', 'App\Http\Controllers\Web\LegacyController@terms')->name('legacy.terms');
$router->get('/tracker.php', 'App\Http\Controllers\Web\LegacyController@tracker')->name('legacy.tracker');
$router->get('/viewforum.php', 'App\Http\Controllers\Web\LegacyController@viewforum')->name('legacy.viewforum');
$router->get('/viewtopic.php', 'App\Http\Controllers\Web\LegacyController@viewtopic')->name('legacy.viewtopic');

// POST routes for legacy controllers that need them
$router->post('/ajax.php', 'App\Http\Controllers\Web\LegacyController@ajax')->name('legacy.ajax.post');
$router->post('/login.php', 'App\Http\Controllers\Web\LegacyController@login')->name('legacy.login.post');
$router->post('/posting.php', 'App\Http\Controllers\Web\LegacyController@posting')->name('legacy.posting.post');
$router->post('/privmsg.php', 'App\Http\Controllers\Web\LegacyController@privmsg')->name('legacy.privmsg.post');
$router->post('/profile.php', 'App\Http\Controllers\Web\LegacyController@profile')->name('legacy.profile.post');
$router->post('/search.php', 'App\Http\Controllers\Web\LegacyController@search')->name('legacy.search.post');