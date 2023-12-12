<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy;

/**
 * Class Caches
 * @package TorrentPier\Legacy
 */
class Caches
{
    public $cfg = []; // config
    public $obj = []; // cache-objects
    public $ref = []; // links to $obj (cache_name => cache_objects)

    public function __construct($cfg)
    {
        $this->cfg = $cfg['cache'];
        $this->obj['__stub'] = new Cache\Common();
    }

    public function get_cache_obj($cache_name)
    {
        if (!isset($this->ref[$cache_name])) {
            if (!$engine_cfg =& $this->cfg['engines'][$cache_name]) {
                $this->ref[$cache_name] =& $this->obj['__stub'];
            } else {
                $cache_type =& $engine_cfg[0];
                $cache_cfg =& $engine_cfg[1];

                switch ($cache_type) {
                    case 'memcache':
                        if (!isset($this->obj[$cache_name])) {
                            $this->obj[$cache_name] = new Cache\Memcache($this->cfg['memcache'], $this->cfg['prefix']);
                        }
                        $this->ref[$cache_name] =& $this->obj[$cache_name];
                        break;
                    case 'sqlite':
                        if (!isset($this->obj[$cache_name])) {
                            $cache_cfg['pconnect'] = $this->cfg['pconnect'];
                            $cache_cfg['db_file_path'] = $this->get_db_path($cache_name, $cache_cfg, '.sqlite.db');

                            $this->obj[$cache_name] = new Cache\Sqlite($cache_cfg, $this->cfg['prefix']);
                        }
                        $this->ref[$cache_name] =& $this->obj[$cache_name];
                        break;
                    case 'redis':
                        if (!isset($this->obj[$cache_name])) {
                            $this->obj[$cache_name] = new Cache\Redis($this->cfg['redis'], $this->cfg['prefix']);
                        }
                        $this->ref[$cache_name] =& $this->obj[$cache_name];
                        break;
                    case 'filecache':
                    default:
                        if (!isset($this->obj[$cache_name])) {
                            $this->obj[$cache_name] = new Cache\File($this->cfg['db_dir'] . $cache_name . '/', $this->cfg['prefix']);
                        }
                        $this->ref[$cache_name] =& $this->obj[$cache_name];
                        break;
                }
            }
        }

        return $this->ref[$cache_name];
    }

    public function get_db_path($name, $cfg, $ext)
    {
        if (!empty($cfg['shard_type']) && $cfg['shard_type'] != 'none') {
            return $this->cfg['db_dir'] . $name . '_*' . $ext;
        }

        return $this->cfg['db_dir'] . $name . $ext;
    }
}
