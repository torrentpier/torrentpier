<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Feed\Provider;

use TorrentPier\Feed\Model\FeedEntry;
use TorrentPier\Feed\Model\FeedMetadata;

/**
 * Interface for feed data providers
 */
interface FeedProviderInterface
{
    /**
     * Get a unique cache key for this feed
     */
    public function getCacheKey(): string;

    /**
     * Get feed metadata (title, link, last modified)
     */
    public function getMetadata(): FeedMetadata;

    /**
     * Get feed entries
     *
     * @return FeedEntry[]
     */
    public function getEntries(): array;
}
