<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveAttachIdFromBbBtTorrents extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('bb_bt_torrents');

        if ($table->hasColumn('attach_id')) {
            $table->removeColumn('attach_id')->update();
        }
    }

    public function down(): void
    {
        $table = $this->table('bb_bt_torrents');

        if (!$table->hasColumn('attach_id')) {
            $table->addColumn('attach_id', 'integer', [
                'signed' => false,
                'default' => 0,
                'after' => 'topic_id',
            ])->update();
        }
    }
}
