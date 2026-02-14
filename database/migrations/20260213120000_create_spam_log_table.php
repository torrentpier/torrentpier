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

final class CreateSpamLogTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('bb_spam_log', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'log_id',
        ]);

        $table
            ->addColumn('log_id', 'integer', ['signed' => false, 'identity' => true])
            ->addColumn('check_type', 'string', ['limit' => 20, 'null' => false])
            ->addColumn('check_ip', 'string', ['limit' => 45, 'default' => '', 'null' => false])
            ->addColumn('check_email', 'string', ['limit' => 255, 'default' => '', 'null' => false])
            ->addColumn('check_username', 'string', ['limit' => 255, 'default' => '', 'null' => false])
            ->addColumn('decision', 'string', ['limit' => 20, 'null' => false])
            ->addColumn('provider_name', 'string', ['limit' => 100, 'default' => '', 'null' => false])
            ->addColumn('reason', 'string', ['limit' => 500, 'default' => '', 'null' => false])
            ->addColumn('details', 'text', ['null' => true])
            ->addColumn('total_time_ms', 'integer', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('check_time', 'integer', ['signed' => false, 'default' => 0, 'null' => false])
            ->addIndex('check_ip')
            ->addIndex('check_email')
            ->addIndex('check_time')
            ->addIndex('decision')
            ->create();
    }

    public function down(): void
    {
        $this->table('bb_spam_log')->drop()->save();
    }
}
