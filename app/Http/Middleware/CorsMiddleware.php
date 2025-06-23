<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    private array $allowedOrigins;
    private array $allowedHeaders;
    private array $allowedMethods;

    public function __construct(
        array $allowedOrigins = ['*'],
        array $allowedHeaders = ['Content-Type', 'Authorization', 'X-Requested-With'],
        array $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']
    )
    {
        $this->allowedOrigins = $allowedOrigins;
        $this->allowedHeaders = $allowedHeaders;
        $this->allowedMethods = $allowedMethods;
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->getMethod() === 'OPTIONS') {
            return $this->createPreflightResponse($request);
        }

        $response = $next($request);
        return $this->addCorsHeaders($response, $request);
    }

    private function createPreflightResponse(Request $request): Response
    {
        $response = new Response('', 200);
        return $this->addCorsHeaders($response, $request);
    }

    private function addCorsHeaders(Response $response, Request $request): Response
    {
        $origin = $request->headers->get('Origin', '');

        if (in_array('*', $this->allowedOrigins) || in_array($origin, $this->allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin ?: '*');
        }

        $response->headers->set('Access-Control-Allow-Methods', implode(', ', $this->allowedMethods));
        $response->headers->set('Access-Control-Allow-Headers', implode(', ', $this->allowedHeaders));
        $response->headers->set('Access-Control-Max-Age', '86400');

        return $response;
    }
}
