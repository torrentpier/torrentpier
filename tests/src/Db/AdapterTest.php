<?php

namespace TorrentPier\Db;

use Zend\Db\Adapter\Driver\Pdo\Result;
use Zend\Db\Adapter\Driver\Pdo\Statement;
use Zend\Db\Adapter\Driver\Pdo\Connection;
use Zend\Db\Adapter\Driver\Pdo\Pdo;
use Zend\Db\Adapter\Platform\AbstractPlatform;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;

class AdapterTest extends \PHPUnit_Framework_TestCase
{
    const AFFECTED_ROWS = 2;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Adapter
     */
    protected $adapterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Sql
     */
    protected $sqlMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Statement
     */
    protected $statementMock;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $resultMock = $this->getMock(Result::class, ['getGeneratedValue', 'getAffectedRows']);
        $resultMock->method('getGeneratedValue')->willReturn(self::AFFECTED_ROWS);
        $resultMock->method('getAffectedRows')->willReturn(self::AFFECTED_ROWS);

        $this->statementMock = $this->getMock(Statement::class, ['execute']);
        $this->statementMock->method('execute')->willReturn($resultMock);

        $this->adapterMock = $this->getMockBuilder(Adapter::class)
            ->setMethods(['getSql', 'getPlatform', 'getDriver'])
            ->disableOriginalConstructor()
            ->getMock();

        $platformMock = $this->getMock(AbstractPlatform::class, ['getName', 'quoteIdentifier', 'quoteValue']);
        $platformMock->method('quoteIdentifier')->willReturnCallback(function($v) { return '`' . $v . '`'; });
        $platformMock->method('quoteValue')->willReturnCallback(function($v) {
            if (is_int($v)) {
                return $v;
            } elseif ($v instanceof \DateTime) {
                $v = $v->format('Y-m-d H:i:s');
            }

            return '\'' . $v . '\'';
        });
        $platformMock->method('getName')->willReturn('platform name');
        $this->adapterMock->method('getPlatform')->willReturn($platformMock);

        $connectionMock = $this->getMock(Connection::class);

        $driverMock = $this->getMock(Pdo::class, [], [
            $connectionMock,
            $this->statementMock,
            $resultMock,
        ]);
        $this->adapterMock->method('getDriver')->willReturn($driverMock);

        $this->sqlMock = $this->getMockBuilder(Sql::class)
            ->setMethods(['prepareStatementForSqlObject'])
            ->setConstructorArgs([$this->adapterMock])
            ->getMock();
        $this->adapterMock->method('getSql')->willReturn($this->sqlMock);
    }

    /**
     * Create sql insert query.
     * @covers \TorrentPier\Db\Adapter::insert
     */
    public function testInsert()
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));

        $this->sqlMock->expects(static::once())
            ->method('prepareStatementForSqlObject')
            ->willReturnCallback(function (Insert $sqlObject) use ($date) {
                static::assertEquals(
                    join(' ', [
                        "INSERT INTO",
                        "`tableName` (`field_int`, `field_str`, `field_date`)",
                        "VALUES (123, 'string', '{$date->format('Y-m-d H:i:s')}')",
                    ]),
                    $this->sqlMock->buildSqlString($sqlObject)
                );
                return $this->statementMock;
            });

        $actual = $this->adapterMock->insert('tableName', [
            'field_int' => 123,
            'field_str' => 'string',
            'field_date' => $date,
        ]);

        static::assertEquals(self::AFFECTED_ROWS, $actual);
    }

    /**
     * Create sql update query.
     * @covers \TorrentPier\Db\Adapter::update
     */
    public function testUpdate()
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));

        $this->sqlMock->expects(static::once())
            ->method('prepareStatementForSqlObject')
            ->willReturnCallback(function (Update $sqlObject) use ($date) {
                static::assertEquals(
                    join(' ', [
                        "UPDATE `tableName` SET",
                        "`field_int` = 123, `field_str` = 'string', `field_date` = '{$date->format('Y-m-d H:i:s')}'",
                        "WHERE \"field_id\" = 1"
                    ]),
                    $this->sqlMock->buildSqlString($sqlObject)
                );
                return $this->statementMock;
            });

        $actual = $this->adapterMock->update('tableName', [
            'field_int' => 123,
            'field_str' => 'string',
            'field_date' => $date,
        ], [
            'field_id' => 1
        ]);

        static::assertEquals(self::AFFECTED_ROWS, $actual);
    }

    /**
     * Create sql delete query.
     * @covers \TorrentPier\Db\Adapter::delete
     */
    public function testDelete()
    {
        $this->sqlMock->expects(static::once())
            ->method('prepareStatementForSqlObject')
            ->willReturnCallback(function (Delete $sqlObject) {
                static::assertEquals(
                    "DELETE FROM `tableName` WHERE \"field_id\" = 1",
                    $this->sqlMock->buildSqlString($sqlObject)
                );
                return $this->statementMock;
            });

        $actual = $this->adapterMock->delete('tableName', [
            'field_id' => 1
        ]);

        static::assertEquals(self::AFFECTED_ROWS, $actual);
    }

    /**
     * Create sql increment query.
     * @covers \TorrentPier\Db\Adapter::increment
     */
    public function testIncrement()
    {
        $this->sqlMock->expects(static::once())
            ->method('prepareStatementForSqlObject')
            ->willReturnCallback(function (Update $sqlObject) {
                static::assertEquals(
                    "UPDATE `tableName` SET `inc` = \"inc\" + 1 WHERE \"field_id\" = 1",
                    $this->sqlMock->buildSqlString($sqlObject)
                );
                return $this->statementMock;
            });

        $actual = $this->adapterMock->increment('tableName', 'inc', ['field_id' => 1]);

        static::assertEquals(self::AFFECTED_ROWS, $actual);
    }

    /**
     * Create sql decrement query.
     * @covers \TorrentPier\Db\Adapter::decrement
     */
    public function testDecrement()
    {
        $this->sqlMock->expects(static::once())
            ->method('prepareStatementForSqlObject')
            ->willReturnCallback(function (Update $sqlObject) {
                static::assertEquals(
                    "UPDATE `tableName` SET `inc` = \"inc\" - 1 WHERE \"field_id\" = 1",
                    $this->sqlMock->buildSqlString($sqlObject)
                );
                return $this->statementMock;
            });

        $actual = $this->adapterMock->decrement('tableName', 'inc', ['field_id' => 1]);

        static::assertEquals(self::AFFECTED_ROWS, $actual);
    }
}
