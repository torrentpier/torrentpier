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
    /**
     * Leechers count
     *
     * @var int
     */
    public int $leechers;

    /**
     * Seeders count
     *
     * @var int
     */
    public int $seeders;

    /**
     * MultiTracker constructor
     *
     * @param array $infoHashes
     * @param array $trackers
     */
    public function __construct(array $infoHashes, array $trackers)
    {
        global $bb_cfg;

        $scraper = new Scraper();
        $announcerInfo = $scraper->scrape(
            $infoHashes,
            $trackers,
            $bb_cfg['tracker']['multitracker']['max_trackers'],
            $bb_cfg['tracker']['multitracker']['timeout'],
        );

        $seeders = $leechers = 0;
        if (!$scraper->hasErrors()) {
            foreach ($infoHashes as $infoHash) {
                $announcerInfo = $announcerInfo[$infoHash];
                $seeders = (int)$announcerInfo['seeders'];
                $leechers = (int)$announcerInfo['leechers'];
            }
        } else {
            dump($scraper->getErrors());
        }

        $this->leechers = $leechers;
        $this->seeders = $seeders;
    }
}
