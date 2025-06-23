<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Factory as ValidationFactory;
use TorrentPier\Config;

class HelloWorldController extends Controller
{
    public function __construct(
        ValidationFactory $validator,
        private Config $config
    ) {
        parent::__construct($validator);
    }

    /**
     * Show the hello world page
     */
    public function index(Request $request): Response
    {
        $siteName = $this->config->get('sitename', 'TorrentPier');
        $currentTime = now()->format('Y-m-d H:i:s');

        $data = [
            'siteName' => $siteName,
            'currentTime' => $currentTime,
            'request' => (object) [
                'uri' => $request->fullUrl(),
                'method' => $request->method()
            ],
            'architecture' => 'MVC with Illuminate HTTP'
        ];

        return $this->view('hello', $data);
    }

    /**
     * Return JSON response for hello world
     */
    public function jsonResponse(Request $request): JsonResponse
    {
        $siteName = $this->config->get('sitename', 'TorrentPier');

        return $this->json([
            'message' => 'Hello World from TorrentPier!',
            'site' => $siteName,
            'timestamp' => now()->timestamp,
            'datetime' => now()->toISOString(),
            'route' => [
                'uri' => $request->fullUrl(),
                'method' => $request->method(),
                'controller' => self::class,
            ],
            'architecture' => [
                'pattern' => 'Laravel-style MVC',
                'router' => 'Custom Laravel-style Router',
                'http' => 'Illuminate HTTP',
                'support' => 'Illuminate Support',
                'di' => 'Illuminate Container'
            ],
            'features' => [
                'illuminate_http' => 'Response and Request handling',
                'illuminate_support' => 'Collections, Str, Arr helpers',
                'carbon' => 'Date manipulation with now() and today()',
                'validation' => 'Laravel-style request validation',
                'collections' => 'collect() helper for data manipulation'
            ]
        ]);
    }

    /**
     * Demonstrate modern Laravel-style features
     */
    public function features(Request $request): JsonResponse
    {
        // Demonstrate collections
        $users = collect([
            ['name' => 'Alice', 'age' => 25],
            ['name' => 'Bob', 'age' => 30],
            ['name' => 'Charlie', 'age' => 35]
        ]);

        $adults = $users->where('age', '>=', 18)->pluck('name');

        // Demonstrate string helpers
        $title = str('hello world')->title()->append('!');

        // Demonstrate array helpers
        $config = [
            'app' => [
                'name' => 'TorrentPier',
                'version' => '3.0'
            ]
        ];

        $appName = data_get($config, 'app.name', 'Unknown');

        return $this->json([
            'collections_demo' => [
                'original_users' => $users->toArray(),
                'adult_names' => $adults->toArray()
            ],
            'string_demo' => [
                'original' => 'hello world',
                'transformed' => (string) $title
            ],
            'array_helpers_demo' => [
                'config' => $config,
                'app_name' => $appName
            ],
            'date_helpers' => [
                'now' => now()->toISOString(),
                'today' => today()->toDateString(),
                'timestamp' => now()->timestamp
            ]
        ]);
    }

    /**
     * Extended features demonstration
     */
    public function extended(Request $request): JsonResponse
    {
        $siteName = $this->config->get('sitename', 'TorrentPier');

        return $this->json([
            'message' => 'Extended Laravel-style features!',
            'site' => $siteName,
            'timestamp' => now()->timestamp,
            'datetime' => now()->toISOString(),
            'request_info' => [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
            'laravel_features' => [
                'collections' => 'Native Laravel collections',
                'request' => 'Pure Illuminate Request',
                'response' => 'Pure Illuminate JsonResponse',
                'validation' => 'Built-in validation',
                'helpers' => 'Laravel helper functions'
            ]
        ]);
    }
}