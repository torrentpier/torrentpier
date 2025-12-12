<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DropUserSkypeFromBbUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->table('bb_users')
            ->removeColumn('user_skype')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->table('bb_users')
            ->addColumn('user_skype', 'string', [
                'limit' => 32,
                'default' => '',
                'null' => false,
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci'
            ])
            ->save();
    }
}
