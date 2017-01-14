<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace TorrentPier\Db;

use TorrentPier\Config;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Adapter\Profiler\Profiler;
use Zend\Db\Adapter\Profiler\ProfilerInterface;
use Zend\Db\Exception\InvalidArgumentException;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Where;

/**
 * Class Adapter
 * @package TorrentPier\Db
 */
class Adapter extends \Zend\Db\Adapter\Adapter
{
    /**
     * @var Entity|null
     */
    protected $resultWrapper;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        $driver,
        PlatformInterface $platform = null,
        ResultSetInterface $queryResultPrototype = null,
        ProfilerInterface $profiler = null
    ) {
        if ($driver instanceof Config) {
            $driver = $driver->toArray();
        }

        if ($profiler === null && is_array($driver) && !empty($driver['debug'])) {
            $profiler = new Profiler();
        }

        parent::__construct($driver, $platform, $queryResultPrototype, $profiler);
    }

    /**
     * Get sql object.
     *
     * @return Sql
     */
    protected function getSql()
    {
        return new Sql($this);
    }

    /**
     * Prepare table name.
     *
     * @param $table
     * @return mixed
     */
    protected function prepareTable($table)
    {
        $this->resultWrapper = null;

        if (is_string($table) && class_exists($table)) {
            $this->resultWrapper = new $table;
            $table = $this->resultWrapper->table();
        }

        return $table;
    }

    /**
     * Insert row to database.
     *
     * @param $table
     * @param array $values
     * @return mixed|null
     * @throws InvalidArgumentException
     */
    public function insert($table, array $values)
    {
        $table = $this->prepareTable($table);
        $sql = $this->getSql();

        /** @var Insert $sqlInsert */
        $sqlInsert = $sql->insert($table);
        $sqlInsert->values($values);

        $statementContainer = $sql->prepareStatementForSqlObject($sqlInsert);
        /** @var ResultInterface $result */
        $result = $statementContainer->execute();
        return $result->getGeneratedValue();
    }

    /**
     * Update rows in database.
     *
     * @param $table
     * @param array $values
     * @param Where|\Closure|string|array $where
     * @return int
     * @throws InvalidArgumentException
     */
    public function update($table, array $values, $where)
    {
        $table = $this->prepareTable($table);
        $sql = $this->getSql();

        /** @var Update $sqlUpdate */
        $sqlUpdate = $sql->update($table);
        $sqlUpdate->set($values);
        $sqlUpdate->where($where);

        $statementContainer = $sql->prepareStatementForSqlObject($sqlUpdate);
        /** @var ResultInterface $result */
        $result = $statementContainer->execute();
        return $result->getAffectedRows();
    }

    /**
     * Delete rows from database.
     *
     * @param string $table
     * @param array $where
     * @return int
     */
    public function delete($table, array $where)
    {
        $table = $this->prepareTable($table);
        $sql = $this->getSql();

        /** @var Delete $sqlDelete */
        $sqlDelete = $sql->delete($table);
        $sqlDelete->where($where);

        $statementContainer = $sql->prepareStatementForSqlObject($sqlDelete);
        /** @var ResultInterface $result */
        $result = $statementContainer->execute();
        return $result->getAffectedRows();
    }

    /**
     * Select rows from database.
     *
     * @param $table
     * @param null|\Closure|array $queryCallback
     * @return PrepareStatement
     * @throws InvalidArgumentException
     */
    public function select($table, $queryCallback = null)
    {
        $table = $this->prepareTable($table);
        $sql = $this->getSql();

        /** @var Select $sqlDelete */
        $sqlSelect = $sql->select($table);

        if ($queryCallback instanceof \Closure) {
            call_user_func($queryCallback, $sqlSelect);
        } elseif (is_array($queryCallback)) {
            $sqlSelect->where($queryCallback);
        }

        $statementContainer = $sql->prepareStatementForSqlObject($sqlSelect);
        return new PrepareStatement($statementContainer, $this->resultWrapper);
    }

    /**
     * Count rows in database.
     *
     * @param $table
     * @param null|\Closure|array $queryCallback
     * @return int
     * @throws InvalidArgumentException
     */
    public function count($table, $queryCallback = null)
    {
        $table = $this->prepareTable($table);
        $sql = $this->getSql();

        /** @var Select $sqlDelete */
        $sqlSelect = $sql->select($table);

        if ($queryCallback instanceof \Closure) {
            call_user_func($queryCallback, $sqlSelect);
        } elseif (is_array($queryCallback)) {
            $sqlSelect->where($queryCallback);
        }

        $sqlSelect->columns(['count' => new Expression('COUNT(*)')]);

        $statementContainer = $sql->prepareStatementForSqlObject($sqlSelect);
        /** @var ResultInterface $result */
        $result = $statementContainer->execute();
        return $result->current()['count'];
    }

    /**
     * Increment field by query.
     *
     * @param string $table
     * @param string $field
     * @param Where|\Closure|string|array $where
     * @param int $num
     * @return int
     * @throws InvalidArgumentException
     */
    public function increment($table, $field, $where = null, $num = 1)
    {
        return $this->update($table, [
            $field => new Expression('? + ?', [$field, $num], [Expression::TYPE_IDENTIFIER, Expression::TYPE_VALUE])
        ], $where);
    }

    /**
     * Decrement field by query.
     *
     * @param string $table
     * @param string $field
     * @param Where|\Closure|string|array $where
     * @param int $num
     * @return int
     * @throws InvalidArgumentException
     */
    public function decrement($table, $field, $where = null, $num = 1)
    {
        return $this->update($table, [
            $field => new Expression('? - ?', [$field, $num], [Expression::TYPE_IDENTIFIER, Expression::TYPE_VALUE])
        ], $where);
    }
}
