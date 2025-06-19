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
              ->addColumn('cat_title', 'string', ['limit' => 100, 'default' => ''])
              ->addColumn('cat_order', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0])
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
              ->addColumn('cat_id', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0])
              ->addColumn('forum_name', 'string', ['limit' => 150, 'default' => ''])
              ->addColumn('forum_desc', 'text')
              ->addColumn('forum_status', 'integer', ['limit' => 4, 'default' => 0])
              ->addColumn('forum_order', 'integer', ['limit' => 5, 'signed' => false, 'default' => 1])
              ->addColumn('forum_posts', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('forum_topics', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('forum_last_post_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('forum_tpl_id', 'integer', ['limit' => 6, 'default' => 0])
              ->addColumn('prune_days', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0])
              ->addColumn('auth_view', 'integer', ['limit' => 2, 'default' => 0])
              ->addColumn('auth_read', 'integer', ['limit' => 2, 'default' => 0])
              ->addColumn('auth_post', 'integer', ['limit' => 2, 'default' => 0])
              ->addColumn('auth_reply', 'integer', ['limit' => 2, 'default' => 0])
              ->addColumn('auth_edit', 'integer', ['limit' => 2, 'default' => 0])
              ->addColumn('auth_delete', 'integer', ['limit' => 2, 'default' => 0])
              ->addColumn('auth_sticky', 'integer', ['limit' => 2, 'default' => 0])
              ->addColumn('auth_announce', 'integer', ['limit' => 2, 'default' => 0])
              ->addColumn('auth_vote', 'integer', ['limit' => 2, 'default' => 0])
              ->addColumn('auth_pollcreate', 'integer', ['limit' => 2, 'default' => 0])
              ->addColumn('auth_attachments', 'integer', ['limit' => 2, 'default' => 0])
              ->addColumn('auth_download', 'integer', ['limit' => 2, 'default' => 0])
              ->addColumn('allow_reg_tracker', 'boolean', ['default' => false])
              ->addColumn('allow_porno_topic', 'boolean', ['default' => false])
              ->addColumn('self_moderated', 'boolean', ['default' => false])
              ->addColumn('forum_parent', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0])
              ->addColumn('show_on_index', 'boolean', ['default' => true])
              ->addColumn('forum_display_sort', 'boolean', ['default' => false])
              ->addColumn('forum_display_order', 'boolean', ['default' => false])
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
              ->addColumn('forum_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('topic_title', 'string', ['limit' => 250, 'default' => ''])
              ->addColumn('topic_poster', 'integer', ['limit' => 8, 'default' => 0])
              ->addColumn('topic_time', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('topic_views', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('topic_replies', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('topic_status', 'integer', ['limit' => 3, 'default' => 0])
              ->addColumn('topic_vote', 'boolean', ['default' => false])
              ->addColumn('topic_type', 'integer', ['limit' => 3, 'default' => 0])
              ->addColumn('topic_first_post_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('topic_last_post_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('topic_moved_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('topic_attachment', 'boolean', ['default' => false])
              ->addColumn('topic_dl_type', 'boolean', ['default' => false])
              ->addColumn('topic_last_post_time', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('topic_show_first_post', 'boolean', ['default' => false])
              ->addColumn('topic_allow_robots', 'boolean', ['default' => false])
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
              ->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('forum_id', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0])
              ->addColumn('poster_id', 'integer', ['limit' => 8, 'default' => 0])
              ->addColumn('post_time', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('poster_ip', 'string', ['limit' => 42, 'default' => '0'])
              ->addColumn('poster_rg_id', 'integer', ['limit' => 8, 'default' => 0])
              ->addColumn('attach_rg_sig', 'integer', ['limit' => 4, 'default' => 0])
              ->addColumn('post_username', 'string', ['limit' => 25, 'default' => ''])
              ->addColumn('post_edit_time', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('post_edit_count', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0])
              ->addColumn('post_attachment', 'boolean', ['default' => false])
              ->addColumn('user_post', 'boolean', ['default' => true])
              ->addColumn('mc_comment', 'text', ['default' => ''])
              ->addColumn('mc_type', 'boolean', ['default' => false])
              ->addColumn('mc_user_id', 'integer', ['limit' => 8, 'default' => 0])
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
        $table->addColumn('post_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('post_text', 'text', ['limit' => 16777215]) // MEDIUMTEXT
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
        $table->addColumn('info_hash', 'varbinary', ['limit' => 20, 'default' => ''])
              ->addColumn('info_hash_v2', 'varbinary', ['limit' => 32, 'default' => ''])
              ->addColumn('post_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('poster_id', 'integer', ['limit' => 9, 'default' => 0])
              ->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('forum_id', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0])
              ->addColumn('attach_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('size', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('reg_time', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('call_seed_time', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('complete_count', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('seeder_last_seen', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('tor_status', 'integer', ['limit' => 4, 'default' => 0])
              ->addColumn('checked_user_id', 'integer', ['limit' => 8, 'default' => 0])
              ->addColumn('checked_time', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('tor_type', 'boolean', ['default' => false])
              ->addColumn('speed_up', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('speed_down', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('last_seeder_id', 'integer', ['limit' => 8, 'default' => 0])
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
        $table->addColumn('peer_hash', 'string', ['limit' => 32, 'collation' => 'utf8_bin', 'default' => ''])
              ->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('peer_id', 'string', ['limit' => 20, 'default' => '0'])
              ->addColumn('user_id', 'integer', ['limit' => 9, 'default' => 0])
              ->addColumn('ip', 'string', ['limit' => 42, 'null' => true])
              ->addColumn('ipv6', 'string', ['limit' => 42, 'null' => true])
              ->addColumn('port', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0])
              ->addColumn('seeder', 'boolean', ['default' => false])
              ->addColumn('releaser', 'boolean', ['default' => false])
              ->addColumn('tor_type', 'boolean', ['default' => false])
              ->addColumn('uploaded', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('downloaded', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('remain', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('speed_up', 'integer', ['limit' => 11, 'signed' => false, 'default' => 0])
              ->addColumn('speed_down', 'integer', ['limit' => 11, 'signed' => false, 'default' => 0])
              ->addColumn('up_add', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('down_add', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('update_time', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('complete_percent', 'biginteger', ['default' => 0])
              ->addColumn('complete', 'boolean', ['default' => false])
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
        $table->addColumn('user_id', 'integer', ['limit' => 9, 'default' => 0])
              ->addColumn('auth_key', 'char', ['limit' => 20, 'collation' => 'utf8_bin', 'default' => ''])
              ->addColumn('u_up_total', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('u_down_total', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('u_up_release', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('u_up_bonus', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('up_today', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('down_today', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('up_release_today', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('up_bonus_today', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('points_today', 'float', ['precision' => 16, 'scale' => 2, 'signed' => false, 'default' => 0.00])
              ->addColumn('up_yesterday', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('down_yesterday', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('up_release_yesterday', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('up_bonus_yesterday', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('points_yesterday', 'float', ['precision' => 16, 'scale' => 2, 'signed' => false, 'default' => 0.00])
              ->addColumn('ratio_nulled', 'boolean', ['default' => false])
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
        $table->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
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
        $table->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('dl_status', 'integer', ['limit' => 4, 'default' => 0])
              ->addColumn('users_count', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0])
              ->addIndex('topic_id')
              ->create();

        // buf_topic_view - Topic view buffer (expendable)
        $table = $this->table('buf_topic_view', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'topic_id'
        ]);
        $table->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('topic_views', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->create();

        // buf_last_seeder - Last seeder buffer (expendable)
        $table = $this->table('buf_last_seeder', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'topic_id'
        ]);
        $table->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('seeder_last_seen', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('user_id', 'integer', ['limit' => 8, 'default' => 0])
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
        $table->addColumn('config_name', 'string', ['limit' => 155, 'default' => ''])
              ->addColumn('config_value', 'text')
              ->create();

        // bb_cron - Scheduled tasks
        $table = $this->table('bb_cron', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'cron_id'
        ]);
        $table->addColumn('cron_id', 'integer', ['limit' => 5, 'signed' => false, 'identity' => true])
              ->addColumn('cron_active', 'integer', ['limit' => 4, 'default' => 1])
              ->addColumn('cron_title', 'char', ['limit' => 120, 'default' => ''])
              ->addColumn('cron_script', 'char', ['limit' => 120, 'default' => ''])
              ->addColumn('schedule', 'enum', ['values' => ['hourly', 'daily', 'weekly', 'monthly', 'interval'], 'default' => 'daily'])
              ->addColumn('run_day', 'enum', ['values' => array_map('strval', range(1, 28)), 'null' => true])
              ->addColumn('run_time', 'time', ['default' => '04:00:00'])
              ->addColumn('run_order', 'integer', ['limit' => 4, 'signed' => false, 'default' => 0])
              ->addColumn('last_run', 'datetime', ['default' => '1900-01-01 00:00:00'])
              ->addColumn('next_run', 'datetime', ['default' => '1900-01-01 00:00:00'])
              ->addColumn('run_interval', 'time', ['null' => true, 'default' => '00:00:00'])
              ->addColumn('log_enabled', 'boolean', ['default' => false])
              ->addColumn('log_file', 'char', ['limit' => 120, 'default' => ''])
              ->addColumn('log_sql_queries', 'integer', ['limit' => 4, 'default' => 0])
              ->addColumn('disable_board', 'boolean', ['default' => false])
              ->addColumn('run_counter', 'biginteger', ['signed' => false, 'default' => 0])
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
        $table->addColumn('session_id', 'char', ['limit' => 255, 'collation' => 'utf8_bin', 'default' => ''])
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
        $table->addColumn('attach_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('post_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('user_id_1', 'integer', ['limit' => 8, 'default' => 0])
              ->create();

        // bb_attachments_desc
        $table = $this->table('bb_attachments_desc', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'attach_id'
        ]);
        $table->addColumn('attach_id', 'integer', ['limit' => 8, 'signed' => false, 'identity' => true])
              ->addColumn('physical_filename', 'string', ['limit' => 255, 'default' => ''])
              ->addColumn('real_filename', 'string', ['limit' => 255, 'default' => ''])
              ->addColumn('download_count', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('comment', 'string', ['limit' => 255, 'default' => ''])
              ->addColumn('extension', 'string', ['limit' => 100, 'default' => ''])
              ->addColumn('mimetype', 'string', ['limit' => 100, 'default' => ''])
              ->addColumn('filesize', 'integer', ['limit' => 20, 'default' => 0])
              ->addColumn('filetime', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('thumbnail', 'boolean', ['default' => false])
              ->addColumn('tracker_status', 'boolean', ['default' => false])
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
              ->addColumn('user_active', 'boolean', ['default' => true])
              ->addColumn('username', 'string', ['limit' => 255, 'default' => ''])
              ->addColumn('user_password', 'string', ['limit' => 255, 'collation' => 'utf8_bin', 'default' => ''])
              ->addColumn('user_session_time', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('user_lastvisit', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('user_last_ip', 'string', ['limit' => 42, 'default' => '0'])
              ->addColumn('user_regdate', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('user_reg_ip', 'string', ['limit' => 42, 'default' => '0'])
              ->addColumn('user_level', 'integer', ['limit' => 4, 'default' => 0])
              ->addColumn('user_posts', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('user_timezone', 'decimal', ['precision' => 5, 'scale' => 2, 'default' => 0.00])
              ->addColumn('user_lang', 'string', ['limit' => 255, 'default' => 'en'])
              ->addColumn('user_new_privmsg', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0])
              ->addColumn('user_unread_privmsg', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0])
              ->addColumn('user_last_privmsg', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('user_opt', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('user_rank', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('avatar_ext_id', 'integer', ['limit' => 4, 'default' => 0])
              ->addColumn('user_gender', 'boolean', ['default' => false])
              ->addColumn('user_birthday', 'date', ['default' => '1900-01-01'])
              ->addColumn('user_email', 'string', ['limit' => 255, 'default' => ''])
              ->addColumn('user_skype', 'string', ['limit' => 32, 'default' => ''])
              ->addColumn('user_twitter', 'string', ['limit' => 15, 'default' => ''])
              ->addColumn('user_icq', 'string', ['limit' => 15, 'default' => ''])
              ->addColumn('user_website', 'string', ['limit' => 100, 'default' => ''])
              ->addColumn('user_from', 'string', ['limit' => 100, 'default' => ''])
              ->addColumn('user_sig', 'text', ['default' => ''])
              ->addColumn('user_occ', 'string', ['limit' => 100, 'default' => ''])
              ->addColumn('user_interests', 'string', ['limit' => 255, 'default' => ''])
              ->addColumn('user_actkey', 'string', ['limit' => 255, 'default' => ''])
              ->addColumn('user_newpasswd', 'string', ['limit' => 255, 'default' => ''])
              ->addColumn('autologin_id', 'string', ['limit' => 255, 'collation' => 'utf8_bin', 'default' => ''])
              ->addColumn('user_newest_pm_id', 'integer', ['limit' => 8, 'default' => 0])
              ->addColumn('user_points', 'float', ['precision' => 16, 'scale' => 2, 'default' => 0.00])
              ->addColumn('tpl_name', 'string', ['limit' => 255, 'default' => 'default'])
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
              ->addColumn('avatar_ext_id', 'integer', ['limit' => 15, 'default' => 0])
              ->addColumn('group_time', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('mod_time', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('group_type', 'integer', ['limit' => 4, 'default' => 1])
              ->addColumn('release_group', 'integer', ['limit' => 4, 'default' => 0])
              ->addColumn('group_name', 'string', ['limit' => 40, 'default' => ''])
              ->addColumn('group_description', 'text', ['default' => ''])
              ->addColumn('group_signature', 'text', ['default' => ''])
              ->addColumn('group_moderator', 'integer', ['limit' => 8, 'default' => 0])
              ->addColumn('group_single_user', 'boolean', ['default' => true])
              ->addIndex('group_single_user')
              ->create();

        // bb_user_group
        $table = $this->table('bb_user_group', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['group_id', 'user_id']
        ]);
        $table->addColumn('group_id', 'integer', ['limit' => 8, 'default' => 0])
              ->addColumn('user_id', 'integer', ['limit' => 8, 'default' => 0])
              ->addColumn('user_pending', 'boolean', ['default' => false])
              ->addColumn('user_time', 'integer', ['limit' => 11, 'default' => 0])
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
              ->addColumn('rank_title', 'string', ['limit' => 50, 'default' => ''])
              ->addColumn('rank_image', 'string', ['limit' => 255, 'default' => ''])
              ->addColumn('rank_style', 'string', ['limit' => 255, 'default' => ''])
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
        $table->addColumn('user_id', 'integer', ['limit' => 9, 'default' => 0])
              ->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('user_status', 'boolean', ['default' => false])
              ->addColumn('last_modified_dlstatus', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
              ->addIndex('topic_id')
              ->create();

        // bb_bt_torstat - Torrent statistics per user
        $table = $this->table('bb_bt_torstat', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['topic_id', 'user_id']
        ]);
        $table->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('user_id', 'integer', ['limit' => 9, 'default' => 0])
              ->addColumn('last_modified_torstat', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
              ->addColumn('completed', 'boolean', ['default' => false])
              ->create();

        // bb_bt_tor_dl_stat - Torrent download statistics
        $table = $this->table('bb_bt_tor_dl_stat', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['topic_id', 'user_id']
        ]);
        $table->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('user_id', 'integer', ['limit' => 9, 'default' => 0])
              ->addColumn('attach_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('t_up_total', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('t_down_total', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('t_bonus_total', 'biginteger', ['signed' => false, 'default' => 0])
              ->create();

        // bb_bt_last_torstat - Last torrent statistics
        $table = $this->table('bb_bt_last_torstat', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['topic_id', 'user_id']
        ]);
        $table->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('user_id', 'integer', ['limit' => 9, 'default' => 0])
              ->addColumn('dl_status', 'boolean', ['default' => false])
              ->addColumn('up_add', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('down_add', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('release_add', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('bonus_add', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('speed_up', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('speed_down', 'biginteger', ['signed' => false, 'default' => 0])
              ->create();

        // bb_bt_last_userstat - Last user statistics
        $table = $this->table('bb_bt_last_userstat', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'user_id'
        ]);
        $table->addColumn('user_id', 'integer', ['limit' => 9, 'default' => 0])
              ->addColumn('up_add', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('down_add', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('release_add', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('bonus_add', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('speed_up', 'biginteger', ['signed' => false, 'default' => 0])
              ->addColumn('speed_down', 'biginteger', ['signed' => false, 'default' => 0])
              ->create();

        // bb_bt_torhelp - Torrent help system
        $table = $this->table('bb_bt_torhelp', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'user_id'
        ]);
        $table->addColumn('user_id', 'integer', ['limit' => 9, 'default' => 0])
              ->addColumn('topic_id_csv', 'text')
              ->create();

        // bb_bt_user_settings - User tracker preferences
        $table = $this->table('bb_bt_user_settings', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'user_id'
        ]);
        $table->addColumn('user_id', 'integer', ['limit' => 9, 'default' => 0])
              ->addColumn('tor_search_set', 'text')
              ->addColumn('last_modified', 'integer', ['limit' => 11, 'default' => 0])
              ->create();

        // bb_thx - Thanks/voting system
        $table = $this->table('bb_thx', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['topic_id', 'user_id']
        ]);
        $table->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('user_id', 'integer', ['limit' => 8, 'default' => 0])
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
              ->addColumn('privmsgs_type', 'integer', ['limit' => 4, 'default' => 0])
              ->addColumn('privmsgs_subject', 'string', ['limit' => 255, 'default' => ''])
              ->addColumn('privmsgs_from_userid', 'integer', ['limit' => 8, 'default' => 0])
              ->addColumn('privmsgs_to_userid', 'integer', ['limit' => 8, 'default' => 0])
              ->addColumn('privmsgs_date', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('privmsgs_ip', 'string', ['limit' => 42, 'default' => '0'])
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
        $table->addColumn('privmsgs_text_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('privmsgs_text', 'text', ['limit' => 16777215]) // MEDIUMTEXT
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
        $table->addColumn('post_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('search_words', 'text')
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
        $table->addColumn('post_id', 'integer', ['limit' => 9, 'default' => 0])
              ->addColumn('post_html_time', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
              ->addColumn('post_html', 'text', ['limit' => 16777215, 'default' => '']) // MEDIUMTEXT
              ->create();

        // bb_search_results - Search result cache
        $table = $this->table('bb_search_results', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['session_id', 'search_type']
        ]);
        $table->addColumn('session_id', 'char', ['limit' => 255, 'collation' => 'utf8_bin', 'default' => ''])
              ->addColumn('search_type', 'integer', ['limit' => 4, 'default' => 0])
              ->addColumn('search_id', 'string', ['limit' => 255, 'collation' => 'utf8_bin', 'default' => ''])
              ->addColumn('search_time', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('search_settings', 'text')
              ->addColumn('search_array', 'text')
              ->create();

        // bb_search_rebuild - Search rebuild status
        $table = $this->table('bb_search_rebuild', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'rebuild_session_id'
        ]);
        $table->addColumn('rebuild_session_id', 'integer', ['limit' => 8, 'signed' => false, 'identity' => true])
              ->addColumn('start_post_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('end_post_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('start_time', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('end_time', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('last_cycle_time', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('session_time', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('session_posts', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('session_cycles', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('search_size', 'integer', ['limit' => 10, 'signed' => false, 'default' => 0])
              ->addColumn('rebuild_session_status', 'boolean', ['default' => false])
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
              ->addColumn('code', 'string', ['limit' => 50, 'default' => ''])
              ->addColumn('smile_url', 'string', ['limit' => 100, 'default' => ''])
              ->addColumn('emoticon', 'string', ['limit' => 75, 'default' => ''])
              ->create();

        // bb_words - Word censoring
        $table = $this->table('bb_words', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'word_id'
        ]);
        $table->addColumn('word_id', 'integer', ['limit' => 8, 'signed' => false, 'identity' => true])
              ->addColumn('word', 'char', ['limit' => 100, 'default' => ''])
              ->addColumn('replacement', 'char', ['limit' => 100, 'default' => ''])
              ->create();

        // bb_banlist - User bans
        $table = $this->table('bb_banlist', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['ban_id', 'ban_userid']
        ]);
        $table->addColumn('ban_id', 'integer', ['limit' => 8, 'signed' => false, 'identity' => true])
              ->addColumn('ban_userid', 'integer', ['limit' => 8, 'default' => 0])
              ->addColumn('ban_reason', 'string', ['limit' => 255, 'default' => ''])
              ->create();

        // bb_disallow - Disallowed usernames
        $table = $this->table('bb_disallow', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'disallow_id'
        ]);
        $table->addColumn('disallow_id', 'integer', ['limit' => 8, 'signed' => false, 'identity' => true])
              ->addColumn('disallow_username', 'string', ['limit' => 25, 'default' => ''])
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
        $table->addColumn('log_type_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('log_user_id', 'integer', ['limit' => 9, 'default' => 0])
              ->addColumn('log_user_ip', 'string', ['limit' => 42, 'default' => '0'])
              ->addColumn('log_forum_id', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0])
              ->addColumn('log_forum_id_new', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0])
              ->addColumn('log_topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('log_topic_id_new', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('log_topic_title', 'string', ['limit' => 250, 'default' => ''])
              ->addColumn('log_topic_title_new', 'string', ['limit' => 250, 'default' => ''])
              ->addColumn('log_time', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('log_msg', 'text')
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
        $table->addColumn('topic_id', 'integer', ['limit' => 10, 'signed' => false])
              ->addColumn('vote_id', 'integer', ['limit' => 4, 'signed' => false])
              ->addColumn('vote_text', 'string', ['limit' => 255])
              ->addColumn('vote_result', 'integer', ['limit' => 8, 'signed' => false])
              ->create();

        // bb_poll_users - Poll participation
        $table = $this->table('bb_poll_users', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['topic_id', 'user_id']
        ]);
        $table->addColumn('topic_id', 'integer', ['limit' => 10, 'signed' => false])
              ->addColumn('user_id', 'integer', ['limit' => 8])
              ->addColumn('vote_ip', 'string', ['limit' => 42, 'default' => '0'])
              ->addColumn('vote_dt', 'integer', ['limit' => 11, 'default' => 0])
              ->create();

        // bb_topics_watch - Topic watching
        $table = $this->table('bb_topics_watch', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false
        ]);
        $table->addColumn('topic_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('user_id', 'integer', ['limit' => 8, 'default' => 0])
              ->addColumn('notify_status', 'boolean', ['default' => false])
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
              ->addColumn('tpl_name', 'string', ['limit' => 60, 'default' => ''])
              ->addColumn('tpl_src_form', 'text')
              ->addColumn('tpl_src_title', 'text')
              ->addColumn('tpl_src_msg', 'text')
              ->addColumn('tpl_comment', 'text')
              ->addColumn('tpl_rules_post_id', 'integer', ['limit' => 10, 'signed' => false, 'default' => 0])
              ->addColumn('tpl_last_edit_tm', 'integer', ['limit' => 11, 'default' => 0])
              ->addColumn('tpl_last_edit_by', 'integer', ['limit' => 11, 'default' => 0])
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
        $table->addColumn('config_name', 'string', ['limit' => 155, 'default' => ''])
              ->addColumn('config_value', 'string', ['limit' => 255, 'default' => ''])
              ->create();

        // bb_attach_quota
        $table = $this->table('bb_attach_quota', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false
        ]);
        $table->addColumn('user_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('group_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
              ->addColumn('quota_type', 'integer', ['limit' => 2, 'default' => 0])
              ->addColumn('quota_limit_id', 'integer', ['limit' => 8, 'signed' => false, 'default' => 0])
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
              ->addColumn('quota_desc', 'string', ['limit' => 20, 'default' => ''])
              ->addColumn('quota_limit', 'biginteger', ['signed' => false, 'default' => 0])
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
        $table->addColumn('group_id', 'integer', ['limit' => 8, 'default' => 0])
              ->addColumn('forum_id', 'integer', ['limit' => 5, 'signed' => false, 'default' => 0])
              ->addColumn('forum_perm', 'integer', ['limit' => 11, 'default' => 0])
              ->addIndex('forum_id')
              ->create();

        // bb_auth_access_snap
        $table = $this->table('bb_auth_access_snap', [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['user_id', 'forum_id']
        ]);
        $table->addColumn('user_id', 'integer', ['limit' => 9, 'default' => 0])
              ->addColumn('forum_id', 'integer', ['limit' => 6, 'default' => 0])
              ->addColumn('forum_perm', 'integer', ['limit' => 11, 'default' => 0])
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