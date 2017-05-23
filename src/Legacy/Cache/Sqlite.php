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

namespace TorrentPier\Legacy\Cache;

use SQLite3;

/**
 * Class Sqlite
 * @package TorrentPier\Legacy\Cache
 */
class Sqlite extends Common
{
    public $used = true;
    public $db;
    public $prefix;
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
        $this->db = new SqliteCommon($this->cfg);
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
			WHERE cache_name IN('$this->prefix_sql" . implode("','$this->prefix_sql", $name_sql) . "') AND cache_expire_time > " . TIMENOW . "
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
        } else {
            return isset($cached_items[$name]) ? $cached_items[$name] : false;
        }
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
        return $result ? $this->db->changes() : 0;
    }
}
