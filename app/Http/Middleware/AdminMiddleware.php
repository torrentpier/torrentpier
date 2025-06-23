<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

/**
 * Admin Authorization Middleware
 *
 * Example middleware showing how to implement role-based authorization
 * using Illuminate HTTP components
 */
class AdminMiddleware
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
        // Check if user is authenticated first
        $userId = $request->attributes->get('authenticated_user_id');

        if (!$userId) {
            if ($request->expectsJson()) {
                return new JsonResponse(['error' => 'Authentication required'], 401);
            }

            return new Response('Authentication required', 401);
        }

        // TODO: Implement actual admin role check
        // For now, accept user ID 1 as admin
        if ($userId !== 1) {
            if ($request->expectsJson()) {
                return new JsonResponse(['error' => 'Admin access required'], 403);
            }

            return new Response('Admin access required', 403);
        }

        return $next($request);
    }
}
