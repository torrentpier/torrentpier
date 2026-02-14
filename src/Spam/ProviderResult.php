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

/**
 * Value object representing a single provider's spam check result
 */
readonly class ProviderResult
{
    public function __construct(
        public string $providerName,
        public Decision $decision,
        public string $reason,
        public float $confidence,
        public float $responseTimeMs,
    ) {}
}
