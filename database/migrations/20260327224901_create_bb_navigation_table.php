<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateBbNavigationTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('bb_navigation', [
            'id' => false,
            'primary_key' => 'navigation_id',
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        $table
            ->addColumn('navigation_id', 'string', [
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('parent_navigation_id', 'string', [
                'limit' => 50,
                'default' => '',
                'null' => false,
            ])
            ->addColumn('navigation_type', 'string', [
                'limit' => 20,
                'default' => 'admin',
                'null' => false,
            ])
            ->addColumn('display_order', 'integer', [
                'default' => 100,
                'null' => false,
            ])
            ->addColumn('link', 'string', [
                'limit' => 255,
                'default' => '',
                'null' => false,
            ])
            ->addColumn('icon', 'string', [
                'limit' => 100,
                'default' => '',
                'null' => false,
            ])
            ->addColumn('title_key', 'string', [
                'limit' => 100,
                'default' => '',
                'null' => false,
            ])
            ->addColumn('permission_check', 'string', [
                'limit' => 50,
                'default' => '',
                'null' => false,
            ])
            ->addColumn('addon_id', 'string', [
                'limit' => 100,
                'null' => true,
                'default' => null,
            ])
            ->addColumn('is_active', 'boolean', [
                'default' => true,
                'null' => false,
            ])
            ->addColumn('created_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => false,
            ])
            ->addColumn('updated_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'null' => false,
            ])
            ->addIndex(['parent_navigation_id'], ['name' => 'idx_parent'])
            ->addIndex(['navigation_type'], ['name' => 'idx_type'])
            ->addIndex(['addon_id'], ['name' => 'idx_addon'])
            ->addIndex(['navigation_type', 'parent_navigation_id', 'display_order'], ['name' => 'idx_type_parent_order'])
            ->create();

        // Seed admin navigation: root categories
        $this->table('bb_navigation')->insert([
            ['navigation_id' => 'admin.dashboard', 'parent_navigation_id' => '', 'navigation_type' => 'admin', 'display_order' => 10, 'link' => 'index.php', 'icon' => 'lucide-layout-dashboard', 'title_key' => 'ADMIN_INDEX', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.general', 'parent_navigation_id' => '', 'navigation_type' => 'admin', 'display_order' => 20, 'link' => '', 'icon' => 'lucide-settings', 'title_key' => 'GENERAL', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.forums', 'parent_navigation_id' => '', 'navigation_type' => 'admin', 'display_order' => 30, 'link' => '', 'icon' => 'lucide-messages-square', 'title_key' => 'FORUMS', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.users', 'parent_navigation_id' => '', 'navigation_type' => 'admin', 'display_order' => 40, 'link' => '', 'icon' => 'lucide-users', 'title_key' => 'USERS', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.groups', 'parent_navigation_id' => '', 'navigation_type' => 'admin', 'display_order' => 50, 'link' => '', 'icon' => 'lucide-users-round', 'title_key' => 'GROUPS', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.mods', 'parent_navigation_id' => '', 'navigation_type' => 'admin', 'display_order' => 60, 'link' => '', 'icon' => 'lucide-wrench', 'title_key' => 'MODS', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.torrentpier', 'parent_navigation_id' => '', 'navigation_type' => 'admin', 'display_order' => 70, 'link' => '', 'icon' => 'lucide-download', 'title_key' => 'APP_NAME', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.marketplace', 'parent_navigation_id' => '', 'navigation_type' => 'admin', 'display_order' => 80, 'link' => '', 'icon' => 'lucide-store', 'title_key' => 'MARKETPLACE', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
        ])->saveData();

        // Seed admin navigation: child items
        $this->table('bb_navigation')->insert([
            // General
            ['navigation_id' => 'admin.general.config', 'parent_navigation_id' => 'admin.general', 'navigation_type' => 'admin', 'display_order' => 10, 'link' => 'admin_board.php?mode=config', 'icon' => '', 'title_key' => 'CONFIGURATION', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.general.terms', 'parent_navigation_id' => 'admin.general', 'navigation_type' => 'admin', 'display_order' => 20, 'link' => 'admin_terms.php', 'icon' => '', 'title_key' => 'TERMS', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.general.word_censor', 'parent_navigation_id' => 'admin.general', 'navigation_type' => 'admin', 'display_order' => 30, 'link' => 'admin_words.php', 'icon' => '', 'title_key' => 'WORD_CENSOR', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.general.smilies', 'parent_navigation_id' => 'admin.general', 'navigation_type' => 'admin', 'display_order' => 40, 'link' => 'admin_smilies.php', 'icon' => '', 'title_key' => 'SMILIES', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.general.phpinfo', 'parent_navigation_id' => 'admin.general', 'navigation_type' => 'admin', 'display_order' => 50, 'link' => 'admin_phpinfo.php', 'icon' => '', 'title_key' => 'PHP_INFO', 'permission_check' => 'super_admin', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.general.rebuild_search', 'parent_navigation_id' => 'admin.general', 'navigation_type' => 'admin', 'display_order' => 60, 'link' => 'admin_rebuild_search.php', 'icon' => '', 'title_key' => 'REBUILD_SEARCH_INDEX', 'permission_check' => 'super_admin', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.general.migrations', 'parent_navigation_id' => 'admin.general', 'navigation_type' => 'admin', 'display_order' => 70, 'link' => 'admin_migrations.php', 'icon' => '', 'title_key' => 'MIGRATIONS_STATUS', 'permission_check' => 'super_admin', 'addon_id' => null, 'is_active' => true],

            // Forums
            ['navigation_id' => 'admin.forums.manage', 'parent_navigation_id' => 'admin.forums', 'navigation_type' => 'admin', 'display_order' => 10, 'link' => 'admin_forums.php', 'icon' => '', 'title_key' => 'MANAGE', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.forums.permissions', 'parent_navigation_id' => 'admin.forums', 'navigation_type' => 'admin', 'display_order' => 20, 'link' => 'admin_forumauth.php', 'icon' => '', 'title_key' => 'PERMISSIONS', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.forums.permissions_list', 'parent_navigation_id' => 'admin.forums', 'navigation_type' => 'admin', 'display_order' => 30, 'link' => 'admin_forumauth_list.php', 'icon' => '', 'title_key' => 'PERMISSIONS_LIST', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.forums.prune', 'parent_navigation_id' => 'admin.forums', 'navigation_type' => 'admin', 'display_order' => 40, 'link' => 'admin_forum_prune.php', 'icon' => '', 'title_key' => 'PRUNE', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],

            // Users
            ['navigation_id' => 'admin.users.search', 'parent_navigation_id' => 'admin.users', 'navigation_type' => 'admin', 'display_order' => 10, 'link' => 'admin_user_search.php', 'icon' => '', 'title_key' => 'SEARCH', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.users.ban', 'parent_navigation_id' => 'admin.users', 'navigation_type' => 'admin', 'display_order' => 20, 'link' => 'admin_user_ban.php', 'icon' => '', 'title_key' => 'BAN_MANAGEMENT', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.users.disallow', 'parent_navigation_id' => 'admin.users', 'navigation_type' => 'admin', 'display_order' => 30, 'link' => 'admin_disallow.php', 'icon' => '', 'title_key' => 'DISALLOW', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.users.permissions', 'parent_navigation_id' => 'admin.users', 'navigation_type' => 'admin', 'display_order' => 40, 'link' => 'admin_ug_auth.php?mode=user', 'icon' => '', 'title_key' => 'PERMISSIONS', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.users.ranks', 'parent_navigation_id' => 'admin.users', 'navigation_type' => 'admin', 'display_order' => 50, 'link' => 'admin_ranks.php', 'icon' => '', 'title_key' => 'RANKS', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.users.actions_log', 'parent_navigation_id' => 'admin.users', 'navigation_type' => 'admin', 'display_order' => 60, 'link' => 'admin_log.php', 'icon' => '', 'title_key' => 'ACTIONS_LOG', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.users.spam_log', 'parent_navigation_id' => 'admin.users', 'navigation_type' => 'admin', 'display_order' => 70, 'link' => 'admin_spam_log.php', 'icon' => '', 'title_key' => 'SPAM_LOG', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],

            // Groups
            ['navigation_id' => 'admin.groups.manage', 'parent_navigation_id' => 'admin.groups', 'navigation_type' => 'admin', 'display_order' => 10, 'link' => 'admin_groups.php', 'icon' => '', 'title_key' => 'MANAGE', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.groups.permissions', 'parent_navigation_id' => 'admin.groups', 'navigation_type' => 'admin', 'display_order' => 20, 'link' => 'admin_ug_auth.php?mode=group', 'icon' => '', 'title_key' => 'PERMISSIONS', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],

            // Mods
            ['navigation_id' => 'admin.mods.config', 'parent_navigation_id' => 'admin.mods', 'navigation_type' => 'admin', 'display_order' => 10, 'link' => 'admin_board.php?mode=config_mods', 'icon' => '', 'title_key' => 'CONFIGURATION', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.mods.mass_email', 'parent_navigation_id' => 'admin.mods', 'navigation_type' => 'admin', 'display_order' => 20, 'link' => 'admin_mass_email.php', 'icon' => '', 'title_key' => 'MASS_EMAIL', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.mods.sitemap', 'parent_navigation_id' => 'admin.mods', 'navigation_type' => 'admin', 'display_order' => 30, 'link' => 'admin_sitemap.php', 'icon' => '', 'title_key' => 'SITEMAP', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.mods.robots', 'parent_navigation_id' => 'admin.mods', 'navigation_type' => 'admin', 'display_order' => 40, 'link' => 'admin_robots.php', 'icon' => '', 'title_key' => 'ROBOTS_TXT_EDITOR_TITLE', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],

            // TorrentPier (APP_NAME)
            ['navigation_id' => 'admin.torrentpier.cron', 'parent_navigation_id' => 'admin.torrentpier', 'navigation_type' => 'admin', 'display_order' => 10, 'link' => 'admin_cron.php?mode=list', 'icon' => '', 'title_key' => 'CRON', 'permission_check' => 'super_admin', 'addon_id' => null, 'is_active' => true],
            ['navigation_id' => 'admin.torrentpier.forum_config', 'parent_navigation_id' => 'admin.torrentpier', 'navigation_type' => 'admin', 'display_order' => 20, 'link' => 'admin_bt_forum_cfg.php', 'icon' => '', 'title_key' => 'FORUM_CONFIG', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],

            // Marketplace
            ['navigation_id' => 'admin.marketplace.modifications', 'parent_navigation_id' => 'admin.marketplace', 'navigation_type' => 'admin', 'display_order' => 10, 'link' => 'admin_modifications.php', 'icon' => '', 'title_key' => 'MODIFICATIONS_LIST', 'permission_check' => '', 'addon_id' => null, 'is_active' => true],
        ])->saveData();
    }

    public function down(): void
    {
        $this->table('bb_navigation')->drop()->save();
    }
}
