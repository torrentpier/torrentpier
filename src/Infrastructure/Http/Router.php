<?php

declare(strict_types=1);

namespace TorrentPier\Infrastructure\Http;

use League\Route\RouteGroup;
use League\Route\Router as LeagueRouter;
use League\Route\Strategy\ApplicationStrategy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use TorrentPier\Infrastructure\DependencyInjection\Container;

class Router
{
    private LeagueRouter $router;
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->router = new LeagueRouter();

        // Set up the application strategy with DI container
        $strategy = new ApplicationStrategy();
        $strategy->setContainer($this->container->getWrappedContainer());
        $this->router->setStrategy($strategy);
    }

    public function get(string $path, $handler): \League\Route\Route
    {
        return $this->router->map('GET', $path, $handler);
    }

    public function post(string $path, $handler): \League\Route\Route
    {
        return $this->router->map('POST', $path, $handler);
    }

    public function put(string $path, $handler): \League\Route\Route
    {
        return $this->router->map('PUT', $path, $handler);
    }

    public function patch(string $path, $handler): \League\Route\Route
    {
        return $this->router->map('PATCH', $path, $handler);
    }

    public function delete(string $path, $handler): \League\Route\Route
    {
        return $this->router->map('DELETE', $path, $handler);
    }

    public function options(string $path, $handler): \League\Route\Route
    {
        return $this->router->map('OPTIONS', $path, $handler);
    }

    public function head(string $path, $handler): \League\Route\Route
    {
        return $this->router->map('HEAD', $path, $handler);
    }

    public function any(string $path, $handler): \League\Route\Route
    {
        return $this->router->map(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $path, $handler);
    }

    public function group(string $prefix, callable $callback): RouteGroup
    {
        return $this->router->group($prefix, $callback);
    }

    public function middleware(MiddlewareInterface $middleware): self
    {
        $this->router->middleware($middleware);
        return $this;
    }

    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        return $this->router->dispatch($request);
    }

    public function getLeagueRouter(): LeagueRouter
    {
        return $this->router;
    }
}
