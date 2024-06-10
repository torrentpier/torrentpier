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
 * Class APCu
 * @package TorrentPier\Legacy\Datastore
 */
class APCu extends Common
{
    public string $prefix;
    public string $engine = 'APCu';

    public function __construct($prefix = null)
    {
        global $debug;

        if (!$this->is_installed()) {
            die("Error: $this->engine extension not installed");
        }

        $this->prefix = $prefix;
        $this->dbg_enabled = $debug->sqlDebugAllowed();
    }

    public function store($title, $var)
    {
        $this->data[$title] = $var;

        $this->cur_query = "cache->set('$title')";
        $this->debug('start');
        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        return (bool)apcu_store($this->prefix . $title, $var);
    }

    public function clean()
    {
        foreach ($this->known_items as $title => $script_name) {
            $this->cur_query = "cache->rm('$title')";
            $this->debug('start');
            $this->debug('stop');
            $this->cur_query = null;
            $this->num_queries++;

            apcu_delete($this->prefix . $title);
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
            $this->cur_query = "cache->get('$item')";
            $this->debug('start');
            $this->debug('stop');
            $this->cur_query = null;
            $this->num_queries++;

            $this->data[$item] = apcu_fetch($this->prefix . $item);
        }
    }

    public function is_installed(): bool
    {
        return extension_loaded('apcu') && apcu_enabled();
    }
}
