<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUserIdToSpamLogTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('bb_spam_log')
            ->addColumn('user_id', 'integer', [
                'signed' => false,
                'null' => true,
                'default' => null,
                'after' => 'post_id',
            ])
            ->addIndex('user_id')
            ->update();
    }
}
