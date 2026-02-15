<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddInfoHashIndexToBtTorrents extends AbstractMigration
{
    public function change(): void
    {
        $this->table('bb_bt_torrents', ['id' => false, 'primary_key' => 'topic_id'])
            ->addIndex('info_hash')
            ->addIndex('info_hash_v2')
            ->update();
    }
}
