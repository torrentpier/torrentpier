<?php

declare(strict_types=1);

namespace TorrentPier\Infrastructure\DependencyInjection\Definitions;

use DI;
use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;
use Nette\Caching\Storages\MemcachedStorage;
use Nette\Caching\Storages\SQLiteStorage;
use Nette\Database\Connection;
use Psr\Container\ContainerInterface;

class InfrastructureDefinitions
{
    public static function getDefinitions(array $config = []): array
    {
        return [
            // TODO: Add infrastructure service definitions as they are implemented

            // Example: Database Connection (implement when Nette Database integration is ready)
            // 'database.connection.default' => DI\factory(function () use ($config) {
            //     $dbConfig = $config['database'] ?? [];
            //     $dsn = sprintf(
            //         'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            //         $dbConfig['host'] ?? '127.0.0.1',
            //         $dbConfig['port'] ?? 3306,
            //         $dbConfig['database'] ?? 'tp',
            //         $dbConfig['charset'] ?? 'utf8mb4'
            //     );
            //
            //     return new Connection(
            //         $dsn,
            //         $dbConfig['username'] ?? 'root',
            //         $dbConfig['password'] ?? ''
            //     );
            // }),

            // Example: Cache Storage (implement when cache infrastructure is ready)
            // 'cache.storage' => DI\factory(function () use ($config) {
            //     $cacheConfig = $config['cache'] ?? [];
            //     $driver = $cacheConfig['driver'] ?? 'file';
            //
            //     switch ($driver) {
            //         case 'memcached':
            //             $memcached = new \Memcached();
            //             $memcached->addServer(
            //                 $cacheConfig['memcached']['host'] ?? '127.0.0.1',
            //                 $cacheConfig['memcached']['port'] ?? 11211
            //             );
            //             return new MemcachedStorage($memcached);
            //
            //         case 'sqlite':
            //             return new SQLiteStorage($cacheConfig['sqlite']['path'] ?? __DIR__ . '/../../../../internal_data/cache/cache.db');
            //
            //         case 'file':
            //         default:
            //             return new FileStorage($cacheConfig['file']['path'] ?? __DIR__ . '/../../../../internal_data/cache');
            //     }
            // }),

            // Example: Repository Implementations (implement when repositories are created)
            // 'TorrentPier\Infrastructure\Persistence\Repository\ForumRepository' => DI\autowire()
            //     ->constructorParameter('connection', DI\get('database.connection.default'))
            //     ->constructorParameter('cache', DI\get('cache.factory')),

            // Example: Email Service (implement when email infrastructure is ready)
            // 'EmailServiceInterface' => DI\factory(function (ContainerInterface $c) use ($config) {
            //     $emailConfig = $config['email'] ?? [];
            //     return new SmtpEmailService($emailConfig);
            // }),

            // Example: File Storage (implement when file storage abstraction is ready)
            // 'FileStorageInterface' => DI\factory(function (ContainerInterface $c) use ($config) {
            //     $storageConfig = $config['storage'] ?? [];
            //     return match ($storageConfig['driver'] ?? 'local') {
            //         's3' => new S3FileStorage($storageConfig['s3']),
            //         default => new LocalFileStorage($storageConfig['local']['path'] ?? __DIR__ . '/../../../../internal_data/uploads'),
            //     };
            // }),
        ];
    }
}
