<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveBbExtensionGroupsTable extends AbstractMigration
{
    /**
     * Remove bb_extension_groups table
     */
    public function up(): void
    {
        $this->table('bb_extension_groups')->drop()->save();
    }

    /**
     * Restore bb_extension_groups table with initial data
     */
    public function down(): void
    {
        $table = $this->table('bb_extension_groups', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'group_id'
        ]);

        $table->addColumn('group_id', 'integer', ['limit' => 16777215, 'identity' => true])
            ->addColumn('group_name', 'string', ['limit' => 20, 'default' => '', 'null' => false])
            ->addColumn('cat_id', 'integer', ['limit' => 255, 'default' => 0, 'null' => false])
            ->addColumn('allow_group', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('download_mode', 'integer', ['limit' => 255, 'signed' => false, 'default' => 1, 'null' => false])
            ->addColumn('upload_icon', 'string', ['limit' => 100, 'default' => '', 'null' => false])
            ->addColumn('max_filesize', 'integer', ['limit' => 20, 'default' => 0, 'null' => false])
            ->addColumn('forum_permissions', 'text', ['null' => false])
            ->create();

        // Restore initial seed data
        $groups = [
            ['group_name' => 'Images', 'cat_id' => 1, 'allow_group' => 1, 'download_mode' => 1, 'upload_icon' => '', 'max_filesize' => 262144, 'forum_permissions' => ''],
            ['group_name' => 'Archives', 'cat_id' => 0, 'allow_group' => 1, 'download_mode' => 1, 'upload_icon' => '', 'max_filesize' => 262144, 'forum_permissions' => ''],
            ['group_name' => 'Plain text', 'cat_id' => 0, 'allow_group' => 1, 'download_mode' => 1, 'upload_icon' => '', 'max_filesize' => 262144, 'forum_permissions' => ''],
            ['group_name' => 'Documents', 'cat_id' => 0, 'allow_group' => 1, 'download_mode' => 1, 'upload_icon' => '', 'max_filesize' => 262144, 'forum_permissions' => ''],
            ['group_name' => 'Real media', 'cat_id' => 0, 'allow_group' => 0, 'download_mode' => 2, 'upload_icon' => '', 'max_filesize' => 262144, 'forum_permissions' => ''],
            ['group_name' => 'Torrent', 'cat_id' => 0, 'allow_group' => 1, 'download_mode' => 1, 'upload_icon' => '', 'max_filesize' => 262144, 'forum_permissions' => '']
        ];

        $this->table('bb_extension_groups')->insert($groups)->saveData();
    }
}