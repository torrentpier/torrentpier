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
    public int $leechers;
    public int $seeders;

    public function __construct(array $infoHashes, array $trackers)
    {
        $scraper = new Scraper();
        $announcerInfo = $scraper->scrape($infoHashes, $trackers);

        $seeders = $leechers = 0;
        if (!$scraper->hasErrors()) {
            foreach ($infoHashes as $infoHash) {
                $announcerInfo = $announcerInfo[$infoHash];
                $seeders = $announcerInfo['seeders'];
                $leechers = $announcerInfo['leechers'];
            }
        } else {
            dump($scraper->getErrors());
        }

        //dd([$seeders, $leechers]);

        $this->leechers = $leechers;
        $this->seeders = $seeders;
    }
}
