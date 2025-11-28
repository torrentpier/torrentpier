<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class CreateDownloadTracking extends AbstractMigration
{
    /**
     * Migrate Up - Create download tracking tables and add a download_count column.
     */
    public function up(): void
    {
        // Table for unique downloads (topic_id, user_id pairs)
        $this->table('bb_torrent_dl', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['topic_id', 'user_id']
        ])
            ->addColumn('topic_id', 'integer', [
                'limit' => MysqlAdapter::INT_MEDIUM,
                'signed' => false,
                'null' => false
            ])
            ->addColumn('user_id', 'integer', [
                'limit' => MysqlAdapter::INT_MEDIUM,
                'signed' => true,
                'null' => false
            ])
            ->create();

        // Table for daily download limits per user
        $this->table('bb_user_dl_day', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => ['user_id']
        ])
            ->addColumn('user_id', 'integer', [
                'limit' => MysqlAdapter::INT_MEDIUM,
                'signed' => true,
                'null' => false
            ])
            ->addColumn('cnt', 'integer', [
                'limit' => MysqlAdapter::INT_TINY,
                'signed' => false,
                'default' => 0,
                'null' => false
            ])
            ->create();

        // Add the download_count column to bb_topics
        $this->table('bb_topics')
            ->addColumn('download_count', 'integer', [
                'limit' => MysqlAdapter::INT_MEDIUM,
                'signed' => false,
                'default' => 0,
                'null' => false,
                'after' => 'attach_ext_id'
            ])
            ->update();

        // Register a cron job for daily aggregation
        $this->table('bb_cron')->insert([
            'cron_active' => 1,
            'cron_title' => 'Torrent download aggregation',
            'cron_script' => 'torrent_dl_aggregate.php',
            'schedule' => 'daily',
            'run_day' => null,
            'run_time' => '00:05:00',
            'run_order' => 50,
            'last_run' => '1900-01-01 00:00:00',
            'next_run' => '1900-01-01 00:00:00',
            'run_interval' => null,
            'log_enabled' => 0,
            'log_file' => '',
            'log_sql_queries' => 0,
            'disable_board' => 0,
            'run_counter' => 0,
            'execution_time' => 0.0
        ])->save();
    }

    /**
     * Migrate Down - Remove download tracking tables and column.
     */
    public function down(): void
    {
        // Remove cron job
        $this->execute("DELETE FROM bb_cron WHERE cron_script = 'torrent_dl_aggregate.php'");

        // Remove the download_count column from bb_topics
        $this->table('bb_topics')
            ->removeColumn('download_count')
            ->update();

        // Drop tables
        $this->table('bb_user_dl_day')->drop()->save();
        $this->table('bb_torrent_dl')->drop()->save();
    }
}
