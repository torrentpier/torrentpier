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

final class AddActkeyTimeToUsers extends AbstractMigration
{
    public function up(): void
    {
        $this->table('bb_users')
            ->addColumn('user_actkey_time', 'integer', [
                'default' => 0,
                'null' => false,
                'after' => 'user_actkey',
            ])
            ->update();
    }

    public function down(): void
    {
        $this->table('bb_users')
            ->removeColumn('user_actkey_time')
            ->update();
    }
}
