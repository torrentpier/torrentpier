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

class datastore_file extends datastore_common
{
    public $dir = null;
    public $prefix = null;
    public $engine = 'Filecache';

    /**
     * datastore_file constructor.
     * @param $dir
     * @param null $prefix
     * @return datastore_file
     */
    public function datastore_file($dir, $prefix = null)
    {
        $this->prefix = $prefix;
        $this->dir = $dir;
        $this->dbg_enabled = sql_dbg_enabled();
    }

    /**
     * @param $title
     * @param $var
     * @return bool
     */
    public function store($title, $var)
    {
        $this->cur_query = "cache->set('$title')";
        $this->debug('start');

        $this->data[$title] = $var;

        $filename = $this->dir . clean_filename($this->prefix . $title) . '.php';

        $filecache = "<?php\n";
        $filecache .= "if (!defined('BB_ROOT')) die(basename(__FILE__));\n";
        $filecache .= '$filecache = ' . var_export($var, true) . ";\n";
        $filecache .= '?>';

        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        return (bool)file_write($filecache, $filename, false, true, true);
    }

    /**
     * Очистка
     */
    public function clean()
    {
        $dir = $this->dir;

        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != "." && $file != "..") {
                        $filename = $dir . $file;

                        unlink($filename);
                    }
                }
                closedir($dh);
            }
        }
    }

    /**
     * Получение из кеша
     */
    public function _fetch_from_store()
    {
        if (!$items = $this->queued_items) {
            $src = $this->_debug_find_caller('enqueue');
            trigger_error("Datastore: item '$item' already enqueued [$src]", E_USER_ERROR);
        }

        foreach ($items as $item) {
            $filename = $this->dir . $this->prefix . $item . '.php';

            $this->cur_query = "cache->get('$item')";
            $this->debug('start');
            $this->debug('stop');
            $this->cur_query = null;
            $this->num_queries++;

            if (file_exists($filename)) {
                require($filename);

                $this->data[$item] = $filecache;
            }
        }
    }
}
