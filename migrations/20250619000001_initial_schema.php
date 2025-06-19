<?php
/**
 * TorrentPier Initial Schema Migration
 * Converts install/sql/mysql.sql to Phinx migration
 */

use Phinx\Migration\AbstractMigration;

class InitialSchema extends AbstractMigration
{
    public function up()
    {
        // Set SQL mode for compatibility
        $this->execute("SET SQL_MODE = ''");

        // Core forum tables - InnoDB for data integrity
        $this->createForumTables();

        // BitTorrent tracker tables - MyISAM for performance
        $this->createTrackerTables();

        // Configuration and system tables - InnoDB
        $this->createSystemTables();

        // Attachment system - InnoDB
        $this->createAttachmentTables();

        // User management - InnoDB
        $this->createUserTables();

        // Cache and temporary tables - MyISAM (expendable)
        $this->createCacheTables();
    }

    private function createForumTables()
    {
        // bb_categories
        $table = $this->table('bb_categories', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'cat_id'
        ]);
        $table->addColumn('cat_id', 'integer', ['limit' => 5, 'signed' => false, 'identity' => true])
            ->addColumn('cat_title', 'string', ['limit' => 100, 'default' => '', 'null' => false])
            ->addColumn('cat_order', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0, 'null' => false])
            ->addIndex('cat_order')
            ->create();

        // bb_forums
        $table = $this->table('bb_forums', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'forum_id'
        ]);
        $table->addColumn('forum_id', 'integer', ['limit' => 5, 'signed' => false, 'identity' => true])
            ->addColumn('cat_id', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('forum_name', 'string', ['limit' => 150, 'default' => '', 'null' => false])
            ->addColumn('forum_desc', 'text', ['null' => false])
            ->addColumn('forum_status', 'integer', ['limit' => 4, 'default' => 0, 'null' => false])
            ->addColumn('forum_order', 'integer', ['limit' => 5, 'signed' => false, 'default' => 1, 'null' => false])
            ->addColumn('forum_posts', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('forum_topics', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('forum_last_post_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('forum_tpl_id', 'integer', ['limit' => 6, 'default' => 0, 'null' => false])
            ->addColumn('prune_days', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('auth_view', 'integer', ['limit' => 2, 'default' => 0, 'null' => false])
            ->addColumn('auth_read', 'integer', ['limit' => 2, 'default' => 0, 'null' => false])
            ->addColumn('auth_post', 'integer', ['limit' => 2, 'default' => 0, 'null' => false])
            ->addColumn('auth_reply', 'integer', ['limit' => 2, 'default' => 0, 'null' => false])
            ->addColumn('auth_edit', 'integer', ['limit' => 2, 'default' => 0, 'null' => false])
            ->addColumn('auth_delete', 'integer', ['limit' => 2, 'default' => 0, 'null' => false])
            ->addColumn('auth_sticky', 'integer', ['limit' => 2, 'default' => 0, 'null' => false])
            ->addColumn('auth_announce', 'integer', ['limit' => 2, 'default' => 0, 'null' => false])
            ->addColumn('auth_vote', 'integer', ['limit' => 2, 'default' => 0, 'null' => false])
            ->addColumn('auth_pollcreate', 'integer', ['limit' => 2, 'default' => 0, 'null' => false])
            ->addColumn('auth_attachments', 'integer', ['limit' => 2, 'default' => 0, 'null' => false])
            ->addColumn('auth_download', 'integer', ['limit' => 2, 'default' => 0, 'null' => false])
            ->addColumn('allow_reg_tracker', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('allow_porno_topic', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('self_moderated', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('forum_parent', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('show_on_index', 'boolean', ['default' => true, 'null' => false])
            ->addColumn('forum_display_sort', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('forum_display_order', 'boolean', ['default' => false, 'null' => false])
            ->addIndex(['forum_order'], ['name' => 'forums_order'])
            ->addIndex('cat_id')
            ->addIndex('forum_last_post_id')
            ->addIndex('forum_parent')
            ->create();

        // bb_topics
        $table = $this->table('bb_topics', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'topic_id'
        ]);
        $table->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'identity' => true])
            ->addColumn('forum_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('topic_title', 'string', ['limit' => 250, 'default' => '', 'null' => false])
            ->addColumn('topic_poster', 'integer', ['limit' => 8, 'default' => 0, 'null' => false])
            ->addColumn('topic_time', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('topic_views', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('topic_replies', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('topic_status', 'integer', ['limit' => 3, 'default' => 0, 'null' => false])
            ->addColumn('topic_vote', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('topic_type', 'integer', ['limit' => 3, 'default' => 0, 'null' => false])
            ->addColumn('topic_first_post_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('topic_last_post_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('topic_moved_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('topic_attachment', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('topic_dl_type', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('topic_last_post_time', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('topic_show_first_post', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('topic_allow_robots', 'boolean', ['default' => false, 'null' => false])
            ->addIndex('forum_id')
            ->addIndex('topic_last_post_id')
            ->addIndex('topic_last_post_time')
            ->create();

        // Add fulltext index for topic titles
        $this->execute('ALTER TABLE bb_topics ADD FULLTEXT KEY topic_title (topic_title)');

        // bb_posts
        $table = $this->table('bb_posts', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'post_id'
        ]);
        $table->addColumn('post_id', 'integer', ['limit' => 8, 'signed' => false, 'identity' => true])
            ->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('forum_id', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('poster_id', 'integer', ['limit' => 8, 'default' => 0, 'null' => false])
            ->addColumn('post_time', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('poster_ip', 'string', ['limit' => 42, 'default' => '0', 'null' => false])
            ->addColumn('poster_rg_id', 'integer', ['limit' => 8, 'default' => 0, 'null' => false])
            ->addColumn('attach_rg_sig', 'integer', ['limit' => 4, 'default' => 0, 'null' => false])
            ->addColumn('post_username', 'string', ['limit' => 25, 'default' => '', 'null' => false])
            ->addColumn('post_edit_time', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('post_edit_count', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('post_attachment', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('user_post', 'boolean', ['default' => true, 'null' => false])
            ->addColumn('mc_comment', 'text', ['default' => '', 'null' => false])
            ->addColumn('mc_type', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('mc_user_id', 'integer', ['limit' => 8, 'default' => 0, 'null' => false])
            ->addIndex('topic_id')
            ->addIndex('poster_id')
            ->addIndex('post_time')
            ->addIndex(['forum_id', 'post_time'], ['name' => 'forum_id_post_time'])
            ->create();

        // bb_posts_text
        $table = $this->table('bb_posts_text', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'post_id'
        ]);
        $table->addColumn('post_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('post_text', 'text', ['limit' => 16777215, 'null' => false]) // MEDIUMTEXT
            ->create();
    }

    private function createTrackerTables()
    {
        // bb_bt_torrents - Core torrent registry (MyISAM for performance)
        $table = $this->table('bb_bt_torrents', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'topic_id'
        ]);
        $table->addColumn('info_hash', 'varbinary', ['limit' => 20, 'default' => '', 'null' => false])
            ->addColumn('info_hash_v2', 'varbinary', ['limit' => 32, 'default' => '', 'null' => false])
            ->addColumn('post_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('poster_id', 'integer', ['limit' => 9, 'default' => 0, 'null' => false])
            ->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('forum_id', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('attach_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('size', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('reg_time', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('call_seed_time', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('complete_count', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('seeder_last_seen', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('tor_status', 'integer', ['limit' => 4, 'default' => 0, 'null' => false])
            ->addColumn('checked_user_id', 'integer', ['limit' => 8, 'default' => 0, 'null' => false])
            ->addColumn('checked_time', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('tor_type', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('speed_up', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('speed_down', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('last_seeder_id', 'integer', ['limit' => 8, 'default' => 0, 'null' => false])
            ->addIndex('post_id', ['unique' => true])
            ->addIndex('topic_id', ['unique' => true])
            ->addIndex('attach_id', ['unique' => true])
            ->addIndex('reg_time')
            ->addIndex('forum_id')
            ->addIndex('poster_id')
            ->create();

        // bb_bt_tracker - Active peer tracking (MyISAM for high-write performance)
        $table = $this->table('bb_bt_tracker', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'peer_hash'
        ]);
        $table->addColumn('peer_hash', 'string', ['limit' => 32, 'collation' => 'utf8_bin', 'default' => '', 'null' => false])
            ->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('peer_id', 'string', ['limit' => 20, 'default' => '0', 'null' => false])
            ->addColumn('user_id', 'integer', ['limit' => 9, 'default' => 0, 'null' => false])
            ->addColumn('ip', 'string', ['limit' => 42, 'null' => true])
            ->addColumn('ipv6', 'string', ['limit' => 42, 'null' => true])
            ->addColumn('port', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('seeder', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('releaser', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('tor_type', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('uploaded', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('downloaded', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('remain', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('speed_up', 'integer', ['limit' => 11, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('speed_down', 'integer', ['limit' => 11, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('up_add', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('down_add', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('update_time', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('complete_percent', 'biginteger', ['default' => 0, 'null' => false])
            ->addColumn('complete', 'boolean', ['default' => false, 'null' => false])
            ->addIndex('topic_id')
            ->addIndex('user_id')
            ->create();

        // bb_bt_users - User tracker statistics
        $table = $this->table('bb_bt_users', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'user_id'
        ]);
        $table->addColumn('user_id', 'integer', ['limit' => 9, 'default' => 0, 'null' => false])
            ->addColumn('auth_key', 'char', ['limit' => 20, 'collation' => 'utf8_bin', 'default' => '', 'null' => false])
            ->addColumn('u_up_total', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('u_down_total', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('u_up_release', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('u_up_bonus', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('up_today', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('down_today', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('up_release_today', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('up_bonus_today', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('points_today', 'float', ['precision' => 16, 'scale' => 2, 'signed' => false, 'default' => 0.00, 'null' => false])
            ->addColumn('up_yesterday', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('down_yesterday', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('up_release_yesterday', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('up_bonus_yesterday', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('points_yesterday', 'float', ['precision' => 16, 'scale' => 2, 'signed' => false, 'default' => 0.00, 'null' => false])
            ->addColumn('ratio_nulled', 'boolean', ['default' => false, 'null' => false])
            ->addIndex('auth_key', ['unique' => true])
            ->create();

        // Snapshot tables - expendable, use MyISAM
        $this->createSnapshotTables();
    }

    private function createSnapshotTables()
    {
        // bb_bt_tracker_snap - Tracker snapshot (expendable)
        $table = $this->table('bb_bt_tracker_snap', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'topic_id'
        ]);
        $table->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('seeders', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
            ->addColumn('leechers', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
            ->addColumn('speed_up', 'integer', ['limit' => 11, 'signed' => false, 'default' => 0])
            ->addColumn('speed_down', 'integer', ['limit' => 11, 'signed' => false, 'default' => 0])
            ->addColumn('completed', 'integer', ['limit' => 10, 'default' => 0])
            ->create();

        // bb_bt_dlstatus_snap - Download status snapshot (expendable)
        $table = $this->table('bb_bt_dlstatus_snap', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false
        ]);
        $table->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('dl_status', 'integer', ['limit' => 4, 'default' => 0, 'null' => false])
            ->addColumn('users_count', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0, 'null' => false])
            ->addIndex('topic_id')
            ->create();

        // buf_topic_view - Topic view buffer (expendable)
        $table = $this->table('buf_topic_view', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'topic_id'
        ]);
        $table->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('topic_views', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->create();

        // buf_last_seeder - Last seeder buffer (expendable)
        $table = $this->table('buf_last_seeder', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'topic_id'
        ]);
        $table->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('seeder_last_seen', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('user_id', 'integer', ['limit' => 8, 'default' => 0, 'null' => false])
            ->create();
    }

    private function createSystemTables()
    {
        // bb_config - Main configuration
        $table = $this->table('bb_config', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'config_name'
        ]);
        $table->addColumn('config_name', 'string', ['limit' => 155, 'default' => '', 'null' => false])
            ->addColumn('config_value', 'text', ['null' => false])
            ->create();

        // bb_cron - Scheduled tasks
        $table = $this->table('bb_cron', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'cron_id'
        ]);
        $table->addColumn('cron_id', 'integer', ['limit' => 5, 'signed' => false, 'identity' => true])
            ->addColumn('cron_active', 'integer', ['limit' => 4, 'default' => 1, 'null' => false])
            ->addColumn('cron_title', 'char', ['limit' => 120, 'default' => '', 'null' => false])
            ->addColumn('cron_script', 'char', ['limit' => 120, 'default' => '', 'null' => false])
            ->addColumn('schedule', 'enum', ['values' => ['hourly', 'daily', 'weekly', 'monthly', 'interval'], 'default' => 'daily', 'null' => false])
            ->addColumn('run_day', 'enum', ['values' => array_map('strval', range(1, 28)), 'null' => true])
            ->addColumn('run_time', 'time', ['default' => '04:00:00', 'null' => false])
            ->addColumn('run_order', 'integer', ['limit' => 4, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('last_run', 'datetime', ['default' => '1900-01-01 00:00:00', 'null' => false])
            ->addColumn('next_run', 'datetime', ['default' => '1900-01-01 00:00:00', 'null' => false])
            ->addColumn('run_interval', 'time', ['null' => true, 'default' => '00:00:00'])
            ->addColumn('log_enabled', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('log_file', 'char', ['limit' => 120, 'default' => '', 'null' => false])
            ->addColumn('log_sql_queries', 'integer', ['limit' => 4, 'default' => 0, 'null' => false])
            ->addColumn('disable_board', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('run_counter', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addIndex('cron_title', ['unique' => true, 'name' => 'title'])
            ->addIndex('cron_script', ['unique' => true, 'name' => 'script'])
            ->create();

        // bb_sessions - User sessions
        $table = $this->table('bb_sessions', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'session_id'
        ]);
        $table->addColumn('session_id', 'char', ['limit' => 255, 'collation' => 'utf8_bin', 'default' => '', 'null' => false])
            ->addColumn('session_user_id', 'integer', ['limit' => 8, 'default' => 0])
            ->addColumn('session_start', 'integer', ['limit' => 11, 'default' => 0])
            ->addColumn('session_time', 'integer', ['limit' => 11, 'default' => 0])
            ->addColumn('session_ip', 'string', ['limit' => 42, 'default' => '0'])
            ->addColumn('session_logged_in', 'boolean', ['default' => false])
            ->addColumn('session_admin', 'integer', ['limit' => 2, 'default' => 0])
            ->create();
    }

    private function createAttachmentTables()
    {
        // bb_attachments
        $table = $this->table('bb_attachments', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['attach_id', 'post_id']
        ]);
        $table->addColumn('attach_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('post_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('user_id_1', 'integer', ['limit' => 8, 'default' => 0, 'null' => false])
            ->create();

        // bb_attachments_desc
        $table = $this->table('bb_attachments_desc', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'attach_id'
        ]);
        $table->addColumn('attach_id', 'integer', ['limit' => 8, 'signed' => false, 'identity' => true])
            ->addColumn('physical_filename', 'string', ['limit' => 255, 'default' => '', 'null' => false])
            ->addColumn('real_filename', 'string', ['limit' => 255, 'default' => '', 'null' => false])
            ->addColumn('download_count', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('comment', 'string', ['limit' => 255, 'default' => '', 'null' => false])
            ->addColumn('extension', 'string', ['limit' => 100, 'default' => '', 'null' => false])
            ->addColumn('mimetype', 'string', ['limit' => 100, 'default' => '', 'null' => false])
            ->addColumn('filesize', 'integer', ['limit' => 20, 'default' => 0, 'null' => false])
            ->addColumn('filetime', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('thumbnail', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('tracker_status', 'boolean', ['default' => false, 'null' => false])
            ->addIndex('filetime')
            ->addIndex('filesize')
            ->addIndex(['physical_filename'], ['name' => 'physical_filename', 'limit' => ['physical_filename' => 10]])
            ->create();

        // bb_extensions
        $table = $this->table('bb_extensions', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'ext_id'
        ]);
        $table->addColumn('ext_id', 'integer', ['limit' => 8, 'signed' => false, 'identity' => true])
            ->addColumn('group_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
            ->addColumn('extension', 'string', ['limit' => 100, 'default' => ''])
            ->addColumn('comment', 'string', ['limit' => 100, 'default' => ''])
            ->create();

        // bb_extension_groups
        $table = $this->table('bb_extension_groups', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'group_id'
        ]);
        $table->addColumn('group_id', 'integer', ['limit' => 8, 'identity' => true])
            ->addColumn('group_name', 'string', ['limit' => 20, 'default' => ''])
            ->addColumn('cat_id', 'integer', ['limit' => 2, 'default' => 0])
            ->addColumn('allow_group', 'boolean', ['default' => false])
            ->addColumn('download_mode', 'integer', ['limit' => 1, 'signed' => false, 'default' => 1])
            ->addColumn('upload_icon', 'string', ['limit' => 100, 'default' => ''])
            ->addColumn('max_filesize', 'integer', ['limit' => 20, 'default' => 0])
            ->addColumn('forum_permissions', 'text')
            ->create();
    }

    private function createUserTables()
    {
        // bb_users
        $table = $this->table('bb_users', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'user_id'
        ]);
        $table->addColumn('user_id', 'integer', ['limit' => 8, 'identity' => true])
            ->addColumn('user_active', 'boolean', ['default' => true, 'null' => false])
            ->addColumn('username', 'string', ['limit' => 255, 'default' => '', 'null' => false])
            ->addColumn('user_password', 'string', ['limit' => 255, 'collation' => 'utf8_bin', 'default' => '', 'null' => false])
            ->addColumn('user_session_time', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('user_lastvisit', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('user_last_ip', 'string', ['limit' => 42, 'default' => '0', 'null' => false])
            ->addColumn('user_regdate', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('user_reg_ip', 'string', ['limit' => 42, 'default' => '0', 'null' => false])
            ->addColumn('user_level', 'integer', ['limit' => 4, 'default' => 0, 'null' => false])
            ->addColumn('user_posts', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('user_timezone', 'decimal', ['precision' => 5, 'scale' => 2, 'default' => 0.00, 'null' => false])
            ->addColumn('user_lang', 'string', ['limit' => 255, 'default' => 'en', 'null' => false])
            ->addColumn('user_new_privmsg', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('user_unread_privmsg', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('user_last_privmsg', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('user_opt', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('user_rank', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('avatar_ext_id', 'integer', ['limit' => 4, 'default' => 0, 'null' => false])
            ->addColumn('user_gender', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('user_birthday', 'date', ['default' => '1900-01-01', 'null' => false])
            ->addColumn('user_email', 'string', ['limit' => 255, 'default' => '', 'null' => false])
            ->addColumn('user_skype', 'string', ['limit' => 32, 'default' => '', 'null' => false])
            ->addColumn('user_twitter', 'string', ['limit' => 15, 'default' => '', 'null' => false])
            ->addColumn('user_icq', 'string', ['limit' => 15, 'default' => '', 'null' => false])
            ->addColumn('user_website', 'string', ['limit' => 100, 'default' => '', 'null' => false])
            ->addColumn('user_from', 'string', ['limit' => 100, 'default' => '', 'null' => false])
            ->addColumn('user_sig', 'text', ['default' => '', 'null' => false])
            ->addColumn('user_occ', 'string', ['limit' => 100, 'default' => '', 'null' => false])
            ->addColumn('user_interests', 'string', ['limit' => 255, 'default' => '', 'null' => false])
            ->addColumn('user_actkey', 'string', ['limit' => 255, 'default' => '', 'null' => false])
            ->addColumn('user_newpasswd', 'string', ['limit' => 255, 'default' => '', 'null' => false])
            ->addColumn('autologin_id', 'string', ['limit' => 255, 'collation' => 'utf8_bin', 'default' => '', 'null' => false])
            ->addColumn('user_newest_pm_id', 'integer', ['limit' => 8, 'default' => 0, 'null' => false])
            ->addColumn('user_points', 'float', ['precision' => 16, 'scale' => 2, 'default' => 0.00, 'null' => false])
            ->addColumn('tpl_name', 'string', ['limit' => 255, 'default' => 'default', 'null' => false])
            ->addIndex(['username'], ['name' => 'username', 'limit' => ['username' => 10]])
            ->addIndex(['user_email'], ['name' => 'user_email', 'limit' => ['user_email' => 10]])
            ->addIndex('user_level')
            ->create();

        // bb_groups
        $table = $this->table('bb_groups', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'group_id'
        ]);
        $table->addColumn('group_id', 'integer', ['limit' => 8, 'identity' => true])
            ->addColumn('avatar_ext_id', 'integer', ['limit' => 15, 'default' => 0, 'null' => false])
            ->addColumn('group_time', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('mod_time', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('group_type', 'integer', ['limit' => 4, 'default' => 1, 'null' => false])
            ->addColumn('release_group', 'integer', ['limit' => 4, 'default' => 0, 'null' => false])
            ->addColumn('group_name', 'string', ['limit' => 40, 'default' => '', 'null' => false])
            ->addColumn('group_description', 'text', ['default' => '', 'null' => false])
            ->addColumn('group_signature', 'text', ['default' => '', 'null' => false])
            ->addColumn('group_moderator', 'integer', ['limit' => 8, 'default' => 0, 'null' => false])
            ->addColumn('group_single_user', 'boolean', ['default' => true, 'null' => false])
            ->addIndex('group_single_user')
            ->create();

        // bb_user_group
        $table = $this->table('bb_user_group', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['group_id', 'user_id']
        ]);
        $table->addColumn('group_id', 'integer', ['limit' => 8, 'default' => 0, 'null' => false])
            ->addColumn('user_id', 'integer', ['limit' => 8, 'default' => 0, 'null' => false])
            ->addColumn('user_pending', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('user_time', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addIndex('user_id')
            ->create();

        // bb_ranks
        $table = $this->table('bb_ranks', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'rank_id'
        ]);
        $table->addColumn('rank_id', 'integer', ['limit' => 5, 'signed' => false, 'identity' => true])
            ->addColumn('rank_title', 'string', ['limit' => 50, 'default' => '', 'null' => false])
            ->addColumn('rank_image', 'string', ['limit' => 255, 'default' => '', 'null' => false])
            ->addColumn('rank_style', 'string', ['limit' => 255, 'default' => '', 'null' => false])
            ->create();
    }

    private function createCacheTables()
    {
        // Additional tracker-related tables that are more expendable
        $tables = [
            'bb_bt_dlstatus',
            'bb_bt_torstat',
            'bb_bt_tor_dl_stat',
            'bb_bt_last_torstat',
            'bb_bt_last_userstat',
            'bb_bt_torhelp',
            'bb_bt_user_settings'
        ];

        // Create these tables with appropriate engines
        $this->createRemainingTrackerTables();

        // Create remaining system tables
        $this->createRemainingSystemTables();
    }

    private function createRemainingTrackerTables()
    {
        // bb_bt_dlstatus - Download status tracking
        $table = $this->table('bb_bt_dlstatus', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['user_id', 'topic_id']
        ]);
        $table->addColumn('user_id', 'integer', ['limit' => 9, 'default' => 0, 'null' => false])
            ->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('user_status', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('last_modified_dlstatus', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addIndex('topic_id')
            ->create();

        // bb_bt_torstat - Torrent statistics per user
        $table = $this->table('bb_bt_torstat', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['topic_id', 'user_id']
        ]);
        $table->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('user_id', 'integer', ['limit' => 9, 'default' => 0, 'null' => false])
            ->addColumn('last_modified_torstat', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('completed', 'boolean', ['default' => false, 'null' => false])
            ->create();

        // bb_bt_tor_dl_stat - Torrent download statistics
        $table = $this->table('bb_bt_tor_dl_stat', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['topic_id', 'user_id']
        ]);
        $table->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('user_id', 'integer', ['limit' => 9, 'default' => 0, 'null' => false])
            ->addColumn('attach_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('t_up_total', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('t_down_total', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('t_bonus_total', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->create();

        // bb_bt_last_torstat - Last torrent statistics
        $table = $this->table('bb_bt_last_torstat', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['topic_id', 'user_id']
        ]);
        $table->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('user_id', 'integer', ['limit' => 9, 'default' => 0, 'null' => false])
            ->addColumn('dl_status', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('up_add', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('down_add', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('release_add', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('bonus_add', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('speed_up', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('speed_down', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->create();

        // bb_bt_last_userstat - Last user statistics
        $table = $this->table('bb_bt_last_userstat', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'user_id'
        ]);
        $table->addColumn('user_id', 'integer', ['limit' => 9, 'default' => 0, 'null' => false])
            ->addColumn('up_add', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('down_add', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('release_add', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('bonus_add', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('speed_up', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('speed_down', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->create();

        // bb_bt_torhelp - Torrent help system
        $table = $this->table('bb_bt_torhelp', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'user_id'
        ]);
        $table->addColumn('user_id', 'integer', ['limit' => 9, 'default' => 0, 'null' => false])
            ->addColumn('topic_id_csv', 'text', ['null' => false])
            ->create();

        // bb_bt_user_settings - User tracker preferences
        $table = $this->table('bb_bt_user_settings', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'user_id'
        ]);
        $table->addColumn('user_id', 'integer', ['limit' => 9, 'default' => 0, 'null' => false])
            ->addColumn('tor_search_set', 'text', ['null' => false])
            ->addColumn('last_modified', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->create();

        // bb_thx - Thanks/voting system
        $table = $this->table('bb_thx', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['topic_id', 'user_id']
        ]);
        $table->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('user_id', 'integer', ['limit' => 8, 'default' => 0, 'null' => false])
            ->addColumn('time', 'integer', ['limit' => 11, 'default' => 0])
            ->create();
    }

    private function createRemainingSystemTables()
    {
        // Additional system tables
        $this->createMessagingTables();
        $this->createSearchTables();
        $this->createMiscTables();
    }

    private function createMessagingTables()
    {
        // bb_privmsgs - Private messages
        $table = $this->table('bb_privmsgs', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'privmsgs_id'
        ]);
        $table->addColumn('privmsgs_id', 'integer', ['limit' => 8, 'signed' => false, 'identity' => true])
            ->addColumn('privmsgs_type', 'integer', ['limit' => 4, 'default' => 0, 'null' => false])
            ->addColumn('privmsgs_subject', 'string', ['limit' => 255, 'default' => '', 'null' => false])
            ->addColumn('privmsgs_from_userid', 'integer', ['limit' => 8, 'default' => 0, 'null' => false])
            ->addColumn('privmsgs_to_userid', 'integer', ['limit' => 8, 'default' => 0, 'null' => false])
            ->addColumn('privmsgs_date', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('privmsgs_ip', 'string', ['limit' => 42, 'default' => '0', 'null' => false])
            ->addIndex('privmsgs_from_userid')
            ->addIndex('privmsgs_to_userid')
            ->create();

        // bb_privmsgs_text - Private message content
        $table = $this->table('bb_privmsgs_text', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'privmsgs_text_id'
        ]);
        $table->addColumn('privmsgs_text_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('privmsgs_text', 'text', ['limit' => 16777215, 'null' => false]) // MEDIUMTEXT
            ->create();
    }

    private function createSearchTables()
    {
        // bb_posts_search - Search index for posts
        $table = $this->table('bb_posts_search', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'post_id'
        ]);
        $table->addColumn('post_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('search_words', 'text', ['null' => false])
            ->create();

        // Add fulltext index
        $this->execute('ALTER TABLE bb_posts_search ADD FULLTEXT KEY search_words (search_words)');

        // bb_posts_html - Cached HTML posts
        $table = $this->table('bb_posts_html', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'post_id'
        ]);
        $table->addColumn('post_id', 'integer', ['limit' => 9, 'default' => 0, 'null' => false])
            ->addColumn('post_html_time', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('post_html', 'text', ['limit' => 16777215, 'default' => '', 'null' => false]) // MEDIUMTEXT
            ->create();

        // bb_search_results - Search result cache
        $table = $this->table('bb_search_results', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['session_id', 'search_type']
        ]);
        $table->addColumn('session_id', 'char', ['limit' => 255, 'collation' => 'utf8_bin', 'default' => '', 'null' => false])
            ->addColumn('search_type', 'integer', ['limit' => 4, 'default' => 0, 'null' => false])
            ->addColumn('search_id', 'string', ['limit' => 255, 'collation' => 'utf8_bin', 'default' => '', 'null' => false])
            ->addColumn('search_time', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('search_settings', 'text', ['null' => false])
            ->addColumn('search_array', 'text', ['null' => false])
            ->create();

        // bb_search_rebuild - Search rebuild status
        $table = $this->table('bb_search_rebuild', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'rebuild_session_id'
        ]);
        $table->addColumn('rebuild_session_id', 'integer', ['limit' => 8, 'signed' => false, 'identity' => true])
            ->addColumn('start_post_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('end_post_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('start_time', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('end_time', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('last_cycle_time', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('session_time', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('session_posts', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('session_cycles', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('search_size', 'integer', ['limit' => 10, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('rebuild_session_status', 'boolean', ['default' => false, 'null' => false])
            ->create();
    }

    private function createMiscTables()
    {
        // bb_smilies - Emoticons
        $table = $this->table('bb_smilies', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'smilies_id'
        ]);
        $table->addColumn('smilies_id', 'integer', ['limit' => 5, 'signed' => false, 'identity' => true])
            ->addColumn('code', 'string', ['limit' => 50, 'default' => '', 'null' => false])
            ->addColumn('smile_url', 'string', ['limit' => 100, 'default' => '', 'null' => false])
            ->addColumn('emoticon', 'string', ['limit' => 75, 'default' => '', 'null' => false])
            ->create();

        // bb_words - Word censoring
        $table = $this->table('bb_words', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'word_id'
        ]);
        $table->addColumn('word_id', 'integer', ['limit' => 8, 'signed' => false, 'identity' => true])
            ->addColumn('word', 'char', ['limit' => 100, 'default' => '', 'null' => false])
            ->addColumn('replacement', 'char', ['limit' => 100, 'default' => '', 'null' => false])
            ->create();

        // bb_banlist - User bans
        $table = $this->table('bb_banlist', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['ban_id', 'ban_userid']
        ]);
        $table->addColumn('ban_id', 'integer', ['limit' => 8, 'signed' => false, 'identity' => true])
            ->addColumn('ban_userid', 'integer', ['limit' => 8, 'default' => 0, 'null' => false])
            ->addColumn('ban_reason', 'string', ['limit' => 255, 'default' => '', 'null' => false])
            ->create();

        // bb_disallow - Disallowed usernames
        $table = $this->table('bb_disallow', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'disallow_id'
        ]);
        $table->addColumn('disallow_id', 'integer', ['limit' => 8, 'signed' => false, 'identity' => true])
            ->addColumn('disallow_username', 'string', ['limit' => 25, 'default' => '', 'null' => false])
            ->create();

        // Additional utility tables
        $this->createUtilityTables();
    }

    private function createUtilityTables()
    {
        // bb_log - Action logging
        $table = $this->table('bb_log', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false
        ]);
        $table->addColumn('log_type_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('log_user_id', 'integer', ['limit' => 9, 'default' => 0, 'null' => false])
            ->addColumn('log_user_ip', 'string', ['limit' => 42, 'default' => '0', 'null' => false])
            ->addColumn('log_forum_id', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('log_forum_id_new', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('log_topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('log_topic_id_new', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('log_topic_title', 'string', ['limit' => 250, 'default' => '', 'null' => false])
            ->addColumn('log_topic_title_new', 'string', ['limit' => 250, 'default' => '', 'null' => false])
            ->addColumn('log_time', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('log_msg', 'text', ['null' => false])
            ->addIndex('log_time')
            ->create();

        // Add fulltext index
        $this->execute('ALTER TABLE bb_log ADD FULLTEXT KEY log_topic_title (log_topic_title)');

        // bb_poll_votes - Poll voting
        $table = $this->table('bb_poll_votes', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['topic_id', 'vote_id']
        ]);
        $table->addColumn('topic_id', 'integer', ['limit' => 10, 'signed' => false, 'null' => false])
            ->addColumn('vote_id', 'integer', ['limit' => 4, 'signed' => false, 'null' => false])
            ->addColumn('vote_text', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('vote_result', 'integer', ['limit' => 8, 'signed' => false, 'null' => false])
            ->create();

        // bb_poll_users - Poll participation
        $table = $this->table('bb_poll_users', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['topic_id', 'user_id']
        ]);
        $table->addColumn('topic_id', 'integer', ['limit' => 10, 'signed' => false, 'null' => false])
            ->addColumn('user_id', 'integer', ['limit' => 8, 'null' => false])
            ->addColumn('vote_ip', 'string', ['limit' => 42, 'default' => '0', 'null' => false])
            ->addColumn('vote_dt', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->create();

        // bb_topics_watch - Topic watching
        $table = $this->table('bb_topics_watch', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false
        ]);
        $table->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('user_id', 'integer', ['limit' => 8, 'default' => 0, 'null' => false])
            ->addColumn('notify_status', 'boolean', ['default' => false, 'null' => false])
            ->addIndex('topic_id')
            ->addIndex('user_id')
            ->addIndex('notify_status')
            ->create();

        // bb_topic_tpl - Topic templates
        $table = $this->table('bb_topic_tpl', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'tpl_id'
        ]);
        $table->addColumn('tpl_id', 'integer', ['limit' => 6, 'identity' => true])
            ->addColumn('tpl_name', 'string', ['limit' => 60, 'default' => '', 'null' => false])
            ->addColumn('tpl_src_form', 'text', ['null' => false])
            ->addColumn('tpl_src_title', 'text', ['null' => false])
            ->addColumn('tpl_src_msg', 'text', ['null' => false])
            ->addColumn('tpl_comment', 'text', ['null' => false])
            ->addColumn('tpl_rules_post_id', 'integer', ['limit' => 10, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('tpl_last_edit_tm', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addColumn('tpl_last_edit_by', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addIndex('tpl_name', ['unique' => true])
            ->create();

        // Remaining attachment tables
        $this->createRemainingAttachmentTables();

        // Auth tables
        $this->createAuthTables();
    }

    private function createRemainingAttachmentTables()
    {
        // bb_attachments_config
        $table = $this->table('bb_attachments_config', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'config_name'
        ]);
        $table->addColumn('config_name', 'string', ['limit' => 155, 'default' => '', 'null' => false])
            ->addColumn('config_value', 'string', ['limit' => 255, 'default' => '', 'null' => false])
            ->create();

        // bb_attach_quota
        $table = $this->table('bb_attach_quota', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false
        ]);
        $table->addColumn('user_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('group_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('quota_type', 'integer', ['limit' => 2, 'default' => 0, 'null' => false])
            ->addColumn('quota_limit_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0, 'null' => false])
            ->addIndex('quota_type')
            ->create();

        // bb_quota_limits
        $table = $this->table('bb_quota_limits', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'quota_limit_id'
        ]);
        $table->addColumn('quota_limit_id', 'integer', ['limit' => 8, 'signed' => false, 'identity' => true])
            ->addColumn('quota_desc', 'string', ['limit' => 20, 'default' => '', 'null' => false])
            ->addColumn('quota_limit', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->create();
    }

    private function createAuthTables()
    {
        // bb_auth_access
        $table = $this->table('bb_auth_access', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['group_id', 'forum_id']
        ]);
        $table->addColumn('group_id', 'integer', ['limit' => 8, 'default' => 0, 'null' => false])
            ->addColumn('forum_id', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('forum_perm', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->addIndex('forum_id')
            ->create();

        // bb_auth_access_snap
        $table = $this->table('bb_auth_access_snap', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['user_id', 'forum_id']
        ]);
        $table->addColumn('user_id', 'integer', ['limit' => 9, 'default' => 0, 'null' => false])
            ->addColumn('forum_id', 'integer', ['limit' => 6, 'default' => 0, 'null' => false])
            ->addColumn('forum_perm', 'integer', ['limit' => 11, 'default' => 0, 'null' => false])
            ->create();
    }

    public function down()
    {
        // Drop all tables in reverse dependency order
        $this->execute('SET FOREIGN_KEY_CHECKS = 0');

        $tables = [
            'bb_auth_access_snap', 'bb_auth_access', 'bb_quota_limits', 'bb_attach_quota',
            'bb_attachments_config', 'bb_topic_tpl', 'bb_topics_watch', 'bb_poll_users',
            'bb_poll_votes', 'bb_log', 'bb_disallow', 'bb_banlist', 'bb_words',
            'bb_smilies', 'bb_search_rebuild', 'bb_search_results', 'bb_posts_html',
            'bb_posts_search', 'bb_privmsgs_text', 'bb_privmsgs', 'bb_bt_user_settings',
            'bb_bt_torhelp', 'bb_bt_last_userstat', 'bb_bt_last_torstat', 'bb_bt_tor_dl_stat',
            'bb_bt_torstat', 'bb_bt_dlstatus', 'bb_thx', 'buf_last_seeder', 'buf_topic_view',
            'bb_bt_dlstatus_snap', 'bb_bt_tracker_snap', 'bb_bt_users', 'bb_bt_tracker',
            'bb_bt_torrents', 'bb_sessions', 'bb_cron', 'bb_config', 'bb_ranks', 'bb_user_group',
            'bb_groups', 'bb_users', 'bb_extension_groups', 'bb_extensions', 'bb_attachments_desc',
            'bb_attachments', 'bb_posts_text', 'bb_posts', 'bb_topics', 'bb_forums', 'bb_categories'
        ];

        foreach ($tables as $table) {
            $this->table($table)->drop()->save();
        }

        $this->execute('SET FOREIGN_KEY_CHECKS = 1');
    }
}
