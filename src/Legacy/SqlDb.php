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

namespace TorrentPier\Legacy;

use mysqli_result;

/**
 * Class SqlDb
 * @package TorrentPier\Legacy
 */
class SqlDb
{
    public $cfg = [];
    public $cfg_keys = ['dbhost', 'dbname', 'dbuser', 'dbpasswd', 'charset', 'persist'];
    private $link;
    public $result;
    public $db_server = '';
    public $selected_db;
    public $inited = false;

    public $locked = false;
    public $locks = [];

    public $num_queries = 0;
    public $sql_starttime = 0;
    public $sql_inittime = 0;
    public $sql_timetotal = 0;
    public $cur_query_time = 0;
    public $slow_time = 0;

    public $dbg = [];
    public $dbg_id = 0;
    public $dbg_enabled = false;
    public $cur_query;

    public $do_explain = false;
    public $explain_hold = '';
    public $explain_out = '';

    public $shutdown = [];

    public $DBS = [];

    /**
     * sql_db constructor.
     * @param $cfg_values
     */
    public function __construct($cfg_values)
    {
        global $DBS;

        $this->cfg = array_combine($this->cfg_keys, $cfg_values);
        $this->dbg_enabled = (sql_dbg_enabled() || !empty($_COOKIE['explain']));
        $this->do_explain = ($this->dbg_enabled && !empty($_COOKIE['explain']));
        $this->slow_time = SQL_SLOW_QUERY_TIME;

        // ссылки на глобальные переменные (для включения логов сразу на всех серверах, подсчета общего количества запросов и т.д.)
        $this->DBS['log_file'] =& $DBS->log_file;
        $this->DBS['log_counter'] =& $DBS->log_counter;
        $this->DBS['num_queries'] =& $DBS->num_queries;
        $this->DBS['sql_inittime'] =& $DBS->sql_inittime;
        $this->DBS['sql_timetotal'] =& $DBS->sql_timetotal;
    }

    /**
     * Initialize connection
     */
    public function init()
    {
        // Connect to server
        $this->connect();

        // Set charset
        if ($this->cfg['charset'] && !mysqli_set_charset($this->link, $this->cfg['charset'])) {
            if (!$this->sql_query("SET NAMES {$this->cfg['charset']}")) {
                die("Could not set charset {$this->cfg['charset']}");
            }
        }

        $this->inited = true;
        $this->num_queries = 0;
        $this->sql_inittime = $this->sql_timetotal;
        $this->DBS['sql_inittime'] += $this->sql_inittime;
    }

    /**
     * Open connection
     */
    public function connect()
    {
        $this->cur_query = $this->dbg_enabled ? "connect to: {$this->cfg['dbhost']}" : 'connect';
        $this->debug('start');

        $p = ((bool)$this->cfg['persist']) ? 'p:' : '';
        $this->link = mysqli_connect($p . $this->cfg['dbhost'], $this->cfg['dbuser'], $this->cfg['dbpasswd'], $this->cfg['dbname']);
        $this->selected_db = $this->cfg['dbname'];

        if (mysqli_connect_error()) {
            $server = DBG_USER ? $this->cfg['dbhost'] : '';
            header('HTTP/1.0 503 Service Unavailable');
            bb_log(' ', "db_err/connect_failed_{$this->cfg['dbhost']}");
            die("Could not connect to mysql server $server");
        }

        register_shutdown_function([&$this, 'close']);

        $this->debug('stop');
        $this->cur_query = null;
    }

    /**
     * Base query method
     *
     * @param $query
     *
     * @return bool|mysqli_result|null
     */
    public function sql_query($query)
    {
        if (!$this->link) {
            $this->init();
        }
        if (is_array($query)) {
            $query = $this->build_sql($query);
        }
        if (SQL_PREPEND_SRC_COMM) {
            $query = '/* ' . $this->debug_find_source() . ' */ ' . $query;
        }
        $this->cur_query = $query;
        $this->debug('start');

        if (!$this->result = mysqli_query($this->link, $query)) {
            $this->log_error();
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
     *
     * @param $query
     *
     * @return bool|mysqli_result|null
     */
    public function query($query)
    {
        if (!$result = $this->sql_query($query)) {
            $this->trigger_error();
        }

        return $result;
    }

    /**
     * Return number of rows
     *
     * @param bool $result
     *
     * @return bool|int
     */
    public function num_rows($result = false)
    {
        $num_rows = false;

        if ($result or $result = $this->result) {
            $num_rows = $result instanceof mysqli_result ? mysqli_num_rows($result) : false;
        }

        return $num_rows;
    }

    /**
     * Return number of affected rows
     *
     * @return int
     */
    public function affected_rows()
    {
        return mysqli_affected_rows($this->link);
    }

    /**
     * Fetch current field
     *
     * @param $field
     * @param int $rownum
     * @param int $query_id
     *
     * @return bool
     */
    public function sql_fetchfield($field, $rownum = -1, $query_id = 0)
    {
        if (!$query_id) {
            $query_id = $this->query_result;
        }
        if ($query_id) {
            if ($rownum > -1) {
                $result = $this->sql_result($query_id, $rownum, $field);
            } else {
                if (empty($this->row[$query_id]) && empty($this->rowset[$query_id])) {
                    if ($this->sql_fetchrow()) {
                        $result = $this->row[$query_id][$field];
                    }
                } else {
                    if ($this->rowset[$query_id]) {
                        $result = $this->rowset[$query_id][0][$field];
                    } elseif ($this->row[$query_id]) {
                        $result = $this->row[$query_id][$field];
                    }
                }
            }
            return $result;
        }

        return false;
    }

    /**
     * @param mysqli_result $res
     * @param $row
     * @param int $field
     *
     * @return mixed
     */
    private function sql_result(mysqli_result $res, $row, $field = 0)
    {
        $res->data_seek($row);
        $dataRow = $res->fetch_array();
        return $dataRow[$field];
    }

    /**
     * Fetch current row
     *
     * @param $result
     * @param string $field_name
     *
     * @return array|bool|null
     */
    public function sql_fetchrow($result, $field_name = '')
    {
        $row = mysqli_fetch_assoc($result);

        if ($field_name) {
            return isset($row[$field_name]) ? $row[$field_name] : false;
        }

        return $row;
    }

    /**
     * Alias of sql_fetchrow()
     * @param $result
     *
     * @return array|bool|null
     */
    public function fetch_next($result)
    {
        return $this->sql_fetchrow($result);
    }

    /**
     * Fetch row WRAPPER (with error handling)
     * @param $query
     * @param string $field_name
     *
     * @return array|bool|null
     */
    public function fetch_row($query, $field_name = '')
    {
        if (!$result = $this->sql_query($query)) {
            $this->trigger_error();
        }

        return $this->sql_fetchrow($result, $field_name);
    }

    /**
     * Fetch all rows
     *
     * @param $result
     * @param string $field_name
     *
     * @return array
     */
    public function sql_fetchrowset($result, $field_name = '')
    {
        $rowset = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $rowset[] = $field_name ? $row[$field_name] : $row;
        }

        return $rowset;
    }

    /**
     * Fetch all rows WRAPPER (with error handling)
     *
     * @param $query
     * @param string $field_name
     *
     * @return array
     */
    public function fetch_rowset($query, $field_name = '')
    {
        if (!$result = $this->sql_query($query)) {
            $this->trigger_error();
        }

        return $this->sql_fetchrowset($result, $field_name);
    }

    /**
     * Fetch all rows WRAPPER (with error handling)
     *
     * @param $query
     * @param string $field_name
     *
     * @return array
     */
    public function fetch_all($query, $field_name = '')
    {
        if (!$result = $this->sql_query($query)) {
            $this->trigger_error();
        }

        return $this->sql_fetchrowset($result, $field_name);
    }

    /**
     * Get last inserted id after insert statement
     *
     * @return int|string
     */
    public function sql_nextid()
    {
        return mysqli_insert_id($this->link);
    }

    /**
     * Free sql result
     *
     * @param bool $result
     */
    public function sql_freeresult($result = false)
    {
        if ($result or $result = $this->result) {
            if ($result instanceof mysqli_result) {
                mysqli_free_result($result);
            }
        }

        $this->result = null;
    }

    /**
     * Escape data used in sql query
     *
     * @param $v
     * @param bool $check_type
     * @param bool $dont_escape
     *
     * @return string
     */
    public function escape($v, $check_type = false, $dont_escape = false)
    {
        if ($dont_escape) {
            return $v;
        }
        if (!$check_type) {
            return $this->escape_string($v);
        }

        switch (true) {
            case is_string($v):
                return "'" . $this->escape_string($v) . "'";
            case is_int($v):
                return "$v";
            case is_bool($v):
                return ($v) ? '1' : '0';
            case is_float($v):
                return "'$v'";
            case null === $v:
                return 'NULL';
        }
        // if $v has unsuitable type
        $this->trigger_error(__FUNCTION__ . ' - wrong params');
    }

    /**
     * Escape string
     *
     * @param $str
     *
     * @return string
     */
    public function escape_string($str)
    {
        if (!$this->link) {
            $this->init();
        }

        return mysqli_real_escape_string($this->link, $str);
    }

    /**
     * Build SQL statement from array.
     * Possible $query_type values: INSERT, INSERT_SELECT, MULTI_INSERT, UPDATE, SELECT
     *
     * @param $query_type
     * @param $input_ary
     * @param bool $data_already_escaped
     * @param bool $check_data_type_in_escape
     *
     * @return string
     */
    public function build_array($query_type, $input_ary, $data_already_escaped = false, $check_data_type_in_escape = true)
    {
        $fields = $values = $ary = $query = [];
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

        if (!$query) {
            bb_die('<pre><b>' . __FUNCTION__ . "</b>: Wrong params for <b>$query_type</b> query type\n\n\$input_ary:\n\n" . htmlCHR(print_r($input_ary, true)) . '</pre>');
        }

        return "\n" . $query . "\n";
    }

    /**
     * @return array
     */
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
            'LIMIT' => [],
        ];
    }

    /**
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
                    $sql .= ($ary) ? ' SELECT ' . implode(' ', $sql_ary['select_options']) . ' ' . implode(', ', $ary) : '';
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
     *
     * @return array
     */
    public function sql_error()
    {
        if ($this->link) {
            return ['code' => mysqli_errno($this->link), 'message' => mysqli_error($this->link)];
        }

        return ['code' => '', 'message' => 'not connected'];
    }

    /**
     * Close sql connection
     */
    public function close()
    {
        if ($this->link) {
            $this->unlock();

            if (!empty($this->locks)) {
                foreach ($this->locks as $name => $void) {
                    $this->release_lock($name);
                }
            }

            $this->exec_shutdown_queries();

            mysqli_close($this->link);
        }

        $this->link = $this->selected_db = null;
    }

    /**
     * Add shutdown query
     *
     * @param $sql
     */
    public function add_shutdown_query($sql)
    {
        $this->shutdown['__sql'][] = $sql;
    }

    /**
     * Exec shutdown queries
     */
    public function exec_shutdown_queries()
    {
        if (empty($this->shutdown)) {
            return;
        }

        if (!empty($this->shutdown['post_html'])) {
            $post_html_sql = $this->build_array('MULTI_INSERT', $this->shutdown['post_html']);
            $this->query("REPLACE INTO " . BB_POSTS_HTML . " $post_html_sql");
        }

        if (!empty($this->shutdown['__sql'])) {
            foreach ($this->shutdown['__sql'] as $sql) {
                $this->query($sql);
            }
        }
    }

    /**
     * Lock tables
     *
     * @param $tables
     * @param string $lock_type
     *
     * @return bool|mysqli_result|null
     */
    public function lock($tables, $lock_type = 'WRITE')
    {
        $tables_sql = [];

        foreach ((array)$tables as $table_name) {
            $tables_sql[] = "$table_name $lock_type";
        }
        if ($tables_sql = implode(', ', $tables_sql)) {
            $this->locked = $this->sql_query("LOCK TABLES $tables_sql");
        }

        return $this->locked;
    }

    /**
     * Unlock tables
     *
     * @return bool
     */
    public function unlock()
    {
        if ($this->locked && $this->sql_query("UNLOCK TABLES")) {
            $this->locked = false;
        }

        return !$this->locked;
    }

    /**
     * Obtain user level lock
     *
     * @param $name
     * @param int $timeout
     *
     * @return mixed
     */
    public function get_lock($name, $timeout = 0)
    {
        $lock_name = $this->get_lock_name($name);
        $timeout = (int)$timeout;
        $row = $this->fetch_row("SELECT GET_LOCK('$lock_name', $timeout) AS lock_result");

        if ($row['lock_result']) {
            $this->locks[$name] = true;
        }

        return $row['lock_result'];
    }

    /**
     * Obtain user level lock status
     *
     * @param $name
     *
     * @return mixed
     */
    public function release_lock($name)
    {
        $lock_name = $this->get_lock_name($name);
        $row = $this->fetch_row("SELECT RELEASE_LOCK('$lock_name') AS lock_result");

        if ($row['lock_result']) {
            unset($this->locks[$name]);
        }

        return $row['lock_result'];
    }

    /**
     * Release user level lock
     *
     * @param $name
     *
     * @return mixed
     */
    public function is_free_lock($name)
    {
        $lock_name = $this->get_lock_name($name);
        $row = $this->fetch_row("SELECT IS_FREE_LOCK('$lock_name') AS lock_result");
        return $row['lock_result'];
    }

    /**
     * Make per db unique lock name
     *
     * @param $name
     *
     * @return string
     */
    public function get_lock_name($name)
    {
        if (!$this->selected_db) {
            $this->init();
        }

        return "{$this->selected_db}_{$name}";
    }

    /**
     * Get info about last query
     *
     * @return mixed
     */
    public function query_info()
    {
        $info = [];

        if ($num = $this->num_rows($this->result)) {
            $info[] = "$num rows";
        }

        if ($this->link and $ext = mysqli_info($this->link)) {
            $info[] = "$ext";
        } elseif (!$num && ($aff = $this->affected_rows($this->result) and $aff != -1)) {
            $info[] = "$aff rows";
        }

        return str_compact(implode(', ', $info));
    }

    /**
     * Get server version
     *
     * @return mixed
     */
    public function server_version()
    {
        preg_match('#^(\d+\.\d+\.\d+).*#', mysqli_get_server_info($this->link), $m);
        return $m[1];
    }

    /**
     * Set slow query marker for xx seconds.
     * This will disable counting other queries as "slow" during this time.
     *
     * @param int $ignoring_time
     * @param int $new_priority
     */
    public function expect_slow_query($ignoring_time = 60, $new_priority = 10)
    {
        if ($old_priority = CACHE('bb_cache')->get('dont_log_slow_query')) {
            if ($old_priority > $new_priority) {
                return;
            }
        }

        if (!defined('IN_FIRST_SLOW_QUERY')) {
            define('IN_FIRST_SLOW_QUERY', true);
        }

        CACHE('bb_cache')->set('dont_log_slow_query', $new_priority, $ignoring_time);
    }

    /**
     * Store debug info
     *
     * @param $mode
     */
    public function debug($mode)
    {
        if (!SQL_DEBUG) {
            return;
        }

        $id =& $this->dbg_id;
        $dbg =& $this->dbg[$id];

        if ($mode == 'start') {
            if (SQL_CALC_QUERY_TIME || DBG_LOG || SQL_LOG_SLOW_QUERIES) {
                $this->sql_starttime = utime();
            }
            if ($this->dbg_enabled) {
                $dbg['sql'] = preg_replace('#^(\s*)(/\*)(.*)(\*/)(\s*)#', '', $this->cur_query);
                $dbg['src'] = $this->debug_find_source();
                $dbg['file'] = $this->debug_find_source('file');
                $dbg['line'] = $this->debug_find_source('line');
                $dbg['time'] = '';
                $dbg['info'] = '';
                $dbg['mem_before'] = sys('mem');
            }
            if ($this->do_explain) {
                $this->explain('start');
            }
        } elseif ($mode == 'stop') {
            if (SQL_CALC_QUERY_TIME || DBG_LOG || SQL_LOG_SLOW_QUERIES) {
                $this->cur_query_time = utime() - $this->sql_starttime;
                $this->sql_timetotal += $this->cur_query_time;
                $this->DBS['sql_timetotal'] += $this->cur_query_time;

                if (SQL_LOG_SLOW_QUERIES && $this->cur_query_time > $this->slow_time) {
                    $this->log_slow_query();
                }
            }
            if ($this->dbg_enabled) {
                $dbg['time'] = utime() - $this->sql_starttime;
                $dbg['info'] = $this->query_info();
                $dbg['mem_after'] = sys('mem');
                $id++;
            }
            if ($this->do_explain) {
                $this->explain('stop');
            }
            // проверка установки $this->inited - для пропуска инициализационных запросов
            if ($this->DBS['log_counter'] && $this->inited) {
                $this->log_query($this->DBS['log_file']);
                $this->DBS['log_counter']--;
            }
        }
    }

    /**
     * Trigger error
     *
     * @param string $msg
     */
    public function trigger_error($msg = 'DB Error')
    {
        if (error_reporting()) {
            if (DBG_LOG === true) {
                $err = $this->sql_error();
                $msg .= "\n" . trim(sprintf('#%06d %s', $err['code'], $err['message']));
            } else {
                $msg .= ' [' . $this->debug_find_source() . ']';
            }

            trigger_error($msg, E_USER_ERROR);
        }
    }

    /**
     * Find caller source
     *
     * @param string $mode
     *
     * @return string
     */
    public function debug_find_source($mode = '')
    {
        foreach (debug_backtrace() as $trace) {
            if (!empty($trace['file']) && $trace['file'] !== __FILE__) {
                switch ($mode) {
                    case 'file':
                        return $trace['file'];
                    case 'line':
                        return $trace['line'];
                    default:
                        return hide_bb_path($trace['file']) . '(' . $trace['line'] . ')';
                }
            }
        }
        return '';
    }

    /**
     * Prepare for logging
     * @param int $queries_count
     * @param string $log_file
     */
    public function log_next_query($queries_count = 1, $log_file = 'sql_queries')
    {
        $this->DBS['log_file'] = $log_file;
        $this->DBS['log_counter'] = $queries_count;
    }

    /**
     * Log query
     *
     * @param string $log_file
     */
    public function log_query($log_file = 'sql_queries')
    {
        $q_time = ($this->cur_query_time >= 10) ? round($this->cur_query_time, 0) : sprintf('%.4f', $this->cur_query_time);
        $msg = [];
        $msg[] = round($this->sql_starttime);
        $msg[] = date('m-d H:i:s', $this->sql_starttime);
        $msg[] = sprintf('%-6s', $q_time);
        $msg[] = sprintf('%-4s', round(sys('la'), 1));
        $msg[] = sprintf('%05d', getmypid());
        $msg[] = $this->db_server;
        $msg[] = short_query($this->cur_query);
        $msg = implode(LOG_SEPR, $msg);
        $msg .= ($info = $this->query_info()) ? ' # ' . $info : '';
        $msg .= ' # ' . $this->debug_find_source() . ' ';
        $msg .= defined('IN_CRON') ? 'cron' : basename($_SERVER['REQUEST_URI']);
        bb_log($msg . LOG_LF, $log_file);
    }

    /**
     * Log slow query
     *
     * @param string $log_file
     */
    public function log_slow_query($log_file = 'sql_slow_bb')
    {
        if (!defined('IN_FIRST_SLOW_QUERY') && CACHE('bb_cache')->get('dont_log_slow_query')) {
            return;
        }
        $this->log_query($log_file);
    }

    /**
     * Log error
     */
    public function log_error()
    {
        if (!SQL_LOG_ERRORS) {
            return;
        }

        $msg = [];
        $err = $this->sql_error();
        $msg[] = str_compact(sprintf('#%06d %s', $err['code'], $err['message']));
        $msg[] = '';
        $msg[] = str_compact($this->cur_query);
        $msg[] = '';
        $msg[] = 'Source  : ' . $this->debug_find_source() . " :: $this->db_server.$this->selected_db";
        $msg[] = 'IP      : ' . @$_SERVER['REMOTE_ADDR'];
        $msg[] = 'Date    : ' . date('Y-m-d H:i:s');
        $msg[] = 'Agent   : ' . @$_SERVER['HTTP_USER_AGENT'];
        $msg[] = 'Req_URI : ' . @$_SERVER['REQUEST_URI'];
        $msg[] = 'Referer : ' . @$_SERVER['HTTP_REFERER'];
        $msg[] = 'Method  : ' . @$_SERVER['REQUEST_METHOD'];
        $msg[] = 'PID     : ' . sprintf('%05d', getmypid());
        $msg[] = 'Request : ' . trim(print_r($_REQUEST, true)) . str_repeat('_', 78) . LOG_LF;
        $msg[] = '';
        bb_log($msg, 'sql_error_bb');
    }

    /**
     * Explain queries
     *
     * @param $mode
     * @param string $html_table
     * @param string $row
     *
     * @return bool|string
     */
    public function explain($mode, $html_table = '', $row = '')
    {
        $query = str_compact($this->cur_query);
        // remove comments
        $query = preg_replace('#(\s*)(/\*)(.*)(\*/)(\s*)#', '', $query);

        switch ($mode) {
            case 'start':
                $this->explain_hold = '';

                if (preg_match('#UPDATE ([a-z0-9_]+).*?WHERE(.*)/#', $query, $m)) {
                    $query = "SELECT * FROM $m[1] WHERE $m[2]";
                } elseif (preg_match('#DELETE FROM ([a-z0-9_]+).*?WHERE(.*)#s', $query, $m)) {
                    $query = "SELECT * FROM $m[1] WHERE $m[2]";
                }

                if (preg_match('#^SELECT#', $query)) {
                    $html_table = false;

                    if ($result = mysqli_query($this->link, "EXPLAIN $query")) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $html_table = $this->explain('add_explain_row', $html_table, $row);
                        }
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
                $htid = 'expl-' . spl_object_hash($this->link) . '-' . $id;
                $dbg = $this->dbg[$id];

                $this->explain_out .= '
				<table width="98%" cellpadding="0" cellspacing="0" class="bodyline row2 bCenter" style="border-bottom: 0;">
				<tr>
					<th style="height: 22px; cursor: pointer;" align="left">&nbsp;' . $dbg['src'] . '&nbsp; [' . sprintf('%.4f', $dbg['time']) . ' s]&nbsp; <i>' . $dbg['info'] . '</i></th>
					<th style="height: 22px; cursor: pointer;" align="right" title="Copy to clipboard" onclick="$.copyToClipboard( $(\'#' . $htid . '\').text() );">' . "$this->db_server.$this->selected_db" . ' :: Query #' . ($this->num_queries + 1) . '&nbsp;</th>
				</tr>
				<tr><td colspan="2">' . $this->explain_hold . '</td></tr>
				</table>
				<div class="sqlLog"><div id="' . $htid . '" class="sqlLogRow sqlExplain" style="padding: 0;">' . short_query($dbg['sql'], true) . '&nbsp;&nbsp;</div></div>
				<br />';
                break;

            case 'add_explain_row':
                if (!$html_table && $row) {
                    $html_table = true;
                    $this->explain_hold .= '<table width="100%" cellpadding="3" cellspacing="1" class="bodyline" style="border-width: 0;"><tr>';
                    foreach (array_keys($row) as $val) {
                        $this->explain_hold .= '<td class="row3 gensmall" align="center"><b>' . $val . '</b></td>';
                    }
                    $this->explain_hold .= '</tr>';
                }
                $this->explain_hold .= '<tr>';
                foreach (array_values($row) as $i => $val) {
                    $class = !($i % 2) ? 'row1' : 'row2';
                    $this->explain_hold .= '<td class="' . $class . ' gen">' . str_replace(["{$this->selected_db}.", ',', ';'], ['', ', ', ';<br />'], $val) . '</td>';
                }
                $this->explain_hold .= '</tr>';

                return $html_table;

                break;

            case 'display':
                echo '<a name="explain"></a><div class="med">' . $this->explain_out . '</div>';
                break;
        }
    }
}
