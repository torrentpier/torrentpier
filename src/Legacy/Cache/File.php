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
        $this->dbg_enabled = sql_dbg_enabled();
    }

    public function get($name, $get_miss_key_callback = '', $ttl = 0)
    {
        $filename = $this->dir . clean_filename($this->prefix . $name) . '.php';

        $this->cur_query = "cache->set('$name')";
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
        if (!function_exists('var_export')) {
            return false;
        }

        $this->cur_query = "cache->set('$name')";
        $this->debug('start');

        $filename = $this->dir . clean_filename($this->prefix . $name) . '.php';
        $expire = TIMENOW + $ttl;
        $cache_data = array(
            'expire' => $expire,
            'value' => $value,
        );

        $filecache = "<?php\n";
        $filecache .= "if (!defined('BB_ROOT')) die(basename(__FILE__));\n";
        $filecache .= '$filecache = ' . var_export($cache_data, true) . ";\n";
        $filecache .= '?>';

        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        return (bool)file_write($filecache, $filename, false, true, true);
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
