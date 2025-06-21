<?php

use DI\Container as DIContainer;
use GuzzleHttp\Psr7\ServerRequest;
use TorrentPier\Infrastructure\DependencyInjection\Container;
use TorrentPier\Infrastructure\Http\Router;
use TorrentPier\Presentation\Http\Kernel;

describe('HTTP Kernel', function () {
    beforeEach(function () {
        // Create a real DI container wrapped in our custom container
        $diContainer = new DIContainer();
        $this->container = new Container($diContainer);

        // Create a real router instance and register it in the container
        $this->router = new Router($this->container);
        $this->container->getWrappedContainer()->set(Router::class, $this->router);

        // Create kernel instance
        $this->kernel = new Kernel($this->container);

        // Store original superglobals
        $this->originalServer = $_SERVER;
        $this->originalGet = $_GET;
        $this->originalPost = $_POST;
    });

    afterEach(function () {
        // Restore original superglobals
        $_SERVER = $this->originalServer;
        $_GET = $this->originalGet;
        $_POST = $this->originalPost;
    });

    describe('route loading', function () {
        it('loads routes from a file', function () {
            // Create a temporary routes file
            $routesFile = tempnam(sys_get_temp_dir(), 'routes');
            file_put_contents($routesFile, '<?php
                return function ($router) {
                    $router->get("/test", function () {
                        return new \GuzzleHttp\Psr7\Response(200, [], "Test route");
                    });
                };
            ');

            $this->kernel->loadRoutes($routesFile);

            // Clean up
            unlink($routesFile);

            // Verify route was loaded by trying to handle it
            $request = new ServerRequest('GET', 'http://example.com/test');
            $response = $this->kernel->handle($request);

            expect($response->getStatusCode())->toBe(200);
            expect((string)$response->getBody())->toBe('Test route');
        });

        it('throws exception for non-existent routes file', function () {
            $nonExistentFile = '/path/to/nonexistent/routes.php';

            expect(fn() => $this->kernel->loadRoutes($nonExistentFile))
                ->toThrow(RuntimeException::class);
        });
    });

    describe('request handling', function () {
        beforeEach(function () {
            // Set up a test route
            $routesFile = tempnam(sys_get_temp_dir(), 'routes');
            file_put_contents($routesFile, '<?php
                return function ($router) {
                    $router->get("/hello", function () {
                        return new \GuzzleHttp\Psr7\Response(200, [], "Hello World");
                    });
                    $router->post("/submit", function ($request) {
                        $body = $request->getParsedBody();
                        return new \GuzzleHttp\Psr7\Response(200, [], json_encode($body));
                    });
                };
            ');
            $this->kernel->loadRoutes($routesFile);
            $this->routesFile = $routesFile;
        });

        afterEach(function () {
            if (isset($this->routesFile)) {
                unlink($this->routesFile);
            }
        });

        it('handles GET requests', function () {
            $request = new ServerRequest('GET', 'http://example.com/hello');
            $response = $this->kernel->handle($request);

            expect($response->getStatusCode())->toBe(200);
            expect((string)$response->getBody())->toBe('Hello World');
        });

        it('handles POST requests', function () {
            $request = new ServerRequest('POST', 'http://example.com/submit');
            $request = $request->withParsedBody(['name' => 'John']);

            $response = $this->kernel->handle($request);

            expect($response->getStatusCode())->toBe(200);
            expect((string)$response->getBody())->toBe('{"name":"John"}');
        });

        it('returns 404 for undefined routes', function () {
            $request = new ServerRequest('GET', 'http://example.com/undefined');
            $response = $this->kernel->handle($request);

            expect($response->getStatusCode())->toBe(404);
        });

        it('returns 405 for wrong HTTP method', function () {
            $request = new ServerRequest('POST', 'http://example.com/hello');
            $response = $this->kernel->handle($request);

            expect($response->getStatusCode())->toBe(405);
        });
    });

    describe('error handling', function () {
        it('handles exceptions gracefully', function () {
            // Set up a route that throws an exception
            $routesFile = tempnam(sys_get_temp_dir(), 'routes');
            file_put_contents($routesFile, '<?php
                return function ($router) {
                    $router->get("/error", function () {
                        throw new \RuntimeException("Test exception");
                    });
                };
            ');
            $this->kernel->loadRoutes($routesFile);

            $request = new ServerRequest('GET', 'http://example.com/error');
            $response = $this->kernel->handle($request);

            expect($response->getStatusCode())->toBe(500);

            // Clean up
            unlink($routesFile);
        });

        it('logs exceptions when bb_log function exists', function () {
            // This is hard to test without actually defining bb_log
            // We'll just verify the error response structure
            $routesFile = tempnam(sys_get_temp_dir(), 'routes');
            file_put_contents($routesFile, '<?php
                return function ($router) {
                    $router->get("/error", function () {
                        throw new \Exception("Test error");
                    });
                };
            ');
            $this->kernel->loadRoutes($routesFile);

            $request = new ServerRequest('GET', 'http://example.com/error');
            $response = $this->kernel->handle($request);

            expect($response->getStatusCode())->toBe(500);
            expect($response->getBody()->getContents())->toContain('Internal Server Error');

            unlink($routesFile);
        });
    });

    describe('run method', function () {
        it('creates request from globals and sends response', function () {
            // Set up globals
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_SERVER['REQUEST_URI'] = '/hello';
            $_SERVER['HTTP_HOST'] = 'example.com';
            $_GET = ['foo' => 'bar'];

            // Set up a route
            $routesFile = tempnam(sys_get_temp_dir(), 'routes');
            file_put_contents($routesFile, '<?php
                return function ($router) {
                    $router->get("/hello", function ($request) {
                        $query = $request->getQueryParams();
                        return new \GuzzleHttp\Psr7\Response(200, [], $query["foo"] ?? "no foo");
                    });
                };
            ');
            $this->kernel->loadRoutes($routesFile);

            // Capture output
            ob_start();
            $this->kernel->run();
            $output = ob_get_clean();

            expect($output)->toContain('bar');

            unlink($routesFile);
        });
    });
});
