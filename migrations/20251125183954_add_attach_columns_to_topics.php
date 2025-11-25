<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddAttachColumnsToTopics extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $this->table('bb_topics')
            ->addColumn('attach_ext_id', 'integer', [
                'limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY,
                'signed' => false,
                'default' => 0,
                'null' => false,
                'after' => 'topic_attachment'
            ])
            ->addColumn('attach_filesize', 'biginteger', [
                'signed' => false,
                'default' => 0,
                'null' => false,
                'after' => 'attach_ext_id'
            ])
            ->update();
    }
}
