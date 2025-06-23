<?php

declare(strict_types=1);

namespace TorrentPier\Infrastructure\DependencyInjection\Definitions;

use DI;
use Psr\Container\ContainerInterface;

class PresentationDefinitions
{
    public static function getDefinitions(): array
    {
        return [
            // HTTP Controllers
            // Controllers are typically autowired with their dependencies

            // Web Controllers
            // 'TorrentPier\Presentation\Http\Controllers\Web\HomeController' => DI\autowire(),
            // 'TorrentPier\Presentation\Http\Controllers\Web\ForumController' => DI\autowire(),
            // 'TorrentPier\Presentation\Http\Controllers\Web\TrackerController' => DI\autowire(),
            // 'TorrentPier\Presentation\Http\Controllers\Web\UserController' => DI\autowire(),

            // API Controllers
            // 'TorrentPier\Presentation\Http\Controllers\Api\UserController' => DI\autowire(),
            // 'TorrentPier\Presentation\Http\Controllers\Api\TorrentController' => DI\autowire(),
            // 'TorrentPier\Presentation\Http\Controllers\Api\ForumController' => DI\autowire(),

            // Admin Controllers
            // 'TorrentPier\Presentation\Http\Controllers\Admin\DashboardController' => DI\autowire(),
            // 'TorrentPier\Presentation\Http\Controllers\Admin\UserController' => DI\autowire(),
            // 'TorrentPier\Presentation\Http\Controllers\Admin\ForumController' => DI\autowire(),
            // 'TorrentPier\Presentation\Http\Controllers\Admin\TrackerController' => DI\autowire(),

            // Middleware
            // 'AuthenticationMiddleware' => DI\autowire('TorrentPier\Presentation\Http\Middleware\AuthenticationMiddleware'),
            // 'AuthorizationMiddleware' => DI\autowire('TorrentPier\Presentation\Http\Middleware\AuthorizationMiddleware'),
            // 'CorsMiddleware' => DI\autowire('TorrentPier\Presentation\Http\Middleware\CorsMiddleware'),
            // 'RateLimitMiddleware' => DI\autowire('TorrentPier\Presentation\Http\Middleware\RateLimitMiddleware'),

            // CLI Commands
            // 'TorrentPier\Presentation\Cli\Commands\CacheCommand' => DI\autowire(),
            // 'TorrentPier\Presentation\Cli\Commands\MigrateCommand' => DI\autowire(),
            // 'TorrentPier\Presentation\Cli\Commands\SeedCommand' => DI\autowire(),
            // 'TorrentPier\Presentation\Cli\Commands\TrackerCommand' => DI\autowire(),

            // View/Response Transformers
            // 'JsonResponseTransformer' => DI\autowire('TorrentPier\Presentation\Http\Responses\JsonResponseTransformer'),
            // 'HtmlResponseTransformer' => DI\autowire('TorrentPier\Presentation\Http\Responses\HtmlResponseTransformer'),
        ];
    }
}
