<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2016 TorrentPier
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

use Zend\Db\Adapter\Driver\StatementInterface;
use Zend\Db\Exception\InvalidArgumentException;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\ResultSet\ResultSet;
use Zend\Hydrator\Reflection;

class PrepareStatement
{
    /**
     * @var StatementInterface
     */
    protected $statementContainer;

    /**
     * @var Entity
     */
    protected $resultWrapper;

    /**
     * PrepareStatement constructor.
     *
     * @param StatementInterface $statementContainer
     * @param Entity|null $resultWrapper
     */
    public function __construct(StatementInterface $statementContainer, Entity $resultWrapper = null)
    {
        $this->statementContainer = $statementContainer;
        $this->resultWrapper = $resultWrapper;
    }

    /**
     * Executing a query and wrapping a result.
     *
     * @return HydratingResultSet|ResultSet
     * @throws InvalidArgumentException
     */
    protected function execute()
    {
        $result = $this->statementContainer->execute();

        if ($this->resultWrapper) {
            $entityClass = $this->resultWrapper;
            $resultSet = new HydratingResultSet(new Reflection(), new $entityClass);
            $resultSet->initialize($result);
        } else {
            $resultSet = new ResultSet();
            $resultSet->initialize($result);
        }

        return $resultSet;
    }

    /**
     * Current result.
     *
     * @return array|null
     * @throws InvalidArgumentException
     */
    public function one()
    {
        return $this->execute()->current() ?: null;
    }

    /**
     * All results.
     *
     * @return HydratingResultSet|ResultSet
     * @throws InvalidArgumentException
     */
    public function all()
    {
        return $this->execute();
    }
}
