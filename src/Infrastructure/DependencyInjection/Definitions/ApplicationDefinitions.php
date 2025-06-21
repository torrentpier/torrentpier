<?php

declare(strict_types=1);

namespace TorrentPier\Infrastructure\DependencyInjection\Definitions;

use DI;
use Psr\Container\ContainerInterface;

class ApplicationDefinitions
{
    public static function getDefinitions(): array
    {
        return [
            // Command Bus
            // 'CommandBusInterface' => DI\factory(function (ContainerInterface $c) {
            //     return new CommandBus($c);
            // }),

            // Query Bus
            // 'QueryBusInterface' => DI\factory(function (ContainerInterface $c) {
            //     return new QueryBus($c);
            // }),

            // Event Dispatcher
            // 'EventDispatcherInterface' => DI\factory(function (ContainerInterface $c) {
            //     return new EventDispatcher();
            // }),

            // Application Services
            // These typically orchestrate domain objects and handle use cases

            // Forum Handlers
            // 'TorrentPier\Application\Forum\Handler\CreatePostHandler' => DI\autowire(),
            // 'TorrentPier\Application\Forum\Handler\GetThreadListHandler' => DI\autowire(),

            // Tracker Handlers
            // 'TorrentPier\Application\Tracker\Handler\RegisterTorrentHandler' => DI\autowire(),
            // 'TorrentPier\Application\Tracker\Handler\ProcessAnnounceHandler' => DI\autowire(),

            // User Handlers
            // 'TorrentPier\Application\User\Handler\RegisterUserHandler' => DI\autowire(),
            // 'TorrentPier\Application\User\Handler\AuthenticateUserHandler' => DI\autowire(),
        ];
    }
}
