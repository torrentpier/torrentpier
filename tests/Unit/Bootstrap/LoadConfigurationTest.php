<?php

declare(strict_types=1);

use App\Bootstrap\LoadConfiguration;

describe('LoadConfiguration', function () {
    afterEach(function () {
        LoadConfiguration::alwaysUse(null);
    });

    describe('alwaysUse', function () {
        it('allows setting static config override', function () {
            $testConfig = ['app' => ['debug' => true, 'env' => 'testing']];

            LoadConfiguration::alwaysUse(fn ($app) => $testConfig);

            $reflection = new ReflectionClass(LoadConfiguration::class);
            $property = $reflection->getProperty('alwaysUseConfig');

            expect($property->getValue())->not->toBeNull();
        });

        it('can be reset to null', function () {
            LoadConfiguration::alwaysUse(fn ($app) => ['test' => 'value']);
            LoadConfiguration::alwaysUse(null);

            $reflection = new ReflectionClass(LoadConfiguration::class);
            $property = $reflection->getProperty('alwaysUseConfig');

            expect($property->getValue())->toBeNull();
        });

        it('closure receives application instance', function () {
            $receivedApp = null;

            LoadConfiguration::alwaysUse(function ($app) use (&$receivedApp) {
                $receivedApp = $app;

                return ['test' => 'value'];
            });

            $reflection = new ReflectionClass(LoadConfiguration::class);
            $property = $reflection->getProperty('alwaysUseConfig');

            $closure = $property->getValue();
            $mockApp = new stdClass;
            $result = $closure($mockApp);

            expect($receivedApp)->toBe($mockApp)
                ->and($result)->toBe(['test' => 'value']);
        });
    });
});
