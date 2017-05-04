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

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

class cache_sqlite extends cache_common
{
    public $used = true;
    public $db = null;
    public $prefix = null;
    public $cfg = array(
        'db_file_path' => '/path/to/cache.db.sqlite',
        'table_name' => 'cache',
        'table_schema' => 'CREATE TABLE cache (
	                cache_name        VARCHAR(255),
	                cache_expire_time INT,
	                cache_value       TEXT,
	                PRIMARY KEY (cache_name)
	        )',
        'pconnect' => true,
        'con_required' => true,
        'log_name' => 'CACHE',
    );

    public function __construct($cfg, $prefix = null)
    {
        $this->cfg = array_merge($this->cfg, $cfg);
        $this->db = new sqlite_common($this->cfg);
        $this->prefix = $prefix;
    }

    public function get($name, $get_miss_key_callback = '', $ttl = 604800)
    {
        if (empty($name)) {
            return is_array($name) ? array() : false;
        }
        $this->db->shard($name);
        $cached_items = array();
        $this->prefix_len = strlen($this->prefix);
        $this->prefix_sql = SQLite3::escapeString($this->prefix);

        $name_ary = $name_sql = (array)$name;
        array_deep($name_sql, 'SQLite3::escapeString');

        // get available items
        $rowset = $this->db->fetch_rowset("
			SELECT cache_name, cache_value
			FROM " . $this->cfg['table_name'] . "
			WHERE cache_name IN('$this->prefix_sql" . join("','$this->prefix_sql", $name_sql) . "') AND cache_expire_time > " . TIMENOW . "
			LIMIT " . count($name) . "
		");

        $this->db->debug('start', 'unserialize()');
        foreach ($rowset as $row) {
            $cached_items[substr($row['cache_name'], $this->prefix_len)] = unserialize($row['cache_value']);
        }
        $this->db->debug('stop');

        // get miss items
        if ($get_miss_key_callback and $miss_key = array_diff($name_ary, array_keys($cached_items))) {
            foreach ($get_miss_key_callback($miss_key) as $k => $v) {
                $this->set($this->prefix . $k, $v, $ttl);
                $cached_items[$k] = $v;
            }
        }
        // return
        if (is_array($this->prefix . $name)) {
            return $cached_items;
        }

        return isset($cached_items[$name]) ? $cached_items[$name] : false;
    }

    public function set($name, $value, $ttl = 604800)
    {
        $this->db->shard($this->prefix . $name);
        $name_sql = SQLite3::escapeString($this->prefix . $name);
        $expire = TIMENOW + $ttl;
        $value_sql = SQLite3::escapeString(serialize($value));

        $result = $this->db->query("REPLACE INTO " . $this->cfg['table_name'] . " (cache_name, cache_expire_time, cache_value) VALUES ('$name_sql', $expire, '$value_sql')");
        return (bool)$result;
    }

    public function rm($name = '')
    {
        if ($name) {
            $this->db->shard($this->prefix . $name);
            $result = $this->db->query("DELETE FROM " . $this->cfg['table_name'] . " WHERE cache_name = '" . SQLite3::escapeString($this->prefix . $name) . "'");
        } else {
            $result = $this->db->query("DELETE FROM " . $this->cfg['table_name']);
        }
        return (bool)$result;
    }

    public function gc($expire_time = TIMENOW)
    {
        $result = $this->db->query("DELETE FROM " . $this->cfg['table_name'] . " WHERE cache_expire_time < $expire_time");
        return ($result) ? $this->db->changes() : 0;
    }
}

class sqlite_common extends cache_common
{
    public $cfg = array(
        'db_file_path' => 'sqlite.db',
        'table_name' => 'table_name',
        'table_schema' => 'CREATE TABLE table_name (...)',
        'pconnect' => true,
        'con_required' => true,
        'log_name' => 'SQLite',
        'shard_type' => 'none',     #  none, string, int (тип перевичного ключа для шардинга)
        'shard_val' => 0,          #  для string - кол. начальных символов, для int - делитель (будет использован остаток от деления)
    );
    public $engine = 'SQLite';
    public $dbh = null;
    public $connected = false;
    public $shard_val = false;

    public $table_create_attempts = 0;

    public function __construct($cfg)
    {
        $this->cfg = array_merge($this->cfg, $cfg);
        $this->dbg_enabled = sql_dbg_enabled();
    }

    public function connect()
    {
        $this->cur_query = ($this->dbg_enabled) ? 'connect to: ' . $this->cfg['db_file_path'] : 'connect';
        $this->debug('start');

        if (@$this->dbh = new SQLite3($this->cfg['db_file_path'])) {
            $this->connected = true;
        }

        if (DBG_LOG) {
            dbg_log(' ', $this->cfg['log_name'] . '-connect' . ($this->connected ? '' : '-FAIL'));
        }

        if (!$this->connected && $this->cfg['con_required']) {
            trigger_error('SQLite not connected', E_USER_ERROR);
        }

        $this->debug('stop');
        $this->cur_query = null;
    }

    public function create_table()
    {
        $this->table_create_attempts++;
        return $this->dbh->query($this->cfg['table_schema']);
    }

    public function shard($name)
    {
        $type = $this->cfg['shard_type'];

        if ($type == 'none') {
            return;
        }
        if (is_array($name)) {
            trigger_error('cannot shard: $name is array', E_USER_ERROR);
        }

        // define shard_val
        if ($type == 'string') {
            $shard_val = substr($name, 0, $this->cfg['shard_val']);
        } else {
            $shard_val = $name % $this->cfg['shard_val'];
        }
        // все запросы должны быть к одному и тому же шарду
        if ($this->shard_val !== false) {
            if ($shard_val != $this->shard_val) {
                trigger_error("shard cannot be reassigned. [{$this->shard_val}, $shard_val, $name]", E_USER_ERROR);
            } else {
                return;
            }
        }
        $this->shard_val = $shard_val;
        $this->cfg['db_file_path'] = str_replace('*', $shard_val, $this->cfg['db_file_path']);
    }

    public function query($query)
    {
        if (!$this->connected) {
            $this->connect();
        }

        $this->cur_query = $query;
        $this->debug('start');

        if (!$result = @$this->dbh->query($query)) {
            $rowsresult = $this->dbh->query("PRAGMA table_info({$this->cfg['table_name']})");
            $rowscount = 0;
            while ($row = $rowsresult->fetchArray(SQLITE3_ASSOC)) {
                $rowscount++;
            }
            if (!$this->table_create_attempts && !$rowscount) {
                if ($this->create_table()) {
                    $result = $this->dbh->query($query);
                }
            }
            if (!$result) {
                $this->trigger_error($this->get_error_msg());
            }
        }

        $this->debug('stop');
        $this->cur_query = null;

        $this->num_queries++;

        return $result;
    }

    public function fetch_row($query)
    {
        $result = $this->query($query);
        return is_resource($result) ? $result->fetchArray(SQLITE3_ASSOC) : false;
    }

    public function fetch_rowset($query)
    {
        $result = $this->query($query);
        $rowset = array();
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $rowset[] = $row;
        }
        return $rowset;
    }

    public function changes()
    {
        return is_resource($this->dbh) ? $this->dbh->changes() : 0;
    }

    public function escape($str)
    {
        return SQLite3::escapeString($str);
    }

    public function get_error_msg()
    {
        return 'SQLite error #' . ($err_code = $this->dbh->lastErrorCode()) . ': ' . $this->dbh->lastErrorMsg();
    }

    public function rm($name = '')
    {
        if ($name) {
            $this->db->shard($this->prefix . $name);
            $result = $this->db->query("DELETE FROM " . $this->cfg['table_name'] . " WHERE cache_name = '" . SQLite3::escapeString($this->prefix . $name) . "'");
        } else {
            $result = $this->db->query("DELETE FROM " . $this->cfg['table_name']);
        }
        return (bool)$result;
    }

    public function gc($expire_time = TIMENOW)
    {
        $result = $this->db->query("DELETE FROM " . $this->cfg['table_name'] . " WHERE cache_expire_time < $expire_time");
        return ($result) ? sqlite_changes($this->db->dbh) : 0;
    }

    public function trigger_error($msg = 'DB Error')
    {
        if (error_reporting()) {
            trigger_error($msg, E_USER_ERROR);
        }
    }
}
