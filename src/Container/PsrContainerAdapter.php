<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Container;

use Illuminate\Container\Container;
use Psr\Container\ContainerInterface;

/**
 * PSR-11 adapter for Illuminate Container
 *
 * Makes has() return true for autowirable classes,
 * enabling proper integration with League Route.
 */
readonly class PsrContainerAdapter implements ContainerInterface
{
    public function __construct(
        private Container $container,
    ) {}

    public function get(string $id): mixed
    {
        return $this->container->make($id);
    }

    public function has(string $id): bool
    {
        return $this->container->bound($id) || class_exists($id);
    }
}
