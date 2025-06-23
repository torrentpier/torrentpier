<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Application Service Provider
 * 
 * Bootstrap any application services here
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services
     */
    public function register(): void
    {
        // Register application services
        $this->registerEvents();
        $this->registerValidation();
    }

    /**
     * Bootstrap any application services
     */
    public function boot(): void
    {
        // Bootstrap services that need the application to be fully loaded
    }


    /**
     * Register the event dispatcher
     */
    protected function registerEvents(): void
    {
        $this->app->singleton('events', function ($app) {
            return new \Illuminate\Events\Dispatcher($app);
        });
        
        $this->app->alias('events', \Illuminate\Events\Dispatcher::class);
        $this->app->alias('events', \Illuminate\Contracts\Events\Dispatcher::class);
    }

    /**
     * Register the validation factory
     */
    protected function registerValidation(): void
    {
        $this->app->singleton('validator', function ($app) {
            $loader = new \Illuminate\Translation\ArrayLoader();
            $translator = new \Illuminate\Translation\Translator($loader, 'en');
            return new \Illuminate\Validation\Factory($translator, $app);
        });

        $this->app->bind(\Illuminate\Validation\Factory::class, function ($app) {
            return $app['validator'];
        });
        
        $this->app->alias('validator', \Illuminate\Validation\Factory::class);
        $this->app->alias('validator', \Illuminate\Contracts\Validation\Factory::class);
    }
}