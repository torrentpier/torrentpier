<?php

namespace TorrentPier\Db;

abstract class Entity
{
    protected $table;

    public function table()
    {
        return $this->table;
    }
}
