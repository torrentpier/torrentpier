<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy\Datastore;

use TorrentPier\Dev;

use MatthiasMullie\Scrapbook\Adapters\Apc;

use Exception;

/**
 * Class APCu
 * @package TorrentPier\Legacy\Datastore
 */
class APCu extends Common
{
    /**
     * Cache driver name
     *
     * @var string
     */
    public string $engine = 'APCu';

    /**
     * Cache prefix
     *
     * @var string
     */
    private string $prefix;

    /**
     * Adapters\Apc class
     *
     * @var Apc
     */
    private Apc $apcu;

    /**
     * APCu constructor
     *
     * @param string $prefix
     */
    public function __construct(string $prefix)
    {
        if (!$this->isInstalled()) {
            throw new Exception('ext-apcu not installed. Check out php.ini file');
        }
        $this->apcu = new Apc();
        $this->prefix = $prefix;
        $this->dbg_enabled = dev()->checkSqlDebugAllowed();
    }

    /**
     * Store data into cache
     *
     * @param string $item_name
     * @param mixed $item_data
     * @return bool
     */
    public function store(string $item_name, mixed $item_data): bool
    {
        $this->data[$item_name] = $item_data;
        $item_name = $this->prefix . $item_name;

        $this->cur_query = "cache->" . __FUNCTION__ . "('$item_name')";
        $this->debug('start');

        $result = $this->apcu->set($item_name, $item_data);

        $this->debug('stop');
        $this->cur_query = null;
        $this->num_queries++;

        return $result;
    }

    /**
     * Removes data from cache
     *
     * @return void
     */
    public function clean(): void
    {
        foreach ($this->known_items as $title => $script_name) {
            $title = $this->prefix . $title;
            $this->cur_query = "cache->rm('$title')";
            $this->debug('start');

            $this->apcu->delete($title);

            $this->debug('stop');
            $this->cur_query = null;
            $this->num_queries++;
        }
    }

    /**
     * Fetch cache from store
     *
     * @return void
     */
    public function _fetch_from_store(): void
    {
        $item = null;
        if (!$items = $this->queued_items) {
            $src = $this->_debug_find_caller('enqueue');
            trigger_error("Datastore: item '$item' already enqueued [$src]", E_USER_ERROR);
        }

        foreach ($items as $item) {
            $item_title = $this->prefix . $item;
            $this->cur_query = "cache->get('$item_title')";
            $this->debug('start');

            $this->data[$item] = $this->apcu->get($item_title);

            $this->debug('stop');
            $this->cur_query = null;
            $this->num_queries++;
        }
    }

    /**
     * Checking if APCu is installed
     *
     * @return bool
     */
    private function isInstalled(): bool
    {
        return extension_loaded('apcu') && function_exists('apcu_fetch');
    }
}
