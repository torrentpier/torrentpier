<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Database;

use Nette\Database\Connection;
use Nette\Database\Conventions\DiscoveredConventions;
use Nette\Database\Explorer;
use Nette\Database\ResultSet;
use Nette\Database\Structure;

/**
 * Modern Database class using Nette Database with backward compatibility
 * Implements singleton pattern while maintaining all existing SqlDb methods
 */
class Database
{
    private static ?Database $instance = null;
    private static array $instances = [];

    public ?Connection $connection = null;
    private ?Explorer $explorer = null;
    private ?ResultSet $result = null;
    public ?DatabaseDebugger $debugger = null;

    // Configuration
    public array $cfg = [];
    public array $cfg_keys = ['dbhost', 'dbport', 'dbname', 'dbuser', 'dbpasswd', 'charset', 'persist'];
    public string $db_server = '';
    public ?string $selected_db = null;
    public bool $inited = false;
    public string $engine = 'MySQL';

    // Locking
    public bool $locked = false;
    public array $locks = [];

    // Statistics and debugging
    public int $num_queries = 0;
    private int $last_affected_rows = 0;
    public float $sql_starttime = 0;
    public float $sql_inittime = 0;
    public float $sql_timetotal = 0;
    public float $cur_query_time = 0;
    public ?string $cur_query = null;
    public ?string $last_query = null; // Store last executed query for error reporting

    public array $shutdown = [];
    public array $DBS = [];

    /**
     * Private constructor for singleton pattern
     */
    private function __construct(array $cfg_values, string $server_name = 'db')
    {
        $this->cfg = array_combine($this->cfg_keys, $cfg_values);
        $this->db_server = $server_name;

        // Initialize debugger
        $this->debugger = new DatabaseDebugger($this);

        // Initialize our own tracking system (replaces the old $DBS global system)
        $this->DBS = [
            'log_file' => 'sql_queries',
            'log_counter' => 0,
            'num_queries' => 0,
            'sql_inittime' => 0,
            'sql_timetotal' => 0
        ];
    }

    /**
     * Get singleton instance for default database
     */
    public static function getInstance(?array $cfg_values = null, string $server_name = 'db'): self
    {
        if (self::$instance === null && $cfg_values !== null) {
            self::$instance = new self($cfg_values, $server_name);
            self::$instances[$server_name] = self::$instance;
        }

        return self::$instance;
    }

    /**
     * Get instance for specific database server
     */
    public static function getServerInstance(array $cfg_values, string $server_name): self
    {
        if (!isset(self::$instances[$server_name])) {
            self::$instances[$server_name] = new self($cfg_values, $server_name);

            // If this is the first instance, set as default
            if (self::$instance === null) {
                self::$instance = self::$instances[$server_name];
            }
        }

        return self::$instances[$server_name];
    }

    /**
     * Initialize connection
     */
    public function init(): void
    {
        if (!$this->inited) {
            $this->connect();
            $this->inited = true;
            $this->num_queries = 0;
            $this->sql_inittime = $this->sql_timetotal;

            $this->DBS['sql_inittime'] += $this->sql_inittime;
        }
    }

    /**
     * Open connection using Nette Database
     */
    public function connect(): void
    {
        $this->cur_query = $this->debugger->dbg_enabled ? "connect to: {$this->cfg['dbhost']}:{$this->cfg['dbport']}" : 'connect';
        $this->debugger->debug('start');

        // Build DSN
        $dsn = "mysql:host={$this->cfg['dbhost']};port={$this->cfg['dbport']};dbname={$this->cfg['dbname']}";
        if (!empty($this->cfg['charset'])) {
            $dsn .= ";charset={$this->cfg['charset']}";
        }

        // Create Nette Database connection
        $this->connection = new Connection(
            $dsn,
            $this->cfg['dbuser'],
            $this->cfg['dbpasswd']
        );

        // Create Nette Database Explorer with all required dependencies
        $storage = $this->getExistingCacheStorage();
        $this->explorer = new Explorer(
            $this->connection,
            new Structure($this->connection, $storage),
            new DiscoveredConventions(new Structure($this->connection, $storage)),
            $storage
        );

        $this->selected_db = $this->cfg['dbname'];

        register_shutdown_function([$this, 'close']);

        $this->debugger->debug('stop');
        $this->cur_query = null;
    }

    /**
     * Base query method (compatible with original)
     */
    public function sql_query($query): ?ResultSet
    {
        if (!$this->connection) {
            $this->init();
        }

        if (is_array($query)) {
            $query = $this->build_sql($query);
        }

        $query = '/* ' . $this->debugger->debug_find_source() . ' */ ' . $query;
        $this->cur_query = $query;
        $this->debugger->debug('start');

        try {
            $this->result = $this->connection->query($query);

            // Update affected rows count for operations that modify data
            // For INSERT, UPDATE, DELETE operations, use getRowCount()
            if ($this->result instanceof ResultSet) {
                $this->last_affected_rows = $this->result->getRowCount();
            } else {
                $this->last_affected_rows = 0;
            }
        } catch (\Exception $e) {
            $this->debugger->log_error($e);
            $this->result = null;
            $this->last_affected_rows = 0;
        }

        $this->debugger->debug('stop');
        $this->last_query = $this->cur_query; // Preserve for error reporting
        $this->cur_query = null;

        if ($this->inited) {
            $this->num_queries++;
            $this->DBS['num_queries']++;
        }

        return $this->result;
    }

    /**
     * Execute query WRAPPER (with error handling)
     */
    public function query($query): ResultSet
    {
        if (!$result = $this->sql_query($query)) {
            $this->trigger_error();
        }

        return $result;
    }

    /**
     * Return number of rows
     */
    public function num_rows($result = false): int
    {
        if ($result || ($result = $this->result)) {
            if ($result instanceof ResultSet) {
                return $result->getRowCount();
            }
        }

        return 0;
    }

    /**
     * Return number of affected rows
     */
    public function affected_rows(): int
    {
        return $this->last_affected_rows;
    }

    /**
     * Fetch current row (compatible with original)
     */
    public function sql_fetchrow($result, string $field_name = ''): mixed
    {
        if (!$result instanceof ResultSet) {
            return false;
        }

        try {
            $row = $result->fetch();
            if (!$row) {
                return false;
            }

            // Convert Row to array for backward compatibility
            // Nette Database Row extends ArrayHash, so we can cast it to array
            $rowArray = (array)$row;

            if ($field_name) {
                return $rowArray[$field_name] ?? false;
            }

            return $rowArray;
        } catch (\Exception $e) {
            // Check if this is a duplicate column error
            if (str_contains($e->getMessage(), 'Found duplicate columns')) {
                // Log this as a problematic query that needs fixing
                $this->debugger->logLegacyQuery($this->last_query ?? $this->cur_query ?? 'Unknown query', $e->getMessage());

                // Automatically retry by re-executing the query with direct PDO
                // This bypasses Nette's duplicate column check completely
                try {
                    // Extract the clean SQL query
                    $cleanQuery = $this->last_query ?? $this->cur_query ?? '';
                    // Remove Nette's debug comment
                    $cleanQuery = preg_replace('#^(\s*)(/\*)(.*)(\*/)(\s*)#', '', $cleanQuery);

                    if (!$cleanQuery) {
                        throw new \RuntimeException('Could not extract clean query for PDO retry');
                    }

                    // Execute directly with PDO to bypass Nette's column checking
                    $stmt = $this->connection->getPdo()->prepare($cleanQuery);
                    $stmt->execute();
                    $row = $stmt->fetch(\PDO::FETCH_ASSOC);

                    // PDO::FETCH_ASSOC automatically handles duplicate columns by keeping the last occurrence
                    // which matches MySQL's behavior for SELECT t.*, f.* queries

                    if (!$row) {
                        return false;
                    }

                    if ($field_name) {
                        return $row[$field_name] ?? false;
                    }

                    return $row;
                } catch (\Exception $retryException) {
                    // If PDO retry also fails, log and re-throw
                    $this->debugger->log_error($retryException);
                    throw $retryException;
                }
            }

            // Log the error including the query that caused it
            $this->debugger->log_error($e);

            // Re-throw the exception so it can be handled by Whoops
            throw $e;
        }
    }

    /**
     * Alias of sql_fetchrow()
     */
    public function fetch_next($result): mixed
    {
        return $this->sql_fetchrow($result);
    }

    /**
     * Fetch row WRAPPER (with error handling)
     */
    public function fetch_row($query, string $field_name = ''): mixed
    {
        if (!$result = $this->sql_query($query)) {
            $this->trigger_error();
        }

        try {
            return $this->sql_fetchrow($result, $field_name);
        } catch (\Exception $e) {
            // Enhance the exception with query information
            $enhancedException = new \RuntimeException(
                "Database error during fetch_row: " . $e->getMessage() .
                "\nProblematic Query: " . ($this->cur_query ?: $this->last_query ?: 'Unknown'),
                $e->getCode(),
                $e
            );

            // Log the enhanced error
            $this->debugger->log_error($enhancedException);

            throw $enhancedException;
        }
    }

    /**
     * Fetch all rows
     */
    public function sql_fetchrowset($result, string $field_name = ''): array
    {
        if (!$result instanceof ResultSet) {
            return [];
        }

        $rowset = [];

        try {
            while ($row = $result->fetch()) {
                // Convert Row to array for backward compatibility
                // Nette Database Row extends ArrayHash, so we can cast it to array
                $rowArray = (array)$row;
                $rowset[] = $field_name ? ($rowArray[$field_name] ?? null) : $rowArray;
            }
        } catch (\Exception $e) {
            // Check if this is a duplicate column error
            if (str_contains($e->getMessage(), 'Found duplicate columns')) {
                // Log this as a problematic query that needs fixing
                $this->debugger->logLegacyQuery($this->last_query ?? $this->cur_query ?? 'Unknown query', $e->getMessage());

                // Automatically retry by re-executing the query with direct PDO
                try {
                    // Extract the clean SQL query
                    $cleanQuery = $this->last_query ?? $this->cur_query ?? '';
                    // Remove Nette's debug comment
                    $cleanQuery = preg_replace('#^(\s*)(/\*)(.*)(\*/)(\s*)#', '', $cleanQuery);

                    if (!$cleanQuery) {
                        throw new \RuntimeException('Could not extract clean query for PDO retry');
                    }

                    // Execute directly with PDO to bypass Nette's column checking
                    $stmt = $this->connection->getPdo()->prepare($cleanQuery);
                    $stmt->execute();

                    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                        $rowset[] = $field_name ? ($row[$field_name] ?? null) : $row;
                    }
                } catch (\Exception $retryException) {
                    // If PDO retry also fails, log and re-throw
                    $this->debugger->log_error($retryException);
                    throw $retryException;
                }
            } else {
                // For other exceptions, just re-throw
                $this->debugger->log_error($e);
                throw $e;
            }
        }

        return $rowset;
    }

    /**
     * Fetch all rows WRAPPER (with error handling)
     */
    public function fetch_rowset($query, string $field_name = ''): array
    {
        if (!$result = $this->sql_query($query)) {
            $this->trigger_error();
        }

        return $this->sql_fetchrowset($result, $field_name);
    }

    /**
     * Get last inserted id after insert statement
     */
    public function sql_nextid(): int
    {
        return $this->connection ? $this->connection->getInsertId() : 0;
    }

    /**
     * Free sql result
     */
    public function sql_freeresult($result = false): void
    {
        // Nette Database handles resource cleanup automatically
        if ($result === false || $result === $this->result) {
            $this->result = null;
        }
    }

    /**
     * Get Database Explorer table access with debug logging
     */
    public function table(string $table): DebugSelection
    {
        if (!$this->explorer) {
            $this->init();
        }

        $selection = $this->explorer->table($table);

        // Wrap the selection to capture queries for debug logging
        return new DebugSelection($selection, $this);
    }

    /**
     * Get existing cache storage from TorrentPier's unified cache system
     *
     * @return \Nette\Caching\Storage
     */
    private function getExistingCacheStorage(): \Nette\Caching\Storage
    {
        // Try to use the existing cache system if available
        if (function_exists('CACHE')) {
            try {
                $cacheManager = CACHE('database_structure');
                return $cacheManager->getStorage();
            } catch (\Exception $e) {
                // Fall back to DevNullStorage if cache system is not available yet
            }
        }

        // Fallback to a simple DevNullStorage if cache system is not available
        return new \Nette\Caching\Storages\DevNullStorage();
    }

    /**
     * Escape data used in sql query (using Nette Database)
     */
    public function escape($v, bool $check_type = false, bool $dont_escape = false): string
    {
        if ($dont_escape) {
            return (string)$v;
        }

        if (!$check_type) {
            return $this->escape_string((string)$v);
        }

        switch (true) {
            case is_string($v):
                return "'" . $this->escape_string($v) . "'";
            case is_int($v):
                return (string)$v;
            case is_bool($v):
                return $v ? '1' : '0';
            case is_float($v):
                return "'$v'";
            case $v === null:
                return 'NULL';
            default:
                $this->trigger_error(__FUNCTION__ . ' - wrong params');
                return '';
        }
    }

    /**
     * Escape string using Nette Database
     */
    public function escape_string(string $str): string
    {
        if (!$this->connection) {
            $this->init();
        }

        // Remove quotes from quoted string
        $quoted = $this->connection->quote($str);
        return substr($quoted, 1, -1);
    }

    /**
     * Build SQL statement from array (maintaining compatibility)
     */
    public function build_array(string $query_type, array $input_ary, bool $data_already_escaped = false, bool $check_data_type_in_escape = true): string
    {
        $fields = $values = $ary = [];
        $dont_escape = $data_already_escaped;
        $check_type = $check_data_type_in_escape;

        if (empty($input_ary)) {
            $this->trigger_error(__FUNCTION__ . ' - wrong params: $input_ary is empty');
        }

        if ($query_type == 'INSERT') {
            foreach ($input_ary as $field => $val) {
                $fields[] = $field;
                $values[] = $this->escape($val, $check_type, $dont_escape);
            }
            $fields = implode(', ', $fields);
            $values = implode(', ', $values);
            $query = "($fields)\nVALUES\n($values)";
        } elseif ($query_type == 'INSERT_SELECT') {
            foreach ($input_ary as $field => $val) {
                $fields[] = $field;
                $values[] = $this->escape($val, $check_type, $dont_escape);
            }
            $fields = implode(', ', $fields);
            $values = implode(', ', $values);
            $query = "($fields)\nSELECT\n$values";
        } elseif ($query_type == 'MULTI_INSERT') {
            foreach ($input_ary as $id => $sql_ary) {
                foreach ($sql_ary as $field => $val) {
                    $values[] = $this->escape($val, $check_type, $dont_escape);
                }
                $ary[] = '(' . implode(', ', $values) . ')';
                $values = [];
            }
            $fields = implode(', ', array_keys($input_ary[0]));
            $values = implode(",\n", $ary);
            $query = "($fields)\nVALUES\n$values";
        } elseif ($query_type == 'SELECT' || $query_type == 'UPDATE') {
            foreach ($input_ary as $field => $val) {
                $ary[] = "$field = " . $this->escape($val, $check_type, $dont_escape);
            }
            $glue = ($query_type == 'SELECT') ? "\nAND " : ",\n";
            $query = implode($glue, $ary);
        }

        if (!isset($query)) {
            if (function_exists('bb_die')) {
                bb_die('<pre><b>' . __FUNCTION__ . "</b>: Wrong params for <b>$query_type</b> query type\n\n\$input_ary:\n\n" . htmlspecialchars(print_r($input_ary, true)) . '</pre>');
            } else {
                throw new \InvalidArgumentException("Wrong params for $query_type query type");
            }
        }

        return "\n" . $query . "\n";
    }

    /**
     * Get empty SQL array structure
     */
    public function get_empty_sql_array(): array
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
            'LIMIT' => [],
        ];
    }

    /**
     * Build SQL from array structure
     */
    public function build_sql(array $sql_ary): string
    {
        $sql = '';

        // Apply array_unique to nested arrays
        foreach ($sql_ary as $clause => $ary) {
            if (is_array($ary) && $clause !== 'select_options') {
                $sql_ary[$clause] = array_unique($ary);
            }
        }

        foreach ($sql_ary as $clause => $ary) {
            switch ($clause) {
                case 'SELECT':
                    $sql .= ($ary) ? ' SELECT ' . implode(' ', $sql_ary['select_options'] ?? []) . ' ' . implode(', ', $ary) : '';
                    break;
                case 'FROM':
                    $sql .= ($ary) ? ' FROM ' . implode(', ', $ary) : '';
                    break;
                case 'INNER JOIN':
                    $sql .= ($ary) ? ' INNER JOIN ' . implode(' INNER JOIN ', $ary) : '';
                    break;
                case 'LEFT JOIN':
                    $sql .= ($ary) ? ' LEFT JOIN ' . implode(' LEFT JOIN ', $ary) : '';
                    break;
                case 'WHERE':
                    $sql .= ($ary) ? ' WHERE ' . implode(' AND ', $ary) : '';
                    break;
                case 'GROUP BY':
                    $sql .= ($ary) ? ' GROUP BY ' . implode(', ', $ary) : '';
                    break;
                case 'HAVING':
                    $sql .= ($ary) ? ' HAVING ' . implode(' AND ', $ary) : '';
                    break;
                case 'ORDER BY':
                    $sql .= ($ary) ? ' ORDER BY ' . implode(', ', $ary) : '';
                    break;
                case 'LIMIT':
                    $sql .= ($ary) ? ' LIMIT ' . implode(', ', $ary) : '';
                    break;
            }
        }

        return trim($sql);
    }

    /**
     * Return sql error array
     */
    public function sql_error(): array
    {
        if ($this->connection) {
            try {
                $pdo = $this->connection->getPdo();
                $errorCode = $pdo->errorCode();
                $errorInfo = $pdo->errorInfo();

                // Filter out "no error" states - PDO returns '00000' when there's no error
                if (!$errorCode || $errorCode === '00000') {
                    return ['code' => '', 'message' => ''];
                }

                // Build meaningful error message from errorInfo array
                // errorInfo format: [SQLSTATE, driver-specific error code, driver-specific error message]
                $message = '';
                if (isset($errorInfo[2]) && $errorInfo[2]) {
                    $message = $errorInfo[2]; // Driver-specific error message is most informative
                } elseif (isset($errorInfo[1]) && $errorInfo[1]) {
                    $message = "Error code: " . $errorInfo[1];
                } else {
                    $message = "SQLSTATE: " . $errorCode;
                }

                return [
                    'code' => $errorCode,
                    'message' => $message
                ];
            } catch (\Exception $e) {
                return ['code' => $e->getCode(), 'message' => $e->getMessage()];
            }
        }

        return ['code' => '', 'message' => 'not connected'];
    }

    /**
     * Close sql connection
     */
    public function close(): void
    {
        if ($this->connection) {
            $this->unlock();

            if (!empty($this->locks)) {
                foreach ($this->locks as $name => $void) {
                    $this->release_lock($name);
                }
            }

            $this->exec_shutdown_queries();

            // Nette Database connection will be closed automatically
            $this->connection = null;
        }

        $this->selected_db = null;
    }

    /**
     * Add shutdown query
     */
    public function add_shutdown_query(string $sql): void
    {
        $this->shutdown['__sql'][] = $sql;
    }

    /**
     * Exec shutdown queries
     */
    public function exec_shutdown_queries(): void
    {
        if (empty($this->shutdown)) {
            return;
        }

        if (!empty($this->shutdown['post_html'])) {
            $post_html_sql = $this->build_array('MULTI_INSERT', $this->shutdown['post_html']);
            $this->query("REPLACE INTO " . (defined('BB_POSTS_HTML') ? BB_POSTS_HTML : 'bb_posts_html') . " $post_html_sql");
        }

        if (!empty($this->shutdown['__sql'])) {
            foreach ($this->shutdown['__sql'] as $sql) {
                $this->query($sql);
            }
        }
    }

    /**
     * Lock tables
     */
    public function lock($tables, string $lock_type = 'WRITE'): ?ResultSet
    {
        $tables_sql = [];

        foreach ((array)$tables as $table_name) {
            $tables_sql[] = "$table_name $lock_type";
        }

        if ($tables_sql = implode(', ', $tables_sql)) {
            $this->locked = (bool)$this->sql_query("LOCK TABLES $tables_sql");
        }

        return $this->locked ? $this->result : null;
    }

    /**
     * Unlock tables
     */
    public function unlock(): bool
    {
        if ($this->locked && $this->sql_query("UNLOCK TABLES")) {
            $this->locked = false;
        }

        return !$this->locked;
    }

    /**
     * Obtain user level lock
     */
    public function get_lock(string $name, int $timeout = 0): mixed
    {
        $lock_name = $this->get_lock_name($name);
        $timeout = (int)$timeout;
        $row = $this->fetch_row("SELECT GET_LOCK('$lock_name', $timeout) AS lock_result");

        if ($row && $row['lock_result']) {
            $this->locks[$name] = true;
        }

        return $row ? $row['lock_result'] : null;
    }

    /**
     * Release user level lock
     */
    public function release_lock(string $name): mixed
    {
        $lock_name = $this->get_lock_name($name);
        $row = $this->fetch_row("SELECT RELEASE_LOCK('$lock_name') AS lock_result");

        if ($row && $row['lock_result']) {
            unset($this->locks[$name]);
        }

        return $row ? $row['lock_result'] : null;
    }

    /**
     * Check if lock is free
     */
    public function is_free_lock(string $name): mixed
    {
        $lock_name = $this->get_lock_name($name);
        $row = $this->fetch_row("SELECT IS_FREE_LOCK('$lock_name') AS lock_result");
        return $row ? $row['lock_result'] : null;
    }

    /**
     * Make per db unique lock name
     */
    public function get_lock_name(string $name): string
    {
        if (!$this->selected_db) {
            $this->init();
        }

        return "{$this->selected_db}_{$name}";
    }

    /**
     * Get info about last query
     */
    public function query_info(): string
    {
        $info = [];

        if ($this->result && ($num = $this->num_rows($this->result))) {
            $info[] = "$num rows";
        }

        // Only check affected rows if we have a stored value
        if ($this->last_affected_rows > 0) {
            $info[] = "{$this->last_affected_rows} rows";
        }

        return implode(', ', $info);
    }

    /**
     * Get server version
     */
    public function server_version(): string
    {
        if (!$this->connection) {
            return '';
        }

        $version = $this->connection->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION);
        if (preg_match('#^(\d+\.\d+\.\d+).*#', $version, $m)) {
            return $m[1];
        }
        return $version;
    }

    /**
     * Set slow query marker (delegated to debugger)
     */
    public function expect_slow_query(int $ignoring_time = 60, int $new_priority = 10): void
    {
        $this->debugger->expect_slow_query($ignoring_time, $new_priority);
    }

    /**
     * Store debug info (delegated to debugger)
     */
    public function debug(string $mode): void
    {
        $this->debugger->debug($mode);
    }

    /**
     * Trigger database error
     */
    public function trigger_error(string $msg = 'Database Error'): void
    {
        $error = $this->sql_error();

        // Define these variables early so they're available throughout the method
        $is_admin = defined('IS_ADMIN') && IS_ADMIN;
        $is_dev_mode = (defined('APP_ENV') && APP_ENV === 'development') || (defined('DBG_USER') && DBG_USER);

        // Build a meaningful error message
        if (!empty($error['message'])) {
            $error_msg = "$msg: " . $error['message'];
            if (!empty($error['code'])) {
                $error_msg = "$msg ({$error['code']}): " . $error['message'];
            }
        } else {
            // Base error message for all users
            $error_msg = "$msg: Database operation failed";

            // Only add detailed debugging information for administrators or in development mode
            if ($is_admin || $is_dev_mode) {
                // Gather detailed debugging information - ONLY for admins/developers
                $debug_info = [];

                // Connection status
                if ($this->connection) {
                    $debug_info[] = "Connection: Active";
                    try {
                        $pdo = $this->connection->getPdo();
                        if ($pdo) {
                            $debug_info[] = "PDO: Available";
                            $errorInfo = $pdo->errorInfo();
                            if ($errorInfo && count($errorInfo) >= 3) {
                                $debug_info[] = "PDO ErrorInfo: " . json_encode($errorInfo);
                            }
                            $debug_info[] = "PDO ErrorCode: " . $pdo->errorCode();
                        } else {
                            $debug_info[] = "PDO: Null";
                        }
                    } catch (\Exception $e) {
                        $debug_info[] = "PDO Check Failed: " . $e->getMessage();
                    }
                } else {
                    $debug_info[] = "Connection: None";
                }

                // Query information
                if ($this->cur_query) {
                    $debug_info[] = "Last Query: " . substr($this->cur_query, 0, 200) . (strlen($this->cur_query) > 200 ? '...' : '');
                } else {
                    $debug_info[] = "Last Query: None";
                }

                // Database information
                $debug_info[] = "Database: " . ($this->selected_db ?: 'None');
                $debug_info[] = "Server: " . $this->db_server;

                // Recent queries from debug log (if available)
                if (isset($this->debugger->dbg) && !empty($this->debugger->dbg)) {
                    $recent_queries = array_slice($this->debugger->dbg, -3); // Last 3 queries
                    $debug_info[] = "Recent Queries Count: " . count($recent_queries);
                    foreach ($recent_queries as $i => $query_info) {
                        $debug_info[] = "Query " . ($i + 1) . ": " . substr($query_info['sql'] ?? 'Unknown', 0, 100) . (strlen($query_info['sql'] ?? '') > 100 ? '...' : '');
                    }
                }

                if ($debug_info) {
                    $error_msg .= " [DEBUG: " . implode("; ", $debug_info) . "]";
                }

                // Log this for investigation
                if (function_exists('bb_log')) {
                    bb_log("Unknown Database Error Debug:\n" . implode(LOG_LF, $debug_info) . LOG_LF . str_repeat('=', 30) . LOG_LF, 'unknown_db_errors');
                }
            } else {
                // For regular users: generic message only + contact admin hint
                $error_msg = "$msg: A database error occurred. Please contact the administrator if this problem persists.";

                // Still log basic information for debugging
                if (function_exists('bb_log')) {
                    bb_log("Database Error (User-facing): $error_msg\nRequest: " . ($_SERVER['REQUEST_URI'] ?? 'CLI') . LOG_LF . str_repeat('=', 30) . LOG_LF, 'user_db_errors');
                }
            }
        }

        // Add query context for debugging (but only for admins/developers)
        if ($this->cur_query && ($is_admin || $is_dev_mode)) {
            $error_msg .= "\nQuery: " . $this->cur_query;
        }

        if (function_exists('bb_die')) {
            bb_die($error_msg);
        } else {
            throw new \RuntimeException($error_msg);
        }
    }

    /**
     * Find source of database call (delegated to debugger)
     */
    public function debug_find_source(string $mode = 'all'): string
    {
        return $this->debugger->debug_find_source($mode);
    }

    /**
     * Prepare for logging (delegated to debugger)
     */
    public function log_next_query(int $queries_count = 1, string $log_file = 'sql_queries'): void
    {
        $this->debugger->log_next_query($queries_count, $log_file);
    }

    /**
     * Log query (delegated to debugger)
     */
    public function log_query(string $log_file = 'sql_queries'): void
    {
        $this->debugger->log_query($log_file);
    }

    /**
     * Log slow query (delegated to debugger)
     */
    public function log_slow_query(string $log_file = 'sql_slow_bb'): void
    {
        $this->debugger->log_slow_query($log_file);
    }

    /**
     * Log error (delegated to debugger)
     */
    public function log_error(?\Exception $exception = null): void
    {
        $this->debugger->log_error($exception);
    }

    /**
     * Explain queries (delegated to debugger)
     */
    public function explain($mode, $html_table = '', array $row = []): mixed
    {
        return $this->debugger->explain($mode, $html_table, $row);
    }

    /**
     * Magic method to provide backward compatibility for debug properties
     */
    public function __get(string $name): mixed
    {
        // Delegate debug-related properties to the debugger
        switch ($name) {
            case 'dbg':
                return $this->debugger->dbg ?? [];
            case 'dbg_id':
                return $this->debugger->dbg_id ?? 0;
            case 'dbg_enabled':
                return $this->debugger->dbg_enabled ?? false;
            case 'do_explain':
                return $this->debugger->do_explain ?? false;
            case 'explain_hold':
                return $this->debugger->explain_hold ?? '';
            case 'explain_out':
                return $this->debugger->explain_out ?? '';
            case 'slow_time':
                return $this->debugger->slow_time ?? 3.0;
            case 'sql_timetotal':
                return $this->sql_timetotal;
            default:
                throw new \InvalidArgumentException("Property '$name' does not exist");
        }
    }

    /**
     * Magic method to check if debug properties exist
     */
    public function __isset(string $name): bool
    {
        switch ($name) {
            case 'dbg':
            case 'dbg_id':
            case 'dbg_enabled':
            case 'do_explain':
            case 'explain_hold':
            case 'explain_out':
            case 'slow_time':
            case 'sql_timetotal':
                return true;
            default:
                return false;
        }
    }

    /**
     * Destroy singleton instances (for testing)
     */
    public static function destroyInstances(): void
    {
        self::$instance = null;
        self::$instances = [];
    }
}
