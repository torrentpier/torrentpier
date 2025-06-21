<?php

declare(strict_types=1);

namespace TorrentPier\Presentation\Http;

use League\Route\Http\Exception\MethodNotAllowedException;
use League\Route\Http\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Throwable;
use TorrentPier\Infrastructure\DependencyInjection\Container;
use TorrentPier\Infrastructure\Http\RequestFactory;
use TorrentPier\Infrastructure\Http\ResponseFactory;
use TorrentPier\Infrastructure\Http\Router;

class Kernel
{
    private Container $container;
    private Router $router;
    private array $middleware = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->router = $container->get(Router::class);
    }

    public function addMiddleware(MiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    public function loadRoutes(string $routesFile): self
    {
        if (!file_exists($routesFile)) {
            throw new \RuntimeException("Routes file not found: {$routesFile}");
        }

        $routeLoader = require $routesFile;
        if (is_callable($routeLoader)) {
            $routeLoader($this->router);
        }

        return $this;
    }

    public function handle(?ServerRequestInterface $request = null): ResponseInterface
    {
        try {
            $request = $request ?: RequestFactory::fromGlobals();

            // Apply middleware to router
            foreach ($this->middleware as $middleware) {
                $this->router->middleware($middleware);
            }

            return $this->router->dispatch($request);

        } catch (NotFoundException $e) {
            return ResponseFactory::html(
                '<h1>404 - Not Found</h1><p>The requested page could not be found.</p>',
                404
            );
        } catch (MethodNotAllowedException $e) {
            return ResponseFactory::html(
                '<h1>405 - Method Not Allowed</h1><p>The request method is not allowed for this endpoint.</p>',
                405
            );
        } catch (Throwable $e) {
            return $this->handleException($e, $request);
        }
    }

    public function run(?ServerRequestInterface $request = null): void
    {
        $response = $this->handle($request);
        $this->sendResponse($response);
    }

    private function handleException(Throwable $e, ?ServerRequestInterface $request = null): ResponseInterface
    {
        // TODO: Replace bb_log() with injected PSR-3 LoggerInterface
        // This is a temporary coupling to the legacy logging system
        if (function_exists('bb_log')) {
            bb_log($e->getMessage() . "\n" . $e->getTraceAsString(), 'kernel_errors');
        }

        // TODO: Replace legacy dev() singleton with injected EnvironmentInterface
        // This is a temporary coupling to the legacy system during the migration period
        // Once all legacy controllers are migrated, inject a proper debug/environment service
        if (function_exists('dev') && dev()->isDebugEnabled()) {
            $html = "
            <!DOCTYPE html>
            <html>
            <head>
                <title>Application Error</title>
                <style>
                    body { font-family: monospace; margin: 40px; background: #f5f5f5; }
                    .error { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                    .error h1 { color: #d32f2f; margin: 0 0 20px 0; }
                    .trace { background: #f8f8f8; padding: 15px; border-radius: 4px; overflow: auto; }
                    pre { margin: 0; white-space: pre-wrap; }
                </style>
            </head>
            <body>
                <div class=\"error\">
                    <h1>Application Error</h1>
                    <p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                    <p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>
                    <div class=\"trace\">
                        <strong>Stack Trace:</strong>
                        <pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>
                    </div>
                </div>
            </body>
            </html>";

            return ResponseFactory::html($html, 500);
        }

        // Production error response
        return ResponseFactory::html('
            <!DOCTYPE html>
            <html>
            <head><title>Server Error</title></head>
            <body>
                <h1>500 - Internal Server Error</h1>
                <p>Something went wrong. Please try again later.</p>
            </body>
            </html>
        ', 500);
    }

    private function sendResponse(ResponseInterface $response): void
    {
        // Send status line
        http_response_code($response->getStatusCode());

        // Send headers
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("{$name}: {$value}", false);
            }
        }

        // Send body
        echo $response->getBody()->getContents();
    }
}
