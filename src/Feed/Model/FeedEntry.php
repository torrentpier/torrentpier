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
 * Value object representing a single feed entry
 */
readonly class FeedEntry
{
    /**
     * @param string $title Entry title
     * @param string $link Entry URL
     * @param DateTimeImmutable $lastModified Last modification date
     * @param string $author Author name
     * @param string|null $description Optional entry description/content
     */
    public function __construct(
        public string $title,
        public string $link,
        public DateTimeImmutable $lastModified,
        public string $author,
        public ?string $description = null
    ) {
    }
}
