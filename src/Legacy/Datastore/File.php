<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy\Datastore;

use TorrentPier\Dev;

/**
 * Class File
 * @package TorrentPier\Legacy\Datastore
 */
class File extends Common
{
    public $dir;
    public $prefix;
    public $engine = 'Filecache';

    public function __construct($dir, $prefix = null)
    {
        $this->prefix = $prefix;
        $this->dir = $dir;
        $this->dbg_enabled = Dev::sql_dbg_enabled();
    }

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

        return (bool)file_write($filecache, $filename, max_size: false, replace_content: true);
    }

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

    public function _fetch_from_store()
    {
        $item = null;
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

            if (is_file($filename) && is_readable($filename)) {
                require($filename);

                $this->data[$item] = $filecache;
            }
        }
    }
}
