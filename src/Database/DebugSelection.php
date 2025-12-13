<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Database;

use Exception;
use Nette\Database\Table\Selection;
use ReflectionClass;

/**
 * DebugSelection - Wraps Nette Database Selection to provide debug logging and explain functionality
 */
class DebugSelection
{
    private Selection $selection;

    private Database $db;

    public function __construct(Selection $selection, Database $db)
    {
        $this->selection = $selection;
        $this->db = $db;
    }

    /**
     * Magic method to delegate calls to the wrapped Selection
     */
    public function __call(string $name, array $arguments)
    {
        $result = $this->selection->{$name}(...$arguments);

        // If result is another Selection, wrap it too
        if ($result instanceof Selection) {
            return new self($result, $this->db);
        }

        return $result;
    }

    /**
     * Magic method to delegate property access
     */
    public function __get(string $name)
    {
        return $this->selection->{$name};
    }

    /**
     * Magic method to delegate property setting
     */
    public function __set(string $name, $value): void
    {
        $this->selection->{$name} = $value;
    }

    /**
     * Magic method to check if property is set
     */
    public function __isset(string $name): bool
    {
        return isset($this->selection->{$name});
    }

    // Delegate common Selection methods with logging
    public function where(...$args): self
    {
        return new self($this->selection->where(...$args), $this->db);
    }

    public function order(...$args): self
    {
        return new self($this->selection->order(...$args), $this->db);
    }

    public function select(...$args): self
    {
        return new self($this->selection->select(...$args), $this->db);
    }

    public function limit(...$args): self
    {
        return new self($this->selection->limit(...$args), $this->db);
    }

    public function fetch()
    {
        $this->logQuery('fetch', []);
        $result = $this->selection->fetch();
        $this->completeQueryLogging();

        return $result;
    }

    public function fetchAll(): array
    {
        $this->logQuery('fetchAll', []);
        $result = $this->selection->fetchAll();
        $this->completeQueryLogging();

        return $result;
    }

    public function insert($data)
    {
        $this->logQuery('insert', [$data]);
        $result = $this->selection->insert($data);
        $this->completeQueryLogging();

        return $result;
    }

    public function update($data): int
    {
        $this->logQuery('update', [$data]);
        $result = $this->selection->update($data);
        $this->completeQueryLogging();

        return $result;
    }

    public function delete(): int
    {
        $this->logQuery('delete', []);
        $result = $this->selection->delete();
        $this->completeQueryLogging();

        return $result;
    }

    public function count(?string $column = null): int
    {
        $this->logQuery('count', [$column]);
        $result = $this->selection->count($column);
        $this->completeQueryLogging();

        return $result;
    }

    public function aggregation(string $function): mixed
    {
        $this->logQuery('aggregation', [$function]);
        $result = $this->selection->aggregation($function);
        $this->completeQueryLogging();

        return $result;
    }

    /**
     * Fetch pairs as an associative array.
     *
     * @param string|int|null $key Column for keys (or null for numeric index)
     * @param string|int|null $value Column for values (or null for entire row)
     * @return array
     */
    public function fetchPairs(string|int|null $key = null, string|int|null $value = null): array
    {
        $this->logQuery('fetchPairs', [$key, $value]);
        $result = $this->selection->fetchPairs($key, $value);
        $this->completeQueryLogging();

        return $result;
    }

    /**
     * Log query execution for debug panel
     */
    private function logQuery(string $method, array $arguments): void
    {
        if (!\defined('SQL_DEBUG') || !SQL_DEBUG) {
            return;
        }

        // Use the actual SQL with substituted parameters for both logging and EXPLAIN
        $sql = $this->generateSqlForLogging($method, $arguments, false);

        // Mark this query as coming from Nette Explorer
        $this->db->debugger->markAsNetteExplorerQuery();

        // Set the query for debug logging
        $this->db->cur_query = $sql;
        $this->db->debug('start');
    }

    /**
     * Complete query logging after execution
     */
    private function completeQueryLogging(): void
    {
        if (!\defined('SQL_DEBUG') || !SQL_DEBUG) {
            return;
        }

        // Note: explain('stop') is automatically called by debug('stop') when do_explain is true
        $this->db->debug('stop');
    }

    /**
     * Generate SQL representation for logging and EXPLAIN
     */
    private function generateSqlForLogging(string $method, array $arguments, bool $useRawSQL = true): string
    {
        // For SELECT operations, try to get the SQL from Nette
        if (\in_array($method, ['fetch', 'fetchAll', 'count', 'aggregation'], true)) {
            $sql = $useRawSQL ? $this->getSqlFromSelection() : $this->getSqlFromSelection(true);

            // Modify the SQL based on the method
            switch ($method) {
                case 'fetch':
                    // If it doesn't already have LIMIT, add it
                    if (!preg_match('/LIMIT\s+\d+/i', $sql)) {
                        $sql .= ' LIMIT 1';
                    }

                    return $sql;
                case 'count':
                    // Replace SELECT * with SELECT COUNT(*) or COUNT(column)
                    $countExpr = isset($arguments[0]) && $arguments[0] !== null
                        ? 'SELECT COUNT(' . $arguments[0] . ')'
                        : 'SELECT COUNT(*)';

                    return preg_replace('/^SELECT\s+\*/i', $countExpr, $sql);
                case 'aggregation':
                    // Replace SELECT * with SELECT {aggregation_function}
                    $aggFunc = $arguments[0] ?? 'COUNT(*)';

                    return preg_replace('/^SELECT\s+\*/i', 'SELECT ' . $aggFunc, $sql);
                case 'fetchAll':
                default:
                    return $sql;
            }
        }

        // For INSERT/UPDATE/DELETE, generate appropriate SQL
        $tableName = $this->selection->getName();

        return match ($method) {
            'insert' => $this->generateInsertSql($tableName, $arguments),
            'update' => $this->generateUpdateSql($tableName, $arguments, $useRawSQL),
            'delete' => $this->generateDeleteSql($tableName, $useRawSQL),
            default => "-- Explorer method: {$method} on {$tableName}"
        };
    }

    /**
     * Generate INSERT SQL statement
     */
    private function generateInsertSql(string $tableName, array $arguments): string
    {
        if (!isset($arguments[0]) || !\is_array($arguments[0])) {
            return "INSERT INTO {$tableName} (...) VALUES (...)";
        }

        $data = $arguments[0];
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_map(
            static fn ($v) => \is_string($v) ? "'{$v}'" : $v,
            array_values($data),
        ));

        return "INSERT INTO {$tableName} ({$columns}) VALUES ({$values})";
    }

    /**
     * Generate UPDATE SQL statement
     */
    private function generateUpdateSql(string $tableName, array $arguments, bool $useRawSQL): string
    {
        $setPairs = [];
        if (isset($arguments[0]) && \is_array($arguments[0])) {
            foreach ($arguments[0] as $key => $value) {
                $setPairs[] = "{$key} = " . (\is_string($value) ? "'{$value}'" : $value);
            }
        }

        $setClause = !empty($setPairs) ? implode(', ', $setPairs) : '...';
        $sql = $this->getSqlFromSelection(!$useRawSQL);

        // Extract WHERE clause from the SQL
        if (preg_match('/WHERE\s+(.+?)(?:\s+ORDER\s+BY|\s+LIMIT|\s+GROUP\s+BY|$)/i', $sql, $matches)) {
            return "UPDATE {$tableName} SET {$setClause} WHERE " . trim($matches[1]);
        }

        return "UPDATE {$tableName} SET {$setClause}";
    }

    /**
     * Generate DELETE SQL statement
     */
    private function generateDeleteSql(string $tableName, bool $useRawSQL): string
    {
        $sql = $this->getSqlFromSelection(!$useRawSQL);

        // Extract WHERE clause from the SQL
        if (preg_match('/WHERE\s+(.+?)(?:\s+ORDER\s+BY|\s+LIMIT|\s+GROUP\s+BY|$)/i', $sql, $matches)) {
            return "DELETE FROM {$tableName} WHERE " . trim($matches[1]);
        }

        return "DELETE FROM {$tableName}";
    }

    /**
     * Get SQL from Nette Selection with optional parameter substitution
     */
    private function getSqlFromSelection(bool $replaceParameters = false): string
    {
        try {
            $reflectionClass = new ReflectionClass($this->selection);
            $sql = '';

            // Try getSql() method first
            if ($reflectionClass->hasMethod('getSql')) {
                $getSqlMethod = $reflectionClass->getMethod('getSql');
                $getSqlMethod->setAccessible(true);
                $sql = $getSqlMethod->invoke($this->selection);
            } else {
                // Try __toString() method as fallback
                $sql = (string)$this->selection;
            }

            // For EXPLAIN to work, we need to replace ? with actual values
            if ($replaceParameters) {
                $sql = preg_replace('/\?/', '1', $sql);
            }

            return $sql;
        } catch (Exception $e) {
            // Fall back to simple representation
            return 'SELECT * FROM ' . $this->selection->getName() . ' WHERE 1=1';
        }
    }
}
