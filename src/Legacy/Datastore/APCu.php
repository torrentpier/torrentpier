<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy\Datastore;

use MatthiasMullie\Scrapbook\Adapters\Apc;
use TorrentPier\Dev;

/**
 * Class APCu
 * @package TorrentPier\Legacy\Datastore
 */
class APCu extends AbstractDatastore
{
    public Apc $apcu;
    public string $prefix = 'tp_';
    public string $engine = 'APCu';

    /**
     * APCu constructor
     *
     * @param string $prefix
     */
    public function __construct(string $prefix)
    {
        $this->apcu = new Apc();
        $this->dbg_enabled = Dev::sql_dbg_enabled();
        $this->prefix = $prefix;
    }

    /**
     * Store in datastore
     *
     * @param string $item_name
     * @param mixed $item_data
     * @return bool
     */
    public function store(string $item_name, mixed $item_data): bool
    {
        $this->data[$item_name] = $item_data;
        $this->cur_query = "cache->set('$item_name')";
        $this->debug('start');

        $store = $this->apcu->set($this->prefix . $item_name, $item_data);

        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        return $store;
    }

    /**
     * Clean datastore
     *
     * @return void
     */
    public function clean(): void
    {
        foreach ($this->known_items as $title => $script_name) {
            $this->cur_query = "cache->rm('$title')";
            $this->debug('start');

            $this->apcu->delete($this->prefix . $title);

            $this->debug('stop');
            $this->cur_query = null;
            $this->num_queries++;
        }
    }

    /**
     * Fetch from datastore
     *
     * @return void
     */
    public function _fetch_from_store(): void
    {
        $items = $this->queued_items;

        foreach ($items as $item) {
            $this->cur_query = "cache->get('$item')";
            $this->debug('start');

            $this->data[$item] = $this->apcu->get($this->prefix . $item);

            $this->debug('stop');
            $this->cur_query = null;
            $this->num_queries++;
        }
    }
}
