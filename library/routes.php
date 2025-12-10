<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

use TorrentPier\Controllers\RobotsController;
use TorrentPier\Router\Router;
use TorrentPier\Router\LegacyAdapter;
use TorrentPier\Router\SemanticUrl\LegacyRedirect;
use TorrentPier\Router\SemanticUrl\RouteAdapter;
use TorrentPier\Http\Middleware\TrailingSlashRedirect;

/**
 * Route definitions for TorrentPier
 *
 * @param Router $router
 */
return function (Router $router): void {
    $basePath = dirname(__DIR__);

    // ==============================================================
    // Modern PSR-7 controllers (in src/Controllers/)
    // ==============================================================

    $router->get('/robots.txt', new RobotsController());

    // ==============================================================
    // SEO-friendly semantic routes (must be defined before legacy routes)
    // Format: /type/slug.id/
    // ==============================================================

    // Topics: /topic/slug.123/
    $router->any('/topic/{params}/', new RouteAdapter('topic'));
    $router->get('/topic/{params}', new TrailingSlashRedirect());

    // Forums: /forum/slug.123/
    $router->any('/forum/{params}/', new RouteAdapter('forum'));
    $router->get('/forum/{params}', new TrailingSlashRedirect());

    // Groups: /groups/ (list), /groups/slug.123/, /groups/slug.123/edit/
    $router->any('/groups/', new LegacyAdapter($basePath . '/src/Controllers/group.php', 'group', options: ['manage_session' => true]));
    $router->get('/groups', new TrailingSlashRedirect());
    $router->any('/groups/{params}/edit/', new RouteAdapter('groups_edit', options: ['manage_session' => true]));
    $router->get('/groups/{params}/edit', new TrailingSlashRedirect());
    $router->any('/groups/{params}/', new RouteAdapter('groups', options: ['manage_session' => true]));
    $router->get('/groups/{params}', new TrailingSlashRedirect());

    // Profile standalone pages (static routes MUST come before variable routes)
    $router->any('/profile/bonus/', new LegacyAdapter($basePath . '/src/Controllers/profile.php', 'profile', options: ['mode' => 'bonus']));
    $router->get('/profile/bonus', new TrailingSlashRedirect());
    $router->any('/profile/watchlist/', new LegacyAdapter($basePath . '/src/Controllers/profile.php', 'profile', options: ['mode' => 'watch']));
    $router->get('/profile/watchlist', new TrailingSlashRedirect());

    // Members: /members/ (list), /members/slug.123/ (profile), /members/slug.123/email/
    $router->any('/members/', new LegacyAdapter($basePath . '/src/Controllers/memberlist.php', options: ['manage_session' => true]));
    $router->get('/members', new TrailingSlashRedirect());
    $router->any('/members/{params}/email/', new RouteAdapter('members', options: ['action' => 'email']));
    $router->get('/members/{params}/email', new TrailingSlashRedirect());
    $router->any('/members/{params}/', new RouteAdapter('members'));
    $router->get('/members/{params}', new TrailingSlashRedirect());

    // Standalone auth/account pages
    $router->any('/register/', new LegacyAdapter($basePath . '/src/Controllers/profile.php', 'profile', options: ['mode' => 'register']));
    $router->get('/register', new TrailingSlashRedirect());
    $router->any('/settings/', new LegacyAdapter($basePath . '/src/Controllers/profile.php', 'profile', options: ['mode' => 'editprofile']));
    $router->get('/settings', new TrailingSlashRedirect());
    $router->any('/password-recovery/', new LegacyAdapter($basePath . '/src/Controllers/profile.php', 'profile', options: ['mode' => 'sendpassword']));
    $router->get('/password-recovery', new TrailingSlashRedirect());
    $router->any('/activate/{key}/', new LegacyAdapter($basePath . '/src/Controllers/profile.php', 'profile', options: ['mode' => 'activate']));
    $router->get('/activate/{key}', new TrailingSlashRedirect());

    // ==============================================================
    // Migrated controllers (in src/Controllers/)
    // ==============================================================

    // Index (homepage)
    $router->any('/', new LegacyAdapter($basePath . '/src/Controllers/index.php', options: ['manage_session' => true]));

    // GET
    $router->get('/dl', new LegacyAdapter($basePath . '/src/Controllers/dl.php'));
    $router->get('/feed', new LegacyAdapter($basePath . '/src/Controllers/feed.php', options: ['manage_session' => true]));
    $router->get('/filelist', new LegacyAdapter($basePath . '/src/Controllers/filelist.php'));
    $router->get('/info', new LegacyAdapter($basePath . '/src/Controllers/info.php'));
    $router->get('/playback_m3u', new LegacyAdapter($basePath . '/src/Controllers/playback_m3u.php', options: ['manage_session' => true]));
    $router->get('/terms', new LegacyAdapter($basePath . '/src/Controllers/terms.php'));

    // POST
    $router->post('/poll', new LegacyAdapter($basePath . '/src/Controllers/poll.php', 'vote', ['manage_session' => true]));

    // ANY (GET + POST)
    $router->any('/ajax', new LegacyAdapter($basePath . '/src/Controllers/ajax.php'));
    $router->any('/dl_list', new LegacyAdapter($basePath . '/src/Controllers/dl_list.php'));
    $router->any('/login', new LegacyAdapter($basePath . '/src/Controllers/login.php'));
    $router->any('/modcp', new LegacyAdapter($basePath . '/src/Controllers/modcp.php', options: ['manage_session' => true]));
    $router->any('/posting', new LegacyAdapter($basePath . '/src/Controllers/posting.php'));
    $router->any('/privmsg', new LegacyAdapter($basePath . '/src/Controllers/privmsg.php', 'pm', ['manage_session' => true]));
    $router->any('/search', new LegacyAdapter($basePath . '/src/Controllers/search.php', options: ['manage_session' => true]));
    $router->any('/tracker', new LegacyAdapter($basePath . '/src/Controllers/tracker.php', options: ['manage_session' => true]));

    // ==============================================================
    // Legacy routes with redirects to SEO-friendly URLs
    // ==============================================================

    // viewtopic?t=123 -> /topic/slug.123/
    $router->any('/viewtopic', new LegacyRedirect(
        'topic',
        $basePath . '/src/Controllers/viewtopic.php'
    ));

    // viewforum?f=123 -> /forum/slug.123/
    $router->any('/viewforum', new LegacyRedirect(
        'forum',
        $basePath . '/src/Controllers/viewforum.php'
    ));

    // profile?mode=viewprofile&u=123 -> /members/slug.123/
    // Note: Only redirects for mode=viewprofile, other modes use fallback controller
    $router->any('/profile', new LegacyRedirect(
        'members',
        $basePath . '/src/Controllers/profile.php'
    ));
};
