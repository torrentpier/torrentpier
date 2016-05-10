<?php

namespace TorrentPier\Authentication\Adapter;

use TorrentPier\Db\Adapter;
use TorrentPier\Di;
use Zend\Authentication\Adapter\DbTable\CallbackCheckAdapter;
use Zend\Db\Sql;

class HashPassword extends CallbackCheckAdapter
{
    public function __construct()
    {
        /** @var Adapter $db */
        $db = Di::getInstance()->db;
        parent::__construct($db, 'bb_users', 'username', 'user_password', [$this, 'credentialValidation']);
    }

    /**
     * @param string $hash
     * @param string $password
     * @return boolean
     */
    public function credentialValidation($hash, $password)
    {
        return password_verify($password, $hash);
    }
}
