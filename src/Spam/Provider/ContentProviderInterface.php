<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Spam\Provider;

use TorrentPier\Spam\ProviderResult;

interface ContentProviderInterface extends ProviderInterface
{
    public function checkContent(int $userId, string $message, array $extra = []): ProviderResult;

    public function submitSpam(int $userId, string $message, array $extra = []): void;

    public function submitHam(int $userId, string $message, array $extra = []): void;
}
