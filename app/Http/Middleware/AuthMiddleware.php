<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

/**
 * Authentication Middleware
 *
 * Example middleware showing how to implement authentication
 * using Illuminate HTTP components
 */
class AuthMiddleware
{
    /**
     * Handle an incoming request
     *
     * @param Request $request
     * @param Closure $next
     * @return Response|JsonResponse
     */
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        // Example authentication check
        $token = $request->bearerToken() ?? $request->input('api_token');

        if (!$token) {
            if ($request->expectsJson()) {
                return new JsonResponse(['error' => 'Authentication required'], 401);
            }

            return new Response('Authentication required', 401);
        }

        // TODO: Implement actual token validation
        // For now, accept any token that starts with 'valid_'
        if (!str_starts_with($token, 'valid_')) {
            if ($request->expectsJson()) {
                return new JsonResponse(['error' => 'Invalid token'], 401);
            }

            return new Response('Invalid token', 401);
        }

        // Add user info to request for use in controllers
        $request->attributes->set('authenticated_user_id', 1);

        return $next($request);
    }
}
