<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use Nemorize\Indexnow\Exceptions\IndexnowException;

/**
 * Class IndexNow
 * @package TorrentPier
 */
class IndexNow
{
    private \Nemorize\Indexnow\Indexnow $indexNow;

    public function __construct()
    {
        global $bb_cfg;

        $this->indexNow = new \Nemorize\Indexnow\Indexnow();
        $this->indexNow->setKey($bb_cfg['indexnow_key']);
    }

    /**
     * Submit page to IndexNow
     *
     * @param string $url
     * @return void
     */
    public function submit(string $url): void
    {
        try {
            $this->indexNow->submit($url);
        } catch (IndexnowException $e) {
            bb_die($e->getMessage());
        }
    }
}
