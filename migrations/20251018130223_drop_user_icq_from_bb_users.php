<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DropUserIcqFromBbUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->table('bb_users')
            ->removeColumn('user_icq')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->table('bb_users')
            ->addColumn('user_icq', 'string', [
                'limit' => 15,
                'default' => '',
                'null' => false
            ])
            ->save();
    }
}
