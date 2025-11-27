<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveBbExtensionsTable extends AbstractMigration
{
    /**
     * Remove bb_extensions table
     */
    public function up(): void
    {
        $this->table('bb_extensions')->drop()->save();
    }

    /**
     * Restore bb_extensions table with initial data
     */
    public function down(): void
    {
        $table = $this->table('bb_extensions', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'ext_id'
        ]);

        $table->addColumn('ext_id', 'integer', ['limit' => 16777215, 'signed' => false, 'identity' => true])
            ->addColumn('group_id', 'integer', ['limit' => 16777215, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('extension', 'string', ['limit' => 100, 'default' => '', 'null' => false])
            ->addColumn('comment', 'string', ['limit' => 100, 'default' => '', 'null' => false])
            ->create();

        // Restore initial seed data
        $extensions = [
            ['group_id' => 1, 'extension' => 'gif', 'comment' => ''],
            ['group_id' => 1, 'extension' => 'png', 'comment' => ''],
            ['group_id' => 1, 'extension' => 'jpeg', 'comment' => ''],
            ['group_id' => 1, 'extension' => 'jpg', 'comment' => ''],
            ['group_id' => 1, 'extension' => 'webp', 'comment' => ''],
            ['group_id' => 1, 'extension' => 'avif', 'comment' => ''],
            ['group_id' => 1, 'extension' => 'bmp', 'comment' => ''],
            ['group_id' => 2, 'extension' => 'gtar', 'comment' => ''],
            ['group_id' => 2, 'extension' => 'gz', 'comment' => ''],
            ['group_id' => 2, 'extension' => 'tar', 'comment' => ''],
            ['group_id' => 2, 'extension' => 'zip', 'comment' => ''],
            ['group_id' => 2, 'extension' => 'rar', 'comment' => ''],
            ['group_id' => 2, 'extension' => 'ace', 'comment' => ''],
            ['group_id' => 2, 'extension' => '7z', 'comment' => ''],
            ['group_id' => 3, 'extension' => 'txt', 'comment' => ''],
            ['group_id' => 3, 'extension' => 'c', 'comment' => ''],
            ['group_id' => 3, 'extension' => 'h', 'comment' => ''],
            ['group_id' => 3, 'extension' => 'cpp', 'comment' => ''],
            ['group_id' => 3, 'extension' => 'hpp', 'comment' => ''],
            ['group_id' => 3, 'extension' => 'diz', 'comment' => ''],
            ['group_id' => 3, 'extension' => 'm3u', 'comment' => ''],
            ['group_id' => 4, 'extension' => 'xls', 'comment' => ''],
            ['group_id' => 4, 'extension' => 'doc', 'comment' => ''],
            ['group_id' => 4, 'extension' => 'dot', 'comment' => ''],
            ['group_id' => 4, 'extension' => 'pdf', 'comment' => ''],
            ['group_id' => 4, 'extension' => 'ai', 'comment' => ''],
            ['group_id' => 4, 'extension' => 'ps', 'comment' => ''],
            ['group_id' => 4, 'extension' => 'ppt', 'comment' => ''],
            ['group_id' => 5, 'extension' => 'rm', 'comment' => ''],
            ['group_id' => 6, 'extension' => 'torrent', 'comment' => '']
        ];

        $this->table('bb_extensions')->insert($extensions)->saveData();
    }
}