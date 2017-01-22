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

namespace TorrentPier;

use \PDO;
use \PDOException;
use \PDOStatement;
use Psr\Log\LoggerInterface;
use TorrentPier\Db\Exception;
use TorrentPier\Db\IntegrityViolationException;
use TorrentPier\Db\LogProcessor;

class Db extends PDO
{
    const DEFAULT_OPTIONS = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];

    public $type;
    /** @var LoggerInterface */
    public $logger;
    /** @var LogProcessor */
    public $logProcessor;
    public $isLogPrepare;
    public $isLogExplain;

    public function __construct(array $config)
    {
        $this->isLogPrepare = $config['isLogPrepare'] ?? false;
        $this->isLogExplain = $config['isLogExplain'] ?? true;
        $type = $this->type = $config['type'] ?? 'mysql';
        $options = $config['options'] ?? [];
        $hostname = $config['hostname'] ?? '127.0.0.1';
        $database = $config['database'] ?? null;
        $username = $config['username'] ?? null;
        $password = $config['password'] ?? null;
        if (is_callable($password)) {
            $password = $password();
        }
        $socket = $config['socket'] ?? null;
        $port = $config['port'] ?? null;
        $charset = $config['charset'] ?? null;

        $dsn = null;
        switch ($this->type) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'mariadb':
                $type = 'mysql';
            // no break

            case 'mysql':
                if (isset($socket)) {
                    if ($socket === true) {
                        $socket = ini_get('pdo_mysql.default_socket');
                    }
                    $dsn = "$type:unix_socket=$socket;dbname=$database";
                } else {
                    $dsn = "$type:host=$hostname" . (isset($port) ? ";port=$port" : '') . ";dbname=$database";
                }
                break;

            case 'pgsql':
                $dsn = "$type:host=$hostname" . (isset($port) ? ";port=$port" : '') . ";dbname=$database";
                break;

            case 'sybase':
                $dsn = "dblib:host=$hostname" . (isset($port) ? ":$port" : '') . ";dbname=$database";
                break;

            case 'oracle':
                $dbname = isset($hostname) ?
                    "//$hostname" . (isset($port) ? ":$port" : ':1521') . "/$database" :
                    $database;

                $dsn = "oci:dbname=$dbname" . (isset($charset) ? ";charset=$charset" : '');
                break;

            case 'mssql':
                $dsn = strstr(PHP_OS, 'WIN') ?
                    "sqlsrv:server=$hostname" . (isset($port) ? ",$port" : '') . ";database=$database" :
                    "dblib:host=$hostname" . (isset($port) ? ":$port" : '') . ";dbname=$database";
                break;

            case 'sqlite':
                $dsn = "$type:$database";
                $username = null;
                $password = null;
                break;

            case 'default':
                throw new PDOException("Unknown database driver");
        }
        $dsn .= ";charset=utf8";

        parent::__construct(
            $dsn,
            $username,
            is_callable($password) ? $password() : $password,
            array_replace_recursive(
                static::DEFAULT_OPTIONS,
                $options
            )
        );
        $this->setAttribute(static::ATTR_STATEMENT_CLASS, ['\\TorrentPier\\Db\\Statement', [$this]]);
    }

    public function prepare($statement, /** @noinspection PhpSignatureMismatchDuringInheritanceInspection */
                            $options = [])
    {
        try {
            if (isset($this->logger) && $this->isLogPrepare) {
                $t = microtime(true);
            }
            return parent::prepare($statement, $options);
        } catch (PDOException $e) {
            throw new Exception($e);
        } finally {
            if (isset($t)) {
                $this->logger->debug($statement, ['time' => microtime(true) - $t, 'prepare' => true]);
            }
        }
    }

    /** @noinspection PhpSignatureMismatchDuringInheritanceInspection */
    /**
     * @param string $statement
     * @param array ...$args
     * @return PDOStatement
     */
    public function query($statement, ...$args)
    {
        try {
            if (func_num_args() > 1) {
                $input = func_get_arg(1);
                if (is_array($input)) {
                    $stmt = $this->prepare($statement);
                    if (func_num_args() > 2) {
                        $stmt->setFetchMode(...array_slice(func_get_args(), 2));
                    }
                    $stmt->execute($input);
                    return $stmt;
                }
            }
            if (isset($this->logger) && strncasecmp($statement, 'EXPLAIN', 7)) {
                $t = microtime(true);
            }
            return parent::query($statement, ...$args);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                throw new IntegrityViolationException($e);
            }
            throw new Exception($e);
        } finally {
            if (isset($t)) {
                $context = ['time' => microtime(true) - $t];
                if ($this->isLogExplain && preg_match('#^s*SELECTs#i', $statement)) {
                    $context['explain'] = $this->explain($statement);
                }
                $this->logger->debug($statement, $context);
            }
        }
    }

    public function explain($statement, $input = null)
    {
        try {
            $stmt = parent::prepare("EXPLAIN $statement");
            $stmt->execute($input);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception($e);
        }
    }

    public function exec($statement)
    {
        try {
            if (isset($this->logger) && strncasecmp($statement, 'EXPLAIN', 7)) {
                $t = microtime(true);
            }
            return parent::exec($statement);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                throw new IntegrityViolationException($e);
            }
            throw new Exception($e);
        } finally {
            if (isset($t)) {
                $this->logger->debug($statement, ['time' => microtime(true) - $t]);
            }
        }
    }

    /** @deprecated
     * @param $statement
     * @return mixed
     */
    public function fetch_row($statement)
    {
        return $this->sql_fetchrow($this->sql_query($statement));
    }

    /** @deprecated
     * @param $statement
     * @return array
     */
    public function fetch_rowset($statement)
    {
        return $this->sql_fetchrowset($this->sql_query($statement));
    }

    /** @deprecated
     * @param PDOStatement $statement
     * @return array
     */
    public function sql_fetchrow(PDOStatement $statement)
    {
        return $statement->fetch();
    }

    /** @deprecated
     * @param PDOStatement $statement
     * @return array
     */
    public function sql_fetchrowset(PDOStatement $statement)
    {
        return $statement->fetchAll();
    }

    /** @deprecated
     * @param $statement
     * @return \PDOStatement
     */
    public function sql_query($statement)
    {
        if (is_array($statement)) {
            $statement = $this->build_sql($statement);
        }
        return $this->query($statement);
    }

    /**
     * @deprecated
     * Escape data used in sql query
     * @param $v
     * @param bool $check_type
     * @param bool $dont_escape
     * @return string
     */
    public function escape($v, $check_type = false, $dont_escape = false)
    {
        if ($dont_escape) {
            return $v;
        }
        if (!$check_type) {
            return $this->quote($v);
        }

        switch (true) {
            case is_string($v):
                return $this->quote($v);
            case is_int($v):
                return "$v";
            case is_bool($v):
                return ($v) ? '1' : '0';
            case is_float($v):
                return "'$v'";
            case is_null($v):
                return 'NULL';
        }
        // if $v has unsuitable type
        throw new PDOException(__FUNCTION__ . ' - wrong params');
    }

    /**
     * @deprecated
     * Return number of rows
     * @param PDOStatement $result
     * @return int
     */
    public function num_rows(PDOStatement $result)
    {
        return $result->rowCount();
    }

    /** @deprecated
     * @param $sql_ary
     * @return string
     */
    public function build_sql($sql_ary)
    {
        $sql = '';
        array_deep($sql_ary, 'array_unique', false, true);

        foreach ($sql_ary as $clause => $ary) {
            switch ($clause) {
                case 'SELECT':
                    $sql .= ($ary) ? ' SELECT ' . join(' ', $sql_ary['select_options']) . ' ' . join(', ', $ary) : '';
                    break;
                case 'FROM':
                    $sql .= ($ary) ? ' FROM ' . join(', ', $ary) : '';
                    break;
                case 'INNER JOIN':
                    $sql .= ($ary) ? ' INNER JOIN ' . join(' INNER JOIN ', $ary) : '';
                    break;
                case 'LEFT JOIN':
                    $sql .= ($ary) ? ' LEFT JOIN ' . join(' LEFT JOIN ', $ary) : '';
                    break;
                case 'WHERE':
                    $sql .= ($ary) ? ' WHERE ' . join(' AND ', $ary) : '';
                    break;
                case 'GROUP BY':
                    $sql .= ($ary) ? ' GROUP BY ' . join(', ', $ary) : '';
                    break;
                case 'HAVING':
                    $sql .= ($ary) ? ' HAVING ' . join(' AND ', $ary) : '';
                    break;
                case 'ORDER BY':
                    $sql .= ($ary) ? ' ORDER BY ' . join(', ', $ary) : '';
                    break;
                case 'LIMIT':
                    $sql .= ($ary) ? ' LIMIT ' . join(', ', $ary) : '';
                    break;
            }
        }

        return trim($sql);
    }

    /** @deprecated */
    public function get_empty_sql_array()
    {
        return [
            'SELECT' => [],
            'select_options' => [],
            'FROM' => [],
            'INNER JOIN' => [],
            'LEFT JOIN' => [],
            'WHERE' => [],
            'GROUP BY' => [],
            'HAVING' => [],
            'ORDER BY' => [],
            'LIMIT' => []
        ];
    }
}
