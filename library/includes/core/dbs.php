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

class dbs
{
    public $cfg = array();   // $srv_name  => $srv_cfg
    public $srv = array();   // $srv_name  => $db_obj
    public $alias = array();   // $srv_alias => $srv_name

    public $log_file = 'sql_queries';
    public $log_counter = 0;
    public $num_queries = 0;
    public $sql_inittime = 0;
    public $sql_timetotal = 0;

    /**
     * dbs constructor.
     * @param $cfg
     * @return dbs
     */
    public function DBS($cfg)
    {
        $this->cfg = $cfg['db'];
        $this->alias = $cfg['db_alias'];

        foreach ($this->cfg as $srv_name => $srv_cfg) {
            $this->srv[$srv_name] = null;
        }
    }

    // получение/инициализация класса для сервера $srv_name
    /**
     * @param string $srv_name_or_alias
     * @return mixed
     */
    public function get_db_obj($srv_name_or_alias = 'db1')
    {
        $srv_name = $this->get_srv_name($srv_name_or_alias);

        if (!is_object($this->srv[$srv_name])) {
            $this->srv[$srv_name] = new sql_db($this->cfg[$srv_name]);
            $this->srv[$srv_name]->db_server = $srv_name;
        }
        return $this->srv[$srv_name];
    }

    // определение имени сервера
    /**
     * @param $name
     * @return mixed|string
     */
    public function get_srv_name($name)
    {
        if (isset($this->alias[$name])) {
            $srv_name = $this->alias[$name];
        } elseif (isset($this->cfg[$name])) {
            $srv_name = $name;
        } else {
            $srv_name = 'db1';
        }
        return $srv_name;
    }
}
