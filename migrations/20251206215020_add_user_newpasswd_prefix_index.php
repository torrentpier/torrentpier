<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUserNewpasswdPrefixIndex extends AbstractMigration
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
        $this->table('bb_users')
            ->addIndex(['user_newpasswd'], ['name' => 'idx_user_newpasswd', 'limit' => ['user_newpasswd' => 1]])
            ->update();
    }
}
