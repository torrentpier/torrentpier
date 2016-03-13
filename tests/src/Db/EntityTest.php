<?php

namespace TorrentPier\Db;

class EntityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers TorrentPier\Db\Entity::table
     */
    public function testGetTableName()
    {
        $model = new Model();
        static::assertEquals('table name', $model->table());
    }
}

class Model extends Entity
{
    protected $table = 'table name';
}
