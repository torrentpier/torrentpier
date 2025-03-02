<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy;

/**
 * Class Dbs
 * @package TorrentPier\Legacy
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
     * @param array $cfg
     */
    public function __construct(array $cfg)
    {
        $this->cfg = $cfg['db'];
        $this->alias = $cfg['db_alias'];

        foreach ($this->cfg as $srv_name => $srv_cfg) {
            $this->srv[$srv_name] = null;
        }
    }

    /**
     * Initialization / Fetching of $srv_name
     *
     * @param string $srv_name_or_alias
     *
     * @return mixed
     */
    public function get_db_obj(string $srv_name_or_alias = 'db')
    {
        $srv_name = $this->get_srv_name($srv_name_or_alias);

        if (!\is_object($this->srv[$srv_name])) {
            $this->srv[$srv_name] = new SqlDb($this->cfg[$srv_name]);
            $this->srv[$srv_name]->db_server = $srv_name;
        }
        return $this->srv[$srv_name];
    }

    /**
     * Fetching server name
     *
     * @param string $name
     *
     * @return mixed|string
     */
    public function get_srv_name(string $name)
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
