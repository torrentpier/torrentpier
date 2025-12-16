<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

use TorrentPier\Router\LegacyAdapter;
use TorrentPier\Router\Router;

/**
 * Admin route definitions
 *
 * All routes use LegacyAdapter to execute procedural PHP files.
 * Using GET and POST methods for admin pages (forms use POST).
 */
return static function (Router $router): void {
    $adminPath = dirname(__DIR__) . '/app/Http/Controllers/Admin';

    $router->group('/admin', function ($group) use ($adminPath) {
        // Index (frameset, left pane, right pane)
        $group->any('/index.php', new LegacyAdapter($adminPath . '/index.php', 'admin_index'));
        $group->any('/', new LegacyAdapter($adminPath . '/index.php', 'admin_index'));
        $group->any('', new LegacyAdapter($adminPath . '/index.php', 'admin_index'));

        // Admin modules
        $group->any('/admin_board.php', new LegacyAdapter($adminPath . '/admin_board.php'));
        $group->any('/admin_bt_forum_cfg.php', new LegacyAdapter($adminPath . '/admin_bt_forum_cfg.php'));
        $group->any('/admin_cron.php', new LegacyAdapter($adminPath . '/admin_cron.php'));
        $group->any('/admin_disallow.php', new LegacyAdapter($adminPath . '/admin_disallow.php'));
        $group->any('/admin_forum_prune.php', new LegacyAdapter($adminPath . '/admin_forum_prune.php'));
        $group->any('/admin_forumauth.php', new LegacyAdapter($adminPath . '/admin_forumauth.php'));
        $group->any('/admin_forumauth_list.php', new LegacyAdapter($adminPath . '/admin_forumauth_list.php'));
        $group->any('/admin_forums.php', new LegacyAdapter($adminPath . '/admin_forums.php'));
        $group->any('/admin_groups.php', new LegacyAdapter($adminPath . '/admin_groups.php'));
        $group->any('/admin_log.php', new LegacyAdapter($adminPath . '/admin_log.php'));
        $group->any('/admin_mass_email.php', new LegacyAdapter($adminPath . '/admin_mass_email.php'));
        $group->any('/admin_migrations.php', new LegacyAdapter($adminPath . '/admin_migrations.php'));
        $group->any('/admin_phpinfo.php', new LegacyAdapter($adminPath . '/admin_phpinfo.php'));
        $group->any('/admin_ranks.php', new LegacyAdapter($adminPath . '/admin_ranks.php'));
        $group->any('/admin_rebuild_search.php', new LegacyAdapter($adminPath . '/admin_rebuild_search.php'));
        $group->any('/admin_robots.php', new LegacyAdapter($adminPath . '/admin_robots.php'));
        $group->any('/admin_sitemap.php', new LegacyAdapter($adminPath . '/admin_sitemap.php'));
        $group->any('/admin_smilies.php', new LegacyAdapter($adminPath . '/admin_smilies.php'));
        $group->any('/admin_terms.php', new LegacyAdapter($adminPath . '/admin_terms.php'));
        $group->any('/admin_ug_auth.php', new LegacyAdapter($adminPath . '/admin_ug_auth.php'));
        $group->any('/admin_user_ban.php', new LegacyAdapter($adminPath . '/admin_user_ban.php'));
        $group->any('/admin_user_search.php', new LegacyAdapter($adminPath . '/admin_user_search.php'));
        $group->any('/admin_words.php', new LegacyAdapter($adminPath . '/admin_words.php'));
    })->middleware('session')->middleware('admin');
};
