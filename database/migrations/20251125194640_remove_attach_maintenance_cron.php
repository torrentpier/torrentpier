<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

use Phinx\Migration\AbstractMigration;

class RemoveAttachMaintenanceCron extends AbstractMigration
{
    public function up()
    {
        $this->execute("DELETE FROM bb_cron WHERE cron_script = 'attach_maintenance.php'");
    }

    public function down()
    {
        $this->execute("
            INSERT INTO bb_cron (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, last_run, next_run, run_interval, log_enabled, log_file, log_sql_queries, disable_board, run_counter, execution_time)
            VALUES (1, 'Attach maintenance', 'attach_maintenance.php', 'daily', NULL, '05:00:00', 40, '1900-01-01 00:00:00', '1900-01-01 00:00:00', NULL, 0, '', 0, 1, 0, 0.0000)
        ");
    }
}
