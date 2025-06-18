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
use Nette\Database\ResultSet;
use Nette\Database\Row;
use TorrentPier\Dev;
use TorrentPier\Legacy\SqlDb;

/**
 * Modern DB class using Nette Database with backward compatibility
 * Implements singleton pattern while maintaining all existing SqlDb methods
 */
class DB
{
    private static ?DB $instance = null;
    private static array $instances = [];

    private ?Connection $connection = null;
    private ?ResultSet $result = null;
    private int $last_affected_rows = 0;

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
    public float $sql_starttime = 0;
    public float $sql_inittime = 0;
    public float $sql_timetotal = 0;
    public float $cur_query_time = 0;
    public float $slow_time = 0;

    public array $dbg = [];
    public int $dbg_id = 0;
    public bool $dbg_enabled = false;
    public ?string $cur_query = null;

    public bool $do_explain = false;
    public string $explain_hold = '';
    public string $explain_out = '';

    public array $shutdown = [];
    public array $DBS = [];

    /**
     * Private constructor for singleton pattern
     */
    private function __construct(array $cfg_values, string $server_name = 'db')
    {
        global $DBS;

        $this->cfg = array_combine($this->cfg_keys, $cfg_values);
        $this->db_server = $server_name;
        $this->dbg_enabled = (dev()->checkSqlDebugAllowed() || !empty($_COOKIE['explain']));
        $this->do_explain = ($this->dbg_enabled && !empty($_COOKIE['explain']));
        $this->slow_time = defined('SQL_SLOW_QUERY_TIME') ? SQL_SLOW_QUERY_TIME : 3;

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
        $this->cur_query = $this->dbg_enabled ? "connect to: {$this->cfg['dbhost']}:{$this->cfg['dbport']}" : 'connect';
        $this->debug('start');

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

        $this->selected_db = $this->cfg['dbname'];

        register_shutdown_function([$this, 'close']);

        $this->debug('stop');
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

        $query = '/* ' . $this->debug_find_source() . ' */ ' . $query;
        $this->cur_query = $query;
        $this->debug('start');

                        try {
            $this->result = $this->connection->query($query);

            // Initialize affected rows to 0 (most queries don't affect rows)
            $this->last_affected_rows = 0;
        } catch (\Exception $e) {
            $this->log_error();
            $this->result = null;
            $this->last_affected_rows = 0;
        }

        $this->debug('stop');
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

        return $this->sql_fetchrow($result, $field_name);
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
        while ($row = $result->fetch()) {
            // Convert Row to array for backward compatibility
            // Nette Database Row extends ArrayHash, so we can cast it to array
            $rowArray = (array)$row;
            $rowset[] = $field_name ? ($rowArray[$field_name] ?? null) : $rowArray;
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

        if (empty($input_ary) || !is_array($input_ary)) {
            $this->trigger_error(__FUNCTION__ . ' - wrong params: $input_ary');
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
                return [
                    'code' => $pdo->errorCode(),
                    'message' => implode(': ', $pdo->errorInfo())
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
     * Set slow query marker
     */
    public function expect_slow_query(int $ignoring_time = 60, int $new_priority = 10): void
    {
        if (function_exists('CACHE')) {
            $cache = CACHE('bb_cache');
            if ($old_priority = $cache->get('dont_log_slow_query')) {
                if ($old_priority > $new_priority) {
                    return;
                }
            }

            if (!defined('IN_FIRST_SLOW_QUERY')) {
                define('IN_FIRST_SLOW_QUERY', true);
            }

            $cache->set('dont_log_slow_query', $new_priority, $ignoring_time);
        }
    }

    /**
     * Store debug info
     */
    public function debug(string $mode): void
    {
        if (!defined('SQL_DEBUG') || !SQL_DEBUG) {
            return;
        }

        $id =& $this->dbg_id;
        $dbg =& $this->dbg[$id];

        if ($mode === 'start') {
            if (defined('SQL_CALC_QUERY_TIME') && SQL_CALC_QUERY_TIME || defined('SQL_LOG_SLOW_QUERIES') && SQL_LOG_SLOW_QUERIES) {
                $this->sql_starttime = microtime(true);
            }

            if ($this->dbg_enabled) {
                $dbg['sql'] = preg_replace('#^(\s*)(/\*)(.*)(\*/)(\s*)#', '', $this->cur_query);
                $dbg['src'] = $this->debug_find_source();
                $dbg['file'] = $this->debug_find_source('file');
                $dbg['line'] = $this->debug_find_source('line');
                $dbg['time'] = '';
                $dbg['info'] = '';
                $dbg['mem_before'] = function_exists('sys') ? sys('mem') : 0;
            }

            if ($this->do_explain) {
                $this->explain('start');
            }
        } elseif ($mode === 'stop') {
            if (defined('SQL_CALC_QUERY_TIME') && SQL_CALC_QUERY_TIME || defined('SQL_LOG_SLOW_QUERIES') && SQL_LOG_SLOW_QUERIES) {
                $this->cur_query_time = microtime(true) - $this->sql_starttime;
                $this->sql_timetotal += $this->cur_query_time;
                $this->DBS['sql_timetotal'] += $this->cur_query_time;

                if (defined('SQL_LOG_SLOW_QUERIES') && SQL_LOG_SLOW_QUERIES && $this->cur_query_time > $this->slow_time) {
                    $this->log_slow_query();
                }
            }

            if ($this->dbg_enabled) {
                $dbg['time'] = microtime(true) - $this->sql_starttime;
                $dbg['info'] = $this->query_info();
                $dbg['mem_after'] = function_exists('sys') ? sys('mem') : 0;
                $id++;
            }

            if ($this->do_explain) {
                $this->explain('stop');
            }

            // Check for logging
            if ($this->DBS['log_counter'] && $this->inited) {
                $this->log_query($this->DBS['log_file']);
                $this->DBS['log_counter']--;
            }
        }
    }

    /**
     * Trigger database error
     */
    public function trigger_error(string $msg = 'DB Error'): void
    {
        $error = $this->sql_error();
        $error_msg = "$msg: " . $error['message'];

        if (function_exists('bb_die')) {
            bb_die($error_msg);
        } else {
            throw new \RuntimeException($error_msg);
        }
    }

    /**
     * Find source of database call
     */
    public function debug_find_source(string $mode = 'all'): string
    {
        if (!defined('SQL_PREPEND_SRC') || !SQL_PREPEND_SRC) {
            return 'src disabled';
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        // Find first non-DB call
        foreach ($trace as $frame) {
            if (isset($frame['file']) && !str_contains($frame['file'], 'Database/DB.php')) {
                switch ($mode) {
                    case 'file':
                        return $frame['file'];
                    case 'line':
                        return (string)($frame['line'] ?? '?');
                    case 'all':
                    default:
                        $file = function_exists('hide_bb_path') ? hide_bb_path($frame['file']) : basename($frame['file']);
                        $line = $frame['line'] ?? '?';
                        return "$file($line)";
                }
            }
        }

        return 'src not found';
    }

    /**
     * Prepare for logging
     */
    public function log_next_query(int $queries_count = 1, string $log_file = 'sql_queries'): void
    {
        $this->DBS['log_file'] = $log_file;
        $this->DBS['log_counter'] = $queries_count;
    }

    /**
     * Log query
     */
    public function log_query(string $log_file = 'sql_queries'): void
    {
        if (!function_exists('bb_log') || !function_exists('dev')) {
            return;
        }

        $q_time = ($this->cur_query_time >= 10) ? round($this->cur_query_time, 0) : sprintf('%.3f', $this->cur_query_time);
        $msg = [];
        $msg[] = round($this->sql_starttime);
        $msg[] = date('m-d H:i:s', (int)$this->sql_starttime);
        $msg[] = sprintf('%-6s', $q_time);
        $msg[] = sprintf('%05d', getmypid());
        $msg[] = $this->db_server;
        $msg[] = dev()->formatShortQuery($this->cur_query);
        $msg = implode(defined('LOG_SEPR') ? LOG_SEPR : ' | ', $msg);
        $msg .= ($info = $this->query_info()) ? ' # ' . $info : '';
        $msg .= ' # ' . $this->debug_find_source() . ' ';
        $msg .= defined('IN_CRON') ? 'cron' : basename($_SERVER['REQUEST_URI'] ?? '');
        bb_log($msg . (defined('LOG_LF') ? LOG_LF : "\n"), $log_file);
    }

    /**
     * Log slow query
     */
    public function log_slow_query(string $log_file = 'sql_slow_bb'): void
    {
        if (!defined('IN_FIRST_SLOW_QUERY') && function_exists('CACHE')) {
            $cache = CACHE('bb_cache');
            if ($cache && $cache->get('dont_log_slow_query')) {
                return;
            }
        }
        $this->log_query($log_file);
    }

    /**
     * Log error
     */
    public function log_error(): void
    {
        $error = $this->sql_error();
        error_log("DB Error: " . $error['message'] . " Query: " . $this->cur_query);
    }

    /**
     * Explain queries - maintains compatibility with legacy SqlDb
     */
    public function explain($mode, $html_table = '', array $row = []): mixed
    {
        if (!$this->do_explain) {
            return false;
        }

        $query = $this->cur_query ?? '';
        // Remove comments
        $query = preg_replace('#(\s*)(/\*)(.*)(\*/)(\s*)#', '', $query);

        switch ($mode) {
            case 'start':
                $this->explain_hold = '';

                if (preg_match('#UPDATE ([a-z0-9_]+).*?WHERE(.*)/#', $query, $m)) {
                    $query = "SELECT * FROM $m[1] WHERE $m[2]";
                } elseif (preg_match('#DELETE FROM ([a-z0-9_]+).*?WHERE(.*)#s', $query, $m)) {
                    $query = "SELECT * FROM $m[1] WHERE $m[2]";
                }

                if (str_starts_with($query, "SELECT")) {
                    $html_table = false;

                    try {
                        $result = $this->connection->query("EXPLAIN $query");
                        while ($row = $result->fetch()) {
                            $rowArray = (array)$row;
                            $html_table = $this->explain('add_explain_row', $html_table, $rowArray);
                        }
                    } catch (\Exception $e) {
                        // Skip if explain fails
                    }

                    if ($html_table) {
                        $this->explain_hold .= '</table>';
                    }
                }
                break;

            case 'stop':
                if (!$this->explain_hold) {
                    break;
                }

                $id = $this->dbg_id - 1;
                $htid = 'expl-' . spl_object_hash($this->connection) . '-' . $id;
                $dbg = $this->dbg[$id] ?? [];

                // Ensure required keys exist with defaults
                $dbg = array_merge([
                    'time' => $this->cur_query_time ?? 0,
                    'sql' => $this->cur_query ?? '',
                    'query' => $this->cur_query ?? '',
                    'src' => $this->debug_find_source(),
                    'trace' => $this->debug_find_source()  // Backup for compatibility
                ], $dbg);

                $this->explain_out .= '
                <table width="98%" cellpadding="0" cellspacing="0" class="bodyline row2 bCenter" style="border-bottom: 0;">
                <tr>
                    <th style="height: 22px;" align="left">&nbsp;' . ($dbg['src'] ?? $dbg['trace']) . '&nbsp; [' . sprintf('%.3f', $dbg['time']) . ' s]&nbsp; <i>' . $this->query_info() . '</i></th>
                    <th class="copyElement" data-clipboard-target="#' . $htid . '" style="height: 22px;" align="right" title="Copy to clipboard">' . "[$this->engine] $this->db_server.$this->selected_db" . ' :: Query #' . ($this->num_queries + 1) . '&nbsp;</th>
                </tr>
                <tr><td colspan="2">' . $this->explain_hold . '</td></tr>
                </table>
                <div class="sqlLog"><div id="' . $htid . '" class="sqlLogRow sqlExplain" style="padding: 0;">' . (function_exists('dev') ? dev()->formatShortQuery($dbg['sql'] ?? $dbg['query'], true) : htmlspecialchars($dbg['sql'] ?? $dbg['query'])) . '&nbsp;&nbsp;</div></div>
                <br />';
                break;

            case 'add_explain_row':
                if (!$html_table && $row) {
                    $html_table = true;
                    $this->explain_hold .= '<table width="100%" cellpadding="3" cellspacing="1" class="bodyline" style="border-width: 0;"><tr>';
                    foreach (array_keys($row) as $val) {
                        $this->explain_hold .= '<td class="row3 gensmall" align="center"><b>' . htmlspecialchars($val) . '</b></td>';
                    }
                    $this->explain_hold .= '</tr>';
                }
                $this->explain_hold .= '<tr>';
                foreach (array_values($row) as $i => $val) {
                    $class = !($i % 2) ? 'row1' : 'row2';
                    $this->explain_hold .= '<td class="' . $class . ' gen">' . str_replace(["{$this->selected_db}.", ',', ';'], ['', ', ', ';<br />'], htmlspecialchars($val ?? '')) . '</td>';
                }
                $this->explain_hold .= '</tr>';

                return $html_table;

            case 'display':
                echo '<a name="explain"></a><div class="med">' . $this->explain_out . '</div>';
                break;
        }

        return false;
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
