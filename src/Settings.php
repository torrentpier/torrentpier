<?php

namespace TorrentPier;

use TorrentPier\Db\Adapter as DbAdapter;
use TorrentPier\Cache\Adapter as CacheAdapter;

class Settings
{
    /** @var DbAdapter */
    protected $db;

    /** @var CacheAdapter */
    protected $cache;

    public function __construct(DbAdapter $db, CacheAdapter $cache)
    {
        $this->db = $db;
        $this->cache = $cache;
    }

    public function get($key)
    {
        return null;
    }
}
