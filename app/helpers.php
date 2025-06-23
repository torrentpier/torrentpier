<?php

declare(strict_types=1);

/**
 * Laravel-style helper functions for TorrentPier
 */

if (!function_exists('app_path')) {
    /**
     * Get the path to the app directory
     */
    function app_path(string $path = ''): string
    {
        return rtrim(__DIR__, '/') . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('base_path')) {
    /**
     * Get the path to the base directory
     */
    function base_path(string $path = ''): string
    {
        return rtrim(dirname(__DIR__), '/') . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('config_path')) {
    /**
     * Get the path to the config directory
     */
    function config_path(string $path = ''): string
    {
        return base_path('config') . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('database_path')) {
    /**
     * Get the path to the database directory
     */
    function database_path(string $path = ''): string
    {
        return base_path('database') . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('public_path')) {
    /**
     * Get the path to the public directory
     */
    function public_path(string $path = ''): string
    {
        return base_path('public') . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('resource_path')) {
    /**
     * Get the path to the resources directory
     */
    function resource_path(string $path = ''): string
    {
        return base_path('resources') . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get the path to the storage directory
     */
    function storage_path(string $path = ''): string
    {
        return base_path('storage') . ($path ? '/' . ltrim($path, '/') : '');
    }
}


if (!function_exists('view')) {
    /**
     * Create a view response (simple implementation)
     */
    function view(string $view, array $data = []): string
    {
        $viewPath = resource_path('views/' . str_replace('.', '/', $view) . '.php');

        if (!file_exists($viewPath)) {
            throw new \InvalidArgumentException("View [{$view}] not found.");
        }

        extract($data);
        ob_start();
        require $viewPath;
        return ob_get_clean();
    }
}

if (!function_exists('fake')) {
    /**
     * Create fake data using FakerPHP (Laravel-style implementation)
     */
    function fake(?string $locale = null): \Faker\Generator
    {
        return \Faker\Factory::create($locale ?? 'en_US');
    }
}

if (!function_exists('collect')) {
    /**
     * Create a collection from the given value (using Illuminate Support)
     */
    function collect(mixed $value = []): \Illuminate\Support\Collection
    {
        return new \Illuminate\Support\Collection($value);
    }
}

if (!function_exists('data_get')) {
    /**
     * Get an item from an array or object using "dot" notation
     */
    function data_get(mixed $target, string|array|int|null $key, mixed $default = null): mixed
    {
        return \Illuminate\Support\Arr::get($target, $key, $default);
    }
}

if (!function_exists('data_set')) {
    /**
     * Set an item on an array or object using "dot" notation
     */
    function data_set(mixed &$target, string|array $key, mixed $value, bool $overwrite = true): mixed
    {
        return \Illuminate\Support\Arr::set($target, $key, $value, $overwrite);
    }
}

if (!function_exists('str')) {
    /**
     * Create a new stringable object from the given string
     */
    function str(string $string = ''): \Illuminate\Support\Stringable
    {
        return \Illuminate\Support\Str::of($string);
    }
}

if (!function_exists('now')) {
    /**
     * Create a new Carbon instance for the current time
     */
    function now($tz = null): \Carbon\Carbon
    {
        return \Carbon\Carbon::now($tz);
    }
}

if (!function_exists('today')) {
    /**
     * Create a new Carbon instance for today
     */
    function today($tz = null): \Carbon\Carbon
    {
        return \Carbon\Carbon::today($tz);
    }
}

if (!function_exists('tap')) {
    /**
     * Call the given Closure with the given value then return the value
     */
    function tap(mixed $value, ?callable $callback = null): mixed
    {
        if (is_null($callback)) {
            return new \Illuminate\Support\HigherOrderTapProxy($value);
        }

        $callback($value);

        return $value;
    }
}

if (!function_exists('optional')) {
    /**
     * Provide access to optional objects
     */
    function optional(mixed $value = null, ?callable $callback = null): mixed
    {
        if (is_null($callback)) {
            return new \Illuminate\Support\Optional($value);
        }

        if (!is_null($value)) {
            return $callback($value);
        }

        return null;
    }
}

if (!function_exists('app')) {
    /**
     * Get the available container instance
     */
    function app(?string $abstract = null, array $parameters = []): mixed
    {
        if (is_null($abstract)) {
            return \Illuminate\Container\Container::getInstance();
        }

        return \Illuminate\Container\Container::getInstance()->make($abstract, $parameters);
    }
}

if (!function_exists('config')) {
    /**
     * Get / set the specified configuration value
     */
    function config(array|string|null $key = null, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return app('config');
        }

        if (is_array($key)) {
            return app('config')->set($key);
        }

        return app('config')->get($key, $default);
    }
}

if (!function_exists('event')) {
    /**
     * Dispatch an event and call the listeners
     */
    function event(string|object $event, mixed $payload = [], bool $halt = false): array|null
    {
        return app('events')->dispatch($event, $payload, $halt);
    }
}
