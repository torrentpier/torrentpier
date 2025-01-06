<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

/**
 * Class MultiTracker
 * @package TorrentPier
 */
class MultiTracker
{
    public function __construct(array $infoHashes, array $trackers)
    {
        $scraper = new Scraper();
        $info = $scraper->scrape($infoHashes, $trackers);
    }
}
