<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DropBbAttachQuota extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->table('bb_attach_quota')
            ->drop()
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $table = $this->table('bb_attach_quota', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false
        ]);
        $table->addColumn('user_id', 'integer', ['limit' => 16777215, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('group_id', 'integer', ['limit' => 16777215, 'signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('quota_type', 'integer', ['limit' => 65535, 'default' => 0, 'null' => false])
            ->addColumn('quota_limit_id', 'integer', ['limit' => 16777215, 'signed' => false, 'default' => 0, 'null' => false])
            ->addIndex('quota_type')
            ->save();
    }
}
