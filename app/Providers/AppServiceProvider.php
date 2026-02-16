<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Providers;

use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\NativeClock;
use TorrentPier\Auth\TwoFactor\Encryption;
use TorrentPier\Auth\TwoFactor\RecoveryCodes;
use TorrentPier\Auth\TwoFactor\TotpService;
use TorrentPier\Auth\TwoFactor\TwoFactorAuth;
use TorrentPier\Censor;
use TorrentPier\Feed\FeedGenerator;
use TorrentPier\Forum\ForumTree;
use TorrentPier\Language;
use TorrentPier\ServiceProvider;

/**
 * Application Service Provider
 *
 * This provider is for application-specific bindings and bootstrapping.
 * It is registered last, after all core providers, so it has access
 * to all other services.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services
     */
    public function register(): void
    {
        // Censor service for word filtering (auto-wired with DatastoreManager)
        $this->app->singleton(Censor::class);

        // Forum tree - cached forum hierarchy (auto-wired with DatastoreManager)
        $this->app->singleton(ForumTree::class);

        // Language system (auto-wired with Config)
        $this->app->singleton(Language::class);

        // PSR-20 Clock (used by TOTP service)
        $this->app->singleton(ClockInterface::class, NativeClock::class);

        // Two-Factor authentication services
        $this->app->singleton(Encryption::class);
        $this->app->singleton(TotpService::class);
        $this->app->singleton(RecoveryCodes::class);
        $this->app->singleton(TwoFactorAuth::class);

        // Feed generator
        $this->app->singleton(FeedGenerator::class, function () {
            return new FeedGenerator;
        });

        // Register aliases
        $this->app->alias(Censor::class, 'censor');
        $this->app->alias(ForumTree::class, 'forum_tree');
        $this->app->alias(Language::class, 'lang');
        $this->app->alias(FeedGenerator::class, 'feed');
        $this->app->alias(TwoFactorAuth::class, 'two_factor');
    }

    /**
     * Bootstrap any application services
     */
    public function boot(): void
    {
        // Perform any application bootstrapping here
    }

    /**
     * Get the services provided by this provider
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [
            Censor::class,
            ForumTree::class,
            Language::class,
            FeedGenerator::class,
            Encryption::class,
            TotpService::class,
            RecoveryCodes::class,
            TwoFactorAuth::class,
            'censor',
            'forum_tree',
            'lang',
            'feed',
            'two_factor',
        ];
    }
}
