<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

use App\Http\Controllers\Api\Ajax\AvatarController;
use App\Http\Controllers\Api\Ajax\CallseedController;
use App\Http\Controllers\Api\Ajax\ChangeTorrentController;
use App\Http\Controllers\Api\Ajax\ChangeTorStatusController;
use App\Http\Controllers\Api\Ajax\ChangeUserOptController;
use App\Http\Controllers\Api\Ajax\ChangeUserRankController;
use App\Http\Controllers\Api\Ajax\EditUserProfileController;
use App\Http\Controllers\Api\Ajax\FfprobeInfoController;
use App\Http\Controllers\Api\Ajax\GroupMembershipController;
use App\Http\Controllers\Api\Ajax\IndexDataController;
use App\Http\Controllers\Api\Ajax\ManageAdminController;
use App\Http\Controllers\Api\Ajax\ManageGroupController;
use App\Http\Controllers\Api\Ajax\ManageUserController;
use App\Http\Controllers\Api\Ajax\ModActionController;
use App\Http\Controllers\Api\Ajax\PasskeyController;
use App\Http\Controllers\Api\Ajax\PostModCommentController;
use App\Http\Controllers\Api\Ajax\PostsController;
use App\Http\Controllers\Api\Ajax\SitemapController;
use App\Http\Controllers\Api\Ajax\ThxController;
use App\Http\Controllers\Api\Ajax\TopicTplController;
use App\Http\Controllers\Api\Ajax\UserRegisterController;
use App\Http\Controllers\Api\Ajax\ViewPostController;
use App\Http\Controllers\Api\Ajax\ViewTorrentController;
use App\Http\Middleware\Api\EnsureRole;
use TorrentPier\Router\RouteGroup;
use TorrentPier\Router\Router;

/**
 * API route definitions for TorrentPier
 *
 * @param Router $router
 */
return static function (Router $router): void {
    $router->group('/api/ajax', function (RouteGroup $group) {
        // Guest
        $group->post('/view-post', ViewPostController::class);
        $group->post('/posts', PostsController::class);
        $group->post('/thx', ThxController::class);
        $group->post('/view-torrent', ViewTorrentController::class);
        $group->post('/user-register', UserRegisterController::class);
        $group->post('/index-data', IndexDataController::class);
        $group->post('/ffprobe-info', FfprobeInfoController::class);

        // User
        $group->post('/avatar', AvatarController::class)->middleware(new EnsureRole('user'));
        $group->post('/passkey', PasskeyController::class)->middleware(new EnsureRole('user'));
        $group->post('/change-torrent', ChangeTorrentController::class)->middleware(new EnsureRole('user'));
        $group->post('/change-tor-status', ChangeTorStatusController::class)->middleware(new EnsureRole('user'));
        $group->post('/manage-group', ManageGroupController::class)->middleware(new EnsureRole('user'));
        $group->post('/callseed', CallseedController::class)->middleware(new EnsureRole('user'));

        // Mod
        $group->post('/mod-action', ModActionController::class)->middleware(new EnsureRole('mod'));
        $group->post('/topic-tpl', TopicTplController::class)->middleware(new EnsureRole('mod'));
        $group->post('/group-membership', GroupMembershipController::class)->middleware(new EnsureRole('mod'));
        $group->post('/post-mod-comment', PostModCommentController::class)->middleware(new EnsureRole('mod'));

        // Admin
        $group->post('/edit-user-profile', EditUserProfileController::class)->middleware(new EnsureRole('admin'));
        $group->post('/change-user-rank', ChangeUserRankController::class)->middleware(new EnsureRole('admin'));
        $group->post('/change-user-opt', ChangeUserOptController::class)->middleware(new EnsureRole('admin'));
        $group->post('/manage-user', ManageUserController::class)->middleware(new EnsureRole('admin'));
        $group->post('/manage-admin', ManageAdminController::class)->middleware(new EnsureRole('admin'));
        $group->post('/sitemap', SitemapController::class)->middleware(new EnsureRole('admin'));
    })->middleware('session');
};
