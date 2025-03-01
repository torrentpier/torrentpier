<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use TorrentPier\Cache\APCu;
use TorrentPier\Cache\Common;
use TorrentPier\Cache\File;
use TorrentPier\Cache\Memcached;
use TorrentPier\Cache\Redis;
use TorrentPier\Cache\Sqlite;

/**
 * Class Caches
 * @package TorrentPier
 */
class Caches
{
    public $cfg = []; // config
    public $obj = []; // cache-objects
    public $ref = []; // links to $obj (cache_name => cache_objects)

    public function __construct($cfg)
    {
        $this->cfg = $cfg['cache'];
        $this->obj['__stub'] = new Common();
    }

    public function get_cache_obj($cache_name)
    {
        if (!isset($this->ref[$cache_name])) {
            if (!$engine_cfg =& $this->cfg['engines'][$cache_name]) {
                $this->ref[$cache_name] =& $this->obj['__stub'];
            } else {
                $cache_type =& $engine_cfg[0];

                switch ($cache_type) {
                    case 'apcu':
                        if (!isset($this->obj[$cache_name])) {
                            $this->obj[$cache_name] = new APCu($this->cfg['prefix']);
                        }
                        $this->ref[$cache_name] =& $this->obj[$cache_name];
                        break;
                    case 'memcached':
                        if (!isset($this->obj[$cache_name])) {
                            $this->obj[$cache_name] = new Memcached($this->cfg['memcached'], $this->cfg['prefix']);
                        }
                        $this->ref[$cache_name] =& $this->obj[$cache_name];
                        break;
                    case 'sqlite':
                        if (!isset($this->obj[$cache_name])) {
                            $this->obj[$cache_name] = new Sqlite($this->cfg['db_dir'] . $cache_name, $this->cfg['prefix']);
                        }
                        $this->ref[$cache_name] =& $this->obj[$cache_name];
                        break;
                    case 'redis':
                        if (!isset($this->obj[$cache_name])) {
                            $this->obj[$cache_name] = new Redis($this->cfg['redis'], $this->cfg['prefix']);
                        }
                        $this->ref[$cache_name] =& $this->obj[$cache_name];
                        break;
                    case 'filecache':
                    default:
                        if (!isset($this->obj[$cache_name])) {
                            $this->obj[$cache_name] = new File($this->cfg['db_dir'] . $cache_name . '/', $this->cfg['prefix']);
                        }
                        $this->ref[$cache_name] =& $this->obj[$cache_name];
                        break;
                }
            }
        }

        return $this->ref[$cache_name];
    }
}
