<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * User API Controller - Pure Laravel-style implementation
 */
class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    )
    {
    }

    /**
     * Register a new user
     */
    public function register(RegisterUserRequest $request): JsonResponse
    {
        try {
            // Laravel automatically validates the request via RegisterUserRequest
            $validated = $request->validated();

            // Create the user using the service
            $user = $this->userService->register($validated);

            return $this->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'id' => $user->getKey(),
                    'username' => $user->username,
                    'email' => $user->user_email,
                    'registered_at' => now()->toISOString()
                ]
            ], 201);

        } catch (ValidationException $e) {
            return $this->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user profile
     */
    public function show(Request $request, int $userId): JsonResponse
    {
        try {
            $user = User::find($userId);

            if (!$user) {
                return $this->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $stats = $user->getStats();

            return $this->json([
                'success' => true,
                'data' => [
                    'id' => $user->getKey(),
                    'username' => $user->username,
                    'level' => $user->user_level,
                    'active' => $user->isActive(),
                    'registered' => now()->createFromTimestamp($user->user_regdate)->toISOString(),
                    'last_visit' => now()->createFromTimestamp($user->user_lastvisit)->diffForHumans(),
                    'stats' => [
                        'uploaded' => $stats['u_up_total'] ?? 0,
                        'downloaded' => $stats['u_down_total'] ?? 0,
                        'ratio' => $user->getRatio(),
                    ],
                    'permissions' => [
                        'is_admin' => $user->isAdmin(),
                        'is_moderator' => $user->isModerator(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to retrieve user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List users with filtering and search
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Use Laravel-style parameter handling
            $page = (int)$request->get('page', 1);
            $perPage = min((int)$request->get('per_page', 20), 100);
            $search = Str::limit(trim($request->get('search', '')), 50);
            $level = $request->get('level');

            // Get users using collection helpers
            $users = collect(User::all())
                ->when(!empty($search), function ($collection) use ($search) {
                    return $collection->filter(function ($user) use ($search) {
                        return Str::contains(Str::lower($user->username), Str::lower($search)) ||
                            Str::contains(Str::lower($user->user_email), Str::lower($search));
                    });
                })
                ->when($level !== null, function ($collection) use ($level) {
                    return $collection->where('user_level', $level);
                })
                ->where('user_active', 1)
                ->sortBy('username')
                ->forPage($page, $perPage)
                ->map(function ($user) {
                    return [
                        'id' => $user->getKey(),
                        'username' => $user->username,
                        'level' => $user->user_level,
                        'registered' => now()->createFromTimestamp($user->user_regdate)->format('Y-m-d'),
                        'is_admin' => $user->isAdmin(),
                        'is_moderator' => $user->isModerator(),
                    ];
                })
                ->values();

            return $this->json([
                'success' => true,
                'data' => $users->toArray(),
                'meta' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'search' => (string)$search,
                    'level_filter' => $level
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to retrieve users',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
