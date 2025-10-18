<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DropBbQuotaLimits extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if ($this->hasTable('bb_quota_limits')) {
            $this->table('bb_quota_limits')
                ->drop()
                ->save();
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $table = $this->table('bb_quota_limits', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'id' => false,
            'primary_key' => 'quota_limit_id'
        ]);
        $table->addColumn('quota_limit_id', 'integer', ['limit' => 16777215, 'signed' => false, 'identity' => true])
            ->addColumn('quota_desc', 'string', ['limit' => 20, 'default' => '', 'null' => false])
            ->addColumn('quota_limit', 'biginteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->save();
    }
}
