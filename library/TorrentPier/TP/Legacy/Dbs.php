<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2017 TorrentPier (https://torrentpier.me)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TP\Legacy;

/**
 * Class Dbs
 * @package TP\Legacy
 */
class Dbs
{
    public $cfg = [];
    public $srv = [];
    public $alias = [];

    public $log_file = 'sql_queries';
    public $log_counter = 0;
    public $num_queries = 0;
    public $sql_inittime = 0;
    public $sql_timetotal = 0;

    /**
     * Dbs constructor
     *
     * @param $cfg
     */
    public function __construct($cfg)
    {
        $this->cfg = $cfg['db'];
        $this->alias = $cfg['db_alias'];

        foreach ($this->cfg as $srv_name => $srv_cfg) {
            $this->srv[$srv_name] = null;
        }
    }

    /**
     * Получение / инициализация класса сервера $srv_name
     *
     * @param string $srv_name_or_alias
     *
     * @return mixed
     */
    public function get_db_obj($srv_name_or_alias = 'db')
    {
        $srv_name = $this->get_srv_name($srv_name_or_alias);

        if (!is_object($this->srv[$srv_name])) {
            $this->srv[$srv_name] = new SqlDb($this->cfg[$srv_name]);
            $this->srv[$srv_name]->db_server = $srv_name;
        }
        return $this->srv[$srv_name];
    }

    /**
     * Определение имени сервера
     *
     * @param $name
     *
     * @return mixed|string
     */
    public function get_srv_name($name)
    {
        $srv_name = 'db';

        if (isset($this->alias[$name])) {
            $srv_name = $this->alias[$name];
        } elseif (isset($this->cfg[$name])) {
            $srv_name = $name;
        }

        return $srv_name;
    }
}
