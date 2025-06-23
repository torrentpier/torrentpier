<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request = $this->before($request);
        $response = $next($request);
        return $this->after($request, $response);
    }

    /**
     * Process request before passing to next middleware
     */
    protected function before(Request $request): Request
    {
        return $request;
    }

    /**
     * Process response after middleware chain
     */
    protected function after(Request $request, Response $response): Response
    {
        return $response;
    }
}
