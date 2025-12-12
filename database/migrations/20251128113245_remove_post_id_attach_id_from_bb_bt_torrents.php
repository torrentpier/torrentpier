<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemovePostIdAttachIdFromBbBtTorrents extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $table = $this->table('bb_bt_torrents');

        // Remove unique indexes first
        $table->removeIndexByName('post_id')
            ->removeIndexByName('attach_id')
            ->save();

        // Remove columns
        $table->removeColumn('post_id')
            ->removeColumn('attach_id')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $table = $this->table('bb_bt_torrents');

        // Restore columns (MEDIUMINT UNSIGNED)
        $table->addColumn('post_id', 'integer', [
            'limit' => 16777215,
            'signed' => false,
            'default' => 0,
            'null' => false,
            'after' => 'info_hash_v2'
        ])
            ->addColumn('attach_id', 'integer', [
                'limit' => 16777215,
                'signed' => false,
                'default' => 0,
                'null' => false,
                'after' => 'forum_id'
            ])
            ->save();

        // Restore unique indexes
        $table->addIndex('post_id', ['unique' => true])
            ->addIndex('attach_id', ['unique' => true])
            ->save();
    }
}
