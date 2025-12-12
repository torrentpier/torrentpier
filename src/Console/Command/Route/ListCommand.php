<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Command\Route;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Console\Command\Command;
use TorrentPier\Router\Router;

/**
 * List all registered routes
 */
#[AsCommand(
    name: 'route:list',
    description: 'Display all registered routes'
)]
class ListCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption(
                'method',
                'm',
                InputOption::VALUE_REQUIRED,
                'Filter by HTTP method (GET, POST, etc.)'
            )
            ->addOption(
                'path',
                'p',
                InputOption::VALUE_REQUIRED,
                'Filter by path (partial match)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Registered Routes');

        // Load routes
        $router = Router::getInstance();
        $routesFile = BB_ROOT . 'library/routes.php';

        if (!is_file($routesFile)) {
            $this->error('Routes file not found: library/routes.php');
            return self::FAILURE;
        }

        // Load route definitions if not already loaded
        if (!$router->areRoutesLoaded()) {
            $routeDefinitions = require $routesFile;
            if (is_callable($routeDefinitions)) {
                $routeDefinitions($router);
            }
        }

        $routes = $router->getRoutes();

        if (empty($routes)) {
            $this->warning('No routes registered.');
            return self::SUCCESS;
        }

        // Apply filters
        $methodFilter = $input->getOption('method');
        $pathFilter = $input->getOption('path');

        if ($methodFilter !== null) {
            $methodFilter = strtoupper($methodFilter);
            $routes = array_filter($routes, fn($r) => str_contains($r['methods'], $methodFilter));
        }

        if ($pathFilter !== null) {
            $routes = array_filter($routes, fn($r) => str_contains($r['path'], $pathFilter));
        }

        if (empty($routes)) {
            $this->warning('No routes match the filters.');
            return self::SUCCESS;
        }

        // Build table
        $rows = [];
        foreach ($routes as $route) {
            $methods = $this->formatMethods($route['methods']);
            $handler = $this->formatHandler($route['handler']);

            $rows[] = [
                $methods,
                $route['path'],
                $handler,
            ];
        }

        // Sort by path
        usort($rows, fn($a, $b) => $a[1] <=> $b[1]);

        $this->table(
            ['Method', 'Path', 'Handler'],
            $rows
        );

        $this->line();
        $this->comment(sprintf('Total: %d route(s)', count($rows)));

        return self::SUCCESS;
    }

    /**
     * Format HTTP methods with colors
     */
    private function formatMethods(string $methods): string
    {
        $colors = [
            'GET' => 'green',
            'POST' => 'yellow',
            'PUT' => 'blue',
            'PATCH' => 'cyan',
            'DELETE' => 'red',
        ];

        $parts = explode('|', $methods);
        $formatted = [];

        foreach ($parts as $method) {
            $color = $colors[$method] ?? 'white';
            $formatted[] = "<fg=$color>$method</>";
        }

        return implode('<fg=gray>|</>', $formatted);
    }

    /**
     * Format handler for display (shorten class names)
     */
    private function formatHandler(string $handler): string
    {
        // Remove TorrentPier namespace prefix for brevity
        $handler = preg_replace('/^TorrentPier\\\\/', '', $handler);

        // Shorten common patterns
        $handler = str_replace('Controllers\\', '', $handler);
        return str_replace('Router\\', '', $handler);
    }
}
