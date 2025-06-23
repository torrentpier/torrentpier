<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

/**
 * Application Exception Handler
 *
 * Handles all uncaught exceptions in the application
 */
class Handler
{
    /**
     * Handle an uncaught exception
     */
    public function handle(Throwable $exception): void
    {
        // Log the exception
        error_log($exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());

        // You can add custom handling here
    }

    /**
     * Render an exception for HTTP response
     */
    public function render(Throwable $exception): string
    {
        if (php_sapi_name() === 'cli') {
            return $exception->getMessage() . "\n";
        }

        // Return JSON for API requests or HTML for web
        $isJson = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');

        if ($isJson) {
            return json_encode([
                'error' => $exception->getMessage(),
                'code' => $exception->getCode()
            ]);
        }

        return '<h1>Error</h1><p>' . htmlspecialchars($exception->getMessage()) . '</p>';
    }
}
