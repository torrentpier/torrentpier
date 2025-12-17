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

final class AddTimestampsToCategories extends AbstractMigration
{
    public function change(): void
    {
        $this->table('bb_categories')
            ->addColumn('created_at', 'timestamp', [
                'null' => true,
                'default' => null,
            ])
            ->addColumn('updated_at', 'timestamp', [
                'null' => true,
                'default' => null,
            ])
            ->update();
    }
}
