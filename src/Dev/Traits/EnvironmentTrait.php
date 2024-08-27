<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Dev\Traits;

use Exception;

/**
 * Trait EnvironmentTrait
 * @package TorrentPier\Dev\Traits
 */
trait EnvironmentTrait
{
    /**
     * Allowed environment types
     *
     * @var array|string[]
     */
    private static array $allowedTypes = [
        'prodSection' => [
            'prod',
            'production'
        ],
        'devSection' => [
            'dev',
            'development',
            'local'
        ]
    ];

    /**
     * Determine production mode
     *
     * @var bool
     */
    public static bool $isProduction = false;

    /**
     * @throws Exception
     */
    public static function getEnvironment(): string
    {
        $envType = strtolower(env('APP_ENV', self::$allowedTypes['prodSection'][0]));

        // Check environment value
        if (!in_array($envType, self::$allowedTypes['devSection'] + self::$allowedTypes['prodSection'])) {
            throw new Exception(sprintf('Invalid APP_ENV value passed: %s', $envType));
        }

        // Determine production mode
        self::$isProduction = in_array($envType, self::$allowedTypes['prodSection']);

        return $envType;
    }
}
