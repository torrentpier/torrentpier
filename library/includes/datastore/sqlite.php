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

class datastore_sqlite extends datastore_common
{
    public $engine = 'SQLite';
    public $db = null;
    public $prefix = null;
    public $cfg = array(
        'db_file_path' => '/path/to/datastore.db.sqlite',
        'table_name' => 'datastore',
        'table_schema' => 'CREATE TABLE datastore (
	            ds_title       VARCHAR(255),
	            ds_data        TEXT,
	            PRIMARY KEY (ds_title)
	        )',
        'pconnect' => true,
        'con_required' => true,
        'log_name' => 'DATASTORE',
    );

    /**
     * datastore_sqlite constructor.
     * @param $cfg
     * @param null $prefix
     * @return datastore_sqlite
     */
    public function datastore_sqlite($cfg, $prefix = null)
    {
        $this->cfg = array_merge($this->cfg, $cfg);
        $this->db = new sqlite_common($this->cfg);
        $this->prefix = $prefix;
    }

    /**
     * @param $item_name
     * @param $item_data
     * @return bool
     */
    public function store($item_name, $item_data)
    {
        $this->data[$item_name] = $item_data;

        $ds_title = SQLite3::escapeString($this->prefix . $item_name);
        $ds_data = SQLite3::escapeString(serialize($item_data));

        $result = $this->db->query("REPLACE INTO " . $this->cfg['table_name'] . " (ds_title, ds_data) VALUES ('$ds_title', '$ds_data')");

        return (bool)$result;
    }

    /**
     * Очистка
     */
    public function clean()
    {
        $this->db->query("DELETE FROM " . $this->cfg['table_name']);
    }

    /**
     * Получение из кеша
     */
    public function _fetch_from_store()
    {
        if (!$items = $this->queued_items) {
            return;
        }

        $prefix_len = strlen($this->prefix);
        $prefix_sql = SQLite3::escapeString($this->prefix);

        array_deep($items, 'SQLite3::escapeString');
        $items_list = $prefix_sql . join("','$prefix_sql", $items);

        $rowset = $this->db->fetch_rowset("SELECT ds_title, ds_data FROM " . $this->cfg['table_name'] . " WHERE ds_title IN ('$items_list')");

        $this->db->debug('start', "unserialize()");
        foreach ($rowset as $row) {
            $this->data[substr($row['ds_title'], $prefix_len)] = unserialize($row['ds_data']);
        }
        $this->db->debug('stop');
    }
}
