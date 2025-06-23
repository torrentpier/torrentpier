<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

/**
 * Base Service Provider
 * 
 * All application service providers should extend this class
 */
abstract class ServiceProvider extends IlluminateServiceProvider
{
    // Add any application-specific service provider functionality here
}