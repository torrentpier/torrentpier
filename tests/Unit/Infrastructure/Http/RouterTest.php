<?php

use DI\Container as DIContainer;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use TorrentPier\Infrastructure\DependencyInjection\Container;
use TorrentPier\Infrastructure\Http\Router;

describe('Router', function () {
    beforeEach(function () {
        // Create a real DI container and wrap it in our custom container
        $diContainer = new DIContainer();
        $this->container = new Container($diContainer);

        // Create router instance
        $this->router = new Router($this->container);
    });

    describe('route registration', function () {
        it('registers GET routes', function () {
            $this->router->get('/test', function () {
                return new Response(200, [], 'GET test');
            });

            $request = new ServerRequest('GET', '/test');
            $response = $this->router->dispatch($request);

            expect($response->getStatusCode())->toBe(200);
            expect((string)$response->getBody())->toBe('GET test');
        });

        it('registers POST routes', function () {
            $this->router->post('/test', function () {
                return new Response(200, [], 'POST test');
            });

            $request = new ServerRequest('POST', '/test');
            $response = $this->router->dispatch($request);

            expect($response->getStatusCode())->toBe(200);
            expect((string)$response->getBody())->toBe('POST test');
        });

        it('registers PUT routes', function () {
            $this->router->put('/test', function () {
                return new Response(200, [], 'PUT test');
            });

            $request = new ServerRequest('PUT', '/test');
            $response = $this->router->dispatch($request);

            expect($response->getStatusCode())->toBe(200);
            expect((string)$response->getBody())->toBe('PUT test');
        });

        it('registers PATCH routes', function () {
            $this->router->patch('/test', function () {
                return new Response(200, [], 'PATCH test');
            });

            $request = new ServerRequest('PATCH', '/test');
            $response = $this->router->dispatch($request);

            expect($response->getStatusCode())->toBe(200);
            expect((string)$response->getBody())->toBe('PATCH test');
        });

        it('registers DELETE routes', function () {
            $this->router->delete('/test', function () {
                return new Response(200, [], 'DELETE test');
            });

            $request = new ServerRequest('DELETE', '/test');
            $response = $this->router->dispatch($request);

            expect($response->getStatusCode())->toBe(200);
            expect((string)$response->getBody())->toBe('DELETE test');
        });

        it('registers HEAD routes', function () {
            $this->router->head('/test', function () {
                return new Response(200);
            });

            $request = new ServerRequest('HEAD', '/test');
            $response = $this->router->dispatch($request);

            expect($response->getStatusCode())->toBe(200);
        });

        it('registers OPTIONS routes', function () {
            $this->router->options('/test', function () {
                return new Response(200, ['Allow' => 'GET, POST, OPTIONS']);
            });

            $request = new ServerRequest('OPTIONS', '/test');
            $response = $this->router->dispatch($request);

            expect($response->getStatusCode())->toBe(200);
            expect($response->getHeader('Allow'))->toBe(['GET, POST, OPTIONS']);
        });
    });

    describe('route parameters', function () {
        it('captures route parameters', function () {
            $this->router->get('/user/{id}', function ($request, $args) {
                return new Response(200, [], 'User ID: ' . $args['id']);
            });

            $request = new ServerRequest('GET', '/user/123');
            $response = $this->router->dispatch($request);

            expect($response->getStatusCode())->toBe(200);
            expect((string)$response->getBody())->toBe('User ID: 123');
        });

        it('captures multiple route parameters', function () {
            $this->router->get('/post/{year}/{month}/{slug}', function ($request, $args) {
                return new Response(200, [], sprintf(
                    'Post: %s/%s/%s',
                    $args['year'],
                    $args['month'],
                    $args['slug']
                ));
            });

            $request = new ServerRequest('GET', '/post/2024/06/hello-world');
            $response = $this->router->dispatch($request);

            expect($response->getStatusCode())->toBe(200);
            expect((string)$response->getBody())->toBe('Post: 2024/06/hello-world');
        });
    });

    describe('controller resolution', function () {
        it('resolves controller from DI container', function () {
            // Create a test controller
            $testController = new class {
                public function index()
                {
                    return new Response(200, [], 'Controller response');
                }
            };

            // Register controller in container using the wrapped DI container
            $this->container->getWrappedContainer()->set('TestController', $testController);

            // Register route with controller
            $this->router->get('/test', ['TestController', 'index']);

            $request = new ServerRequest('GET', '/test');
            $response = $this->router->dispatch($request);

            expect($response->getStatusCode())->toBe(200);
            expect((string)$response->getBody())->toBe('Controller response');
        });

        it('resolves controller by class name', function () {
            // Define a test controller class
            $controllerClass = 'TestControllerClass' . uniqid();
            eval("
                class $controllerClass {
                    public function handle() {
                        return new \GuzzleHttp\Psr7\Response(200, [], 'Class-based controller');
                    }
                }
            ");

            $this->router->get('/test', [$controllerClass, 'handle']);

            $request = new ServerRequest('GET', '/test');
            $response = $this->router->dispatch($request);

            expect($response->getStatusCode())->toBe(200);
            expect((string)$response->getBody())->toBe('Class-based controller');
        });
    });

    describe('middleware support', function () {
        it('applies middleware to routes', function () {
            // Create a simple middleware that implements PSR-15
            $middleware = new class implements \Psr\Http\Server\MiddlewareInterface {
                public function process(
                    \Psr\Http\Message\ServerRequestInterface $request,
                    \Psr\Http\Server\RequestHandlerInterface $handler
                ): \Psr\Http\Message\ResponseInterface
                {
                    $response = $handler->handle($request);
                    return $response->withHeader('X-Middleware', 'Applied');
                }
            };

            $this->router->middleware($middleware)
                ->get('/test', function () {
                    return new Response(200, [], 'With middleware');
                });

            $request = new ServerRequest('GET', '/test');
            $response = $this->router->dispatch($request);

            expect($response->getStatusCode())->toBe(200);
            expect($response->getHeader('X-Middleware'))->toBe(['Applied']);
            expect((string)$response->getBody())->toBe('With middleware');
        });
    });

    describe('error handling', function () {
        it('throws NotFoundException for undefined routes', function () {
            $request = new ServerRequest('GET', '/undefined');

            expect(fn() => $this->router->dispatch($request))
                ->toThrow(League\Route\Http\Exception\NotFoundException::class);
        });

        it('throws MethodNotAllowedException for wrong HTTP method', function () {
            $this->router->post('/test', function () {
                return new Response(200);
            });

            $request = new ServerRequest('GET', '/test');

            expect(fn() => $this->router->dispatch($request))
                ->toThrow(League\Route\Http\Exception\MethodNotAllowedException::class);
        });
    });

    describe('route groups', function () {
        it('supports route groups with prefix', function () {
            $this->router->group('/api', function ($router) {
                $router->get('/users', function () {
                    return new Response(200, [], 'Users list');
                });
                $router->get('/posts', function () {
                    return new Response(200, [], 'Posts list');
                });
            });

            // Test /api/users
            $request = new ServerRequest('GET', '/api/users');
            $response = $this->router->dispatch($request);
            expect($response->getStatusCode())->toBe(200);
            expect((string)$response->getBody())->toBe('Users list');

            // Test /api/posts
            $request = new ServerRequest('GET', '/api/posts');
            $response = $this->router->dispatch($request);
            expect($response->getStatusCode())->toBe(200);
            expect((string)$response->getBody())->toBe('Posts list');
        });
    });
});
