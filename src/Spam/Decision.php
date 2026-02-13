<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Spam;

enum Decision: int
{
    case Allowed = 1;
    case Moderated = 2;
    case Denied = 3;

    /**
     * Return the more severe of two decisions
     */
    public function escalate(self $other): self
    {
        return $this->value >= $other->value ? $this : $other;
    }
}
