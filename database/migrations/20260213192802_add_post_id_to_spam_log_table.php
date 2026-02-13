<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPostIdToSpamLogTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('bb_spam_log')
            ->addColumn('post_id', 'integer', [
                'signed' => false,
                'null' => true,
                'default' => null,
                'after' => 'check_time',
            ])
            ->addIndex('post_id')
            ->update();
    }
}
