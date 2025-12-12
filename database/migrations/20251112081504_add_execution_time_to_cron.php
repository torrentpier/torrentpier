<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

use Phinx\Migration\AbstractMigration;

class AddExecutionTimeToCron extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('bb_cron');
        $table->addColumn('execution_time', 'float', [
            'precision' => 10,
            'scale' => 4,
            'default' => 0.0000,
            'null' => false,
            'after' => 'run_counter',
            'comment' => 'Last execution time in seconds'
        ])
        ->update();
    }

    public function down()
    {
        $table = $this->table('bb_cron');
        $table->removeColumn('execution_time')
            ->update();
    }
}
