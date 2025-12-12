<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MakeInfoHashColumnsNullable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('bb_bt_torrents');

        // First, make columns nullable
        $table->changeColumn('info_hash', 'varbinary', [
            'limit' => 20,
            'null' => true,
            'default' => null,
        ]);

        $table->changeColumn('info_hash_v2', 'varbinary', [
            'limit' => 32,
            'null' => true,
            'default' => null,
        ]);

        $table->update();

        // Then, convert empty strings to NULL
        $this->execute("UPDATE bb_bt_torrents SET info_hash = NULL WHERE info_hash = ''");
        $this->execute("UPDATE bb_bt_torrents SET info_hash_v2 = NULL WHERE info_hash_v2 = ''");
    }

    public function down(): void
    {
        // First, convert NULLs back to empty strings
        $this->execute("UPDATE bb_bt_torrents SET info_hash = '' WHERE info_hash IS NULL");
        $this->execute("UPDATE bb_bt_torrents SET info_hash_v2 = '' WHERE info_hash_v2 IS NULL");

        $table = $this->table('bb_bt_torrents');

        $table->changeColumn('info_hash', 'varbinary', [
            'limit' => 20,
            'null' => false,
            'default' => '',
        ]);

        $table->changeColumn('info_hash_v2', 'varbinary', [
            'limit' => 32,
            'null' => false,
            'default' => '',
        ]);

        $table->update();
    }
}
