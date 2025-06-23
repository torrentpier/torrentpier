<?php

declare(strict_types=1);

namespace App\Http\Routing;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Router as LaravelRouter;
use Illuminate\Routing\UrlGenerator;

/**
 * Router
 * 
 * Illuminate Routing wrapper for TorrentPier
 */
class Router
{
    private LaravelRouter $router;
    private Container $container;
    
    public function __construct(Container $container)
    {
        $this->container = $container;
        
        // Create event dispatcher if not already bound
        if (!$container->bound('events')) {
            $container->singleton('events', function () {
                return new Dispatcher($this->container);
            });
        }
        
        // Create the Illuminate Router
        $this->router = new LaravelRouter($container->make('events'), $container);
        
        // Register middleware aliases
        $this->registerMiddleware();
        
        // Bind router instance
        $container->instance('router', $this->router);
        $container->instance(LaravelRouter::class, $this->router);
        
        // Create and bind URL generator
        $request = $container->bound('request') ? $container->make('request') : Request::capture();
        $container->instance('request', $request);
        
        $url = new UrlGenerator($this->router->getRoutes(), $request);
        $container->instance('url', $url);
        $container->instance(UrlGenerator::class, $url);
    }
    
    /**
     * Get the underlying Laravel Router instance
     */
    public function getRouter(): LaravelRouter
    {
        return $this->router;
    }
    
    /**
     * Register a GET route
     */
    public function get(string $uri, $action): \Illuminate\Routing\Route
    {
        return $this->router->get($uri, $this->parseAction($action));
    }
    
    /**
     * Register a POST route
     */
    public function post(string $uri, $action): \Illuminate\Routing\Route
    {
        return $this->router->post($uri, $this->parseAction($action));
    }
    
    /**
     * Register a PUT route
     */
    public function put(string $uri, $action): \Illuminate\Routing\Route
    {
        return $this->router->put($uri, $this->parseAction($action));
    }
    
    /**
     * Register a PATCH route
     */
    public function patch(string $uri, $action): \Illuminate\Routing\Route
    {
        return $this->router->patch($uri, $this->parseAction($action));
    }
    
    /**
     * Register a DELETE route
     */
    public function delete(string $uri, $action): \Illuminate\Routing\Route
    {
        return $this->router->delete($uri, $this->parseAction($action));
    }
    
    /**
     * Register an OPTIONS route
     */
    public function options(string $uri, $action): \Illuminate\Routing\Route
    {
        return $this->router->options($uri, $this->parseAction($action));
    }
    
    /**
     * Register a route for any HTTP verb
     */
    public function any(string $uri, $action): \Illuminate\Routing\Route
    {
        return $this->router->any($uri, $this->parseAction($action));
    }
    
    /**
     * Register a route group
     */
    public function group(array $attributes, \Closure $callback): void
    {
        $this->router->group($attributes, $callback);
    }
    
    /**
     * Register a route prefix
     */
    public function prefix(string $prefix): \Illuminate\Routing\RouteRegistrar
    {
        return $this->router->prefix($prefix);
    }
    
    /**
     * Register middleware
     */
    public function middleware($middleware): \Illuminate\Routing\RouteRegistrar
    {
        return $this->router->middleware($middleware);
    }
    
    /**
     * Register a resource controller
     */
    public function resource(string $name, string $controller, array $options = []): \Illuminate\Routing\PendingResourceRegistration
    {
        return $this->router->resource($name, $controller, $options);
    }
    
    /**
     * Dispatch the request to the application
     */
    public function dispatch(Request $request): Response|JsonResponse
    {
        try {
            $response = $this->router->dispatch($request);
            
            // Ensure we always return a Response object
            if (!$response instanceof Response && !$response instanceof JsonResponse) {
                if (is_array($response) || is_object($response)) {
                    return new JsonResponse($response);
                }
                return new Response($response);
            }
            
            return $response;
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return new Response('Not Found', 404);
        } catch (\Exception $e) {
            // Log the error if logger is available
            if ($this->container->bound('log')) {
                $this->container->make('log')->error($e->getMessage(), ['exception' => $e]);
            }
            
            return new Response('Internal Server Error', 500);
        }
    }
    
    /**
     * Parse the action to convert Class::method to array format
     */
    private function parseAction($action)
    {
        if (is_string($action) && str_contains($action, '::')) {
            return str_replace('::', '@', $action);
        }
        
        return $action;
    }
    
    /**
     * Get all registered routes
     */
    public function getRoutes(): \Illuminate\Routing\RouteCollection
    {
        return $this->router->getRoutes();
    }
    
    /**
     * Set the fallback route
     */
    public function fallback($action): \Illuminate\Routing\Route
    {
        return $this->router->fallback($this->parseAction($action));
    }
    
    /**
     * Register middleware aliases
     */
    private function registerMiddleware(): void
    {
        $middlewareAliases = [
            'auth' => \App\Http\Middleware\AuthMiddleware::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'cors' => \App\Http\Middleware\CorsMiddleware::class,
        ];
        
        foreach ($middlewareAliases as $alias => $middleware) {
            $this->router->aliasMiddleware($alias, $middleware);
        }
    }
}