<?php

declare(strict_types=1);

/**
 * Console Bootstrap
 *
 * Bootstrap the console application
 */

use Illuminate\Contracts\Container\BindingResolutionException;
use Symfony\Component\Console\Application;

// Only define DEXTER_BINARY if not already defined
if (!defined('DEXTER_BINARY')) {
    define('DEXTER_BINARY', true);
}
require_once __DIR__ . '/../vendor/autoload.php';

// Load container bootstrap
require_once __DIR__ . '/container.php';

// Create the application container
$container = createContainer(dirname(__DIR__));

// Create Symfony Console Application
$app = new Application('TorrentPier Console', '3.0-dev');

// Get registered commands from the container
try {
    if ($container->bound('console.commands')) {
        $commands = $container->make('console.commands');
        foreach ($commands as $command) {
            try {
                $app->add($container->make($command));
            } catch (BindingResolutionException $e) {
                // Skip commands that can't be resolved - console still works with built-in commands
                continue;
            }
        }
    }
} catch (BindingResolutionException $e) {
    // No commands registered or service binding failed - console still works with built-in commands
}

// Return the console application
return $app;
