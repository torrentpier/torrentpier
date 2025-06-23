<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\Finder\Finder;

/**
 * Console Service Provider
 * 
 * Automatically discovers and registers console commands
 */
class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Register console commands
     */
    public function register(): void
    {
        $this->registerCommands();
    }

    /**
     * Register console commands
     */
    protected function registerCommands(): void
    {
        $commands = $this->discoverCommands();
        
        $this->app->bind('console.commands', function () use ($commands) {
            return $commands;
        });

        // Register each command in the container
        foreach ($commands as $command) {
            $this->app->bind($command, function ($app) use ($command) {
                return new $command();
            });
        }
    }

    /**
     * Discover commands in the Commands directory
     */
    protected function discoverCommands(): array
    {
        $commands = [];
        $commandsPath = $this->app->make('path.app') . '/Console/Commands';

        if (!is_dir($commandsPath)) {
            return $commands;
        }

        $finder = new Finder();
        $finder->files()->name('*Command.php')->in($commandsPath);

        foreach ($finder as $file) {
            $relativePath = $file->getRelativePathname();
            $className = 'App\\Console\\Commands\\' . str_replace(['/', '.php'], ['\\', ''], $relativePath);

            if (class_exists($className)) {
                $reflection = new \ReflectionClass($className);
                
                // Only include concrete command classes that extend our base Command
                if (!$reflection->isAbstract() && 
                    $reflection->isSubclassOf(\App\Console\Commands\Command::class)) {
                    $commands[] = $className;
                }
            }
        }

        return $commands;
    }
}