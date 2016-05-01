<?php

namespace TorrentPier\Authentication\Adapter;

use TorrentPier\Db\Adapter;
use TorrentPier\Di;
use Zend\Authentication\Adapter\DbTable\CredentialTreatmentAdapter;

class HashDoubleMD5 extends CredentialTreatmentAdapter
{
    public function __construct()
    {
        /** @var Adapter $db */
        $db = Di::getInstance()->db;
        parent::__construct($db, 'bb_users', 'username', 'user_password', 'MD5(MD5(?))');
    }
}
