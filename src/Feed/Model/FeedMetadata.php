<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Feed\Model;

use DateTimeImmutable;

/**
 * Value object representing feed metadata
 */
readonly class FeedMetadata
{
    /**
     * @param string $title Feed title
     * @param string $link Feed main URL
     * @param DateTimeImmutable $lastModified Last modification date
     */
    public function __construct(
        public string $title,
        public string $link,
        public DateTimeImmutable $lastModified
    ) {
    }
}
