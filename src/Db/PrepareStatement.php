<?php

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
