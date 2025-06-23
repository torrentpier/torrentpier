<?php

declare(strict_types=1);

namespace TorrentPier\Infrastructure\DependencyInjection\Definitions;

use DI;
use Psr\Container\ContainerInterface;

class DomainDefinitions
{
    public static function getDefinitions(): array
    {
        return [
            // Domain services should not depend on infrastructure
            // These are typically created by factories in the application layer

            // Example domain service factory definitions:
            // 'TorrentPier\Domain\Forum\Repository\ForumRepositoryInterface' => DI\factory(function (ContainerInterface $c) {
            //     return $c->get('TorrentPier\Infrastructure\Persistence\Repository\ForumRepository');
            // }),

            // 'TorrentPier\Domain\Tracker\Repository\TorrentRepositoryInterface' => DI\factory(function (ContainerInterface $c) {
            //     return $c->get('TorrentPier\Infrastructure\Persistence\Repository\TorrentRepository');
            // }),

            // 'TorrentPier\Domain\User\Repository\UserRepositoryInterface' => DI\factory(function (ContainerInterface $c) {
            //     return $c->get('TorrentPier\Infrastructure\Persistence\Repository\UserRepository');
            // }),
        ];
    }
}
