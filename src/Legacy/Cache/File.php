<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy\Cache;

use TorrentPier\Dev;

/**
 * Class File
 * @package TorrentPier\Legacy\Cache
 */
class File extends Common
{
    public $used = true;
    public $engine = 'Filecache';
    public $dir;
    public $prefix;

    public function __construct($dir, $prefix = null)
    {
        $this->dir = $dir;
        $this->prefix = $prefix;
        $this->dbg_enabled = Dev::sql_dbg_enabled();
    }

    public function get($name, $get_miss_key_callback = '', $ttl = 0)
    {
        $filecache = [];
        $filename = $this->dir . clean_filename($this->prefix . $name) . '.php';

        $this->cur_query = "cache->get('$name')";
        $this->debug('start');

        if (file_exists($filename)) {
            require($filename);
        }

        $this->debug('stop');
        $this->cur_query = null;

        return (!empty($filecache['value'])) ? $filecache['value'] : false;
    }

    public function set($name, $value, $ttl = 86400)
    {
        if (!\function_exists('var_export')) {
            return false;
        }

        $this->cur_query = "cache->set('$name')";
        $this->debug('start');

        $filename = $this->dir . clean_filename($this->prefix . $name) . '.php';
        $expire = TIMENOW + $ttl;
        $cache_data = ['expire' => $expire, 'value' => $value];

        $filecache = "<?php\n";
        $filecache .= "if (!defined('BB_ROOT')) die(basename(__FILE__));\n";
        $filecache .= '$filecache = ' . var_export($cache_data, true) . ";\n";
        $filecache .= '?>';

        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        return (bool)file_write($filecache, $filename, max_size: false, replace_content: true);
    }

    public function rm($name = '')
    {
        $clear = false;
        if ($name) {
            $this->cur_query = "cache->rm('$name')";
            $this->debug('start');

            $filename = $this->dir . clean_filename($this->prefix . $name) . '.php';
            if (file_exists($filename)) {
                $clear = (bool)unlink($filename);
            }

            $this->debug('stop');
            $this->cur_query = null;
            $this->num_queries++;
        } else {
            if (is_dir($this->dir)) {
                if ($dh = opendir($this->dir)) {
                    while (($file = readdir($dh)) !== false) {
                        if ($file != "." && $file != "..") {
                            $filename = $this->dir . $file;

                            unlink($filename);
                            $clear = true;
                        }
                    }
                    closedir($dh);
                }
            }
        }
        return $clear;
    }

    public function gc($expire_time = TIMENOW)
    {
        $filecache = [];
        $clear = false;

        if (is_dir($this->dir)) {
            if ($dh = opendir($this->dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != "." && $file != "..") {
                        $filename = $this->dir . $file;

                        require($filename);

                        if (!empty($filecache['expire']) && ($filecache['expire'] < $expire_time)) {
                            unlink($filename);
                            $clear = true;
                        }
                    }
                }
                closedir($dh);
            }
        }

        return $clear;
    }
}
