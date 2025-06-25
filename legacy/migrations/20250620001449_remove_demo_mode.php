<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveDemoMode extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        // Delete the demo_mode.php cron job from bb_cron table
        $this->table('bb_cron')
            ->getAdapter()
            ->execute("DELETE FROM bb_cron WHERE cron_script = 'demo_mode.php'");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        // Restore the demo_mode.php cron job to bb_cron table
        $this->table('bb_cron')->insert([
            'cron_active'     => 1,
            'cron_title'      => 'Demo mode',
            'cron_script'     => 'demo_mode.php',
            'schedule'        => 'daily',
            'run_day'         => null,
            'run_time'        => '05:00:00',
            'run_order'       => 255,
            'last_run'        => '1900-01-01 00:00:00',
            'next_run'        => '1900-01-01 00:00:00',
            'run_interval'    => null,
            'log_enabled'     => 1,
            'log_file'        => 'demo_mode_cron',
            'log_sql_queries' => 1,
            'disable_board'   => 1,
            'run_counter'     => 0,
        ])->save();
    }
}
