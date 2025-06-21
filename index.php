<?php /** @noinspection PhpDefineCanBeReplacedWithConstInspection */

declare(strict_types=1);

/**
 * Modern routing entry point for TorrentPier 3.0
 *
 * This file bootstraps the new hexagonal architecture routing system
 * using league/route and the dependency injection container.
 */

// Bootstrap autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load legacy common.php to initialize global functions and legacy components
require_once __DIR__ . '/common.php';

// Using define() instead of const to ensure global accessibility for legacy controllers
define('IN_TORRENTPIER', true);

use TorrentPier\Infrastructure\DependencyInjection\Bootstrap;
use TorrentPier\Presentation\Http\Kernel;

// Initialize the dependency injection container
$container = Bootstrap::init(__DIR__);

// Get the HTTP kernel from the container
$kernel = $container->get(Kernel::class);

// Load web routes
$routesFile = __DIR__ . '/src/Presentation/Http/Routes/web.php';
$kernel->loadRoutes($routesFile);

// Handle the request and send response
$kernel->run();
